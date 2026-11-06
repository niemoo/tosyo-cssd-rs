<?php

namespace Tests\Feature;

use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Pagar permanen untuk Bug #7 ("permission hantu"): sebuah @can()/@cannot()/@canany()
 * di view, atau middleware permission: di route, yang menunjuk permission yang tidak
 * ada di katalog. @can() lalu selalu bernilai false untuk siapa pun — termasuk Admin —
 * sehingga tombolnya tak pernah dirender, atau (untuk route) semua orang kena 403.
 */
class PermissionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    /** Hanya cocokkan literal string berpola {modul}.{aksi}, supaya ability policy seperti @can('update', $model) tidak ikut terjaring. */
    private const PERMISSION_NAME_PATTERN = '/^[a-z0-9-]+\.[a-z0-9-]+$/';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
    }

    public function test_no_ghost_permissions_referenced_in_blade_views(): void
    {
        $catalog = Permission::pluck('name')->all();
        $viewsPath = resource_path('views');
        $offenders = [];

        foreach (File::allFiles($viewsPath) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            $relativePath = 'resources/views/'.ltrim(str_replace($viewsPath, '', $file->getPathname()), '/\\');

            foreach ($this->extractPermissionLiterals($contents) as $name) {
                if (! in_array($name, $catalog, true)) {
                    $offenders[] = "'{$name}' referenced in {$relativePath} does not exist in the Permission catalog";
                }
            }
        }

        $this->assertEmpty($offenders, "Ghost permissions found in Blade views:\n".implode("\n", $offenders));
    }

    public function test_no_ghost_permissions_referenced_in_route_middleware(): void
    {
        $catalog = Permission::pluck('name')->all();
        $offenders = [];

        foreach (Route::getRoutes() as $route) {
            foreach ($route->gatherMiddleware() as $middleware) {
                if (! str_starts_with($middleware, 'permission:')) {
                    continue;
                }

                $spec = substr($middleware, strlen('permission:'));

                foreach (explode('|', $spec) as $name) {
                    if (! in_array($name, $catalog, true)) {
                        $offenders[] = "'{$name}' referenced on route '{$route->getName()}' ({$route->uri()}) does not exist in the Permission catalog";
                    }
                }
            }
        }

        $this->assertEmpty($offenders, "Ghost permissions found in route middleware:\n".implode("\n", $offenders));
    }

    /**
     * Pagar untuk Bug #8: permission-nya eksis di katalog, tapi gate @can() di view
     * dan middleware permission: di route tidak sama — tombol muncul lalu submit 403,
     * atau (kebalikannya) tombol tersembunyi dari orang yang sebenarnya berhak.
     * Sudah menggigit 3x (consumable-usages, distribution-requests, sterilization-batches,
     * lalu restore di 12 modul lain) — tes ini memeriksa setiap route `*.restore` /
     * `*.destroy` yang direferensikan dari `resources/views/{modul}/index.blade.php`.
     */
    public function test_restore_and_destroy_route_permissions_match_view_gates(): void
    {
        $viewsPath = resource_path('views');
        $mismatches = [];

        foreach (File::directories($viewsPath) as $moduleDir) {
            $indexFile = $moduleDir.'/index.blade.php';

            if (! File::exists($indexFile)) {
                continue;
            }

            $module = basename($moduleDir);
            $contents = file_get_contents($indexFile);

            foreach (['restore', 'destroy'] as $action) {
                $routeName = "{$module}.{$action}";

                if (! Route::has($routeName)) {
                    continue;
                }

                $viewGates = $this->enclosingCanGatesForRoute($contents, $routeName);

                if (empty($viewGates)) {
                    continue; // this action isn't referenced from this index view
                }

                $routeGate = $this->mostSpecificPermission($routeName);

                foreach (array_unique($viewGates) as $viewGate) {
                    if ($viewGate !== $routeGate) {
                        $mismatches[] = "{$module}: view gates route('{$routeName}') behind @can('{$viewGate}') but the route's middleware requires permission:{$routeGate}";
                    }
                }
            }
        }

        $this->assertEmpty($mismatches, "Route/view permission mismatches:\n".implode("\n", $mismatches));
    }

    /** Last permission: middleware entry on the named route — the route-specific gate, after the enclosing group's .view. */
    private function mostSpecificPermission(string $routeName): ?string
    {
        $route = Route::getRoutes()->getByName($routeName);
        $permissions = [];

        foreach ($route->gatherMiddleware() as $middleware) {
            if (str_starts_with($middleware, 'permission:')) {
                $permissions[] = substr($middleware, strlen('permission:'));
            }
        }

        return end($permissions) ?: null;
    }

    /**
     * For every occurrence of route('{$routeName}' in $contents, walk the @can/@cannot/@endcan
     * stack up to that offset and return the name of the innermost still-open gate.
     *
     * @return list<string>
     */
    private function enclosingCanGatesForRoute(string $contents, string $routeName): array
    {
        $tokens = [];

        if (preg_match_all('/@can(?:not)?\(\s*[\'"]([^\'"]+)[\'"]/', $contents, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[1] as [$name, $offset]) {
                $tokens[] = ['offset' => $offset, 'type' => 'open', 'name' => $name];
            }
        }

        if (preg_match_all('/@endcan\b/', $contents, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as [, $offset]) {
                $tokens[] = ['offset' => $offset, 'type' => 'close', 'name' => null];
            }
        }

        usort($tokens, fn ($a, $b) => $a['offset'] <=> $b['offset']);

        $quotedRouteName = preg_quote($routeName, '/');
        if (! preg_match_all("/route\\(\\s*['\"]{$quotedRouteName}['\"]/", $contents, $routeMatches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $gates = [];

        foreach ($routeMatches[0] as [, $routeOffset]) {
            $stack = [];

            foreach ($tokens as $token) {
                if ($token['offset'] > $routeOffset) {
                    break;
                }

                if ($token['type'] === 'open') {
                    $stack[] = $token['name'];
                } else {
                    array_pop($stack);
                }
            }

            if (! empty($stack)) {
                $gates[] = end($stack);
            }
        }

        return $gates;
    }

    /** @return list<string> */
    private function extractPermissionLiterals(string $contents): array
    {
        $names = [];

        // @can('x.y') / @cannot('x.y') — first-argument string literal. Policy calls like
        // @can('update', $model) are captured too, but filtered out below by the dot pattern.
        if (preg_match_all('/@can(?:not)?\(\s*[\'"]([^\'"]+)[\'"]/', $contents, $matches)) {
            $names = array_merge($names, $matches[1]);
        }

        // @canany(['x.y', 'a.b']) — every quoted string inside the array literal.
        if (preg_match_all('/@canany\(\s*\[(.*?)\]/s', $contents, $arrayMatches)) {
            foreach ($arrayMatches[1] as $arrayBody) {
                if (preg_match_all('/[\'"]([^\'"]+)[\'"]/', $arrayBody, $stringMatches)) {
                    $names = array_merge($names, $stringMatches[1]);
                }
            }
        }

        return array_values(array_unique(array_filter(
            $names,
            fn (string $name) => preg_match(self::PERMISSION_NAME_PATTERN, $name) === 1
        )));
    }
}
