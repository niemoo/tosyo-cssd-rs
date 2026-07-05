<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Hospital;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['name', 'username', 'is_active', 'created_at', 'last_login_at'];

        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query = User::with(['roles', 'hospitals'])
                     ->withTrashed($request->boolean('show_deleted'));

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }

        $query->orderBy($sortBy, $sortDir);

        $users = $query->paginate(10)->withQueryString();

        $roles     = Role::orderBy('name')->get();
        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total'    => User::count(),
            'active'   => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        return view('users.index', compact('users', 'roles', 'hospitals', 'stats', 'sortBy', 'sortDir'));
    }

    public function create(): View
    {
        $roles     = Role::orderBy('name')->get();
        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get();

        return view('users.create', compact('roles', 'hospitals'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'username'  => $request->username,
            'name'      => $request->name,
            'phone'     => $request->phone,
            'password'  => $request->password,
            'is_active' => $request->is_active,
        ]);

        // Assign ke hospitals
        foreach ($request->hospital_ids as $hospitalId) {
            $user->hospitalUsers()->create([
                'hospital_id' => $hospitalId,
                'joined_at'   => now(),
                'is_active'   => true,
            ]);
        }

        // Assign role
        $user->assignRole($request->role);

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'hospitals']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $roles           = Role::orderBy('name')->get();
        $hospitals       = Hospital::where('is_active', true)->orderBy('name')->get();
        $userHospitalIds = $user->hospitalUsers()
                                ->where('is_active', true)
                                ->pluck('hospital_id')
                                ->toArray();
        $userRole        = $user->roles->first()?->name;

        return view('users.edit', compact('user', 'roles', 'hospitals', 'userHospitalIds', 'userRole'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name'      => $request->name,
            'phone'     => $request->phone,
            'is_active' => $request->is_active,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        $newHospitalIds     = $request->hospital_ids ?? [];
        $allHospitalRecords = $user->hospitalUsers()->get();
        $existingIds        = $allHospitalRecords->pluck('hospital_id')->toArray();

        foreach ($newHospitalIds as $hospitalId) {
            if (in_array($hospitalId, $existingIds)) {
                // Sudah ada — aktifkan
                $user->hospitalUsers()
                    ->where('hospital_id', $hospitalId)
                    ->update(['is_active' => true]);
            } else {
                // Belum ada — buat baru
                $user->hospitalUsers()->create([
                    'hospital_id' => $hospitalId,
                    'joined_at'   => now(),
                    'is_active'   => true,
                ]);
            }
        }

        // Nonaktifkan yang tidak dipilih
        $user->hospitalUsers()
            ->whereNotIn('hospital_id', $newHospitalIds)
            ->update(['is_active' => false]);

        // Sync role
        $user->syncRoles([$request->role]);

        return redirect()->route('users.index')
                        ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                             ->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('users.index')
                         ->with('success', 'Pengguna berhasil dipulihkan.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                             ->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
                         ->with('success', "Pengguna berhasil {$status}.");
    }
}