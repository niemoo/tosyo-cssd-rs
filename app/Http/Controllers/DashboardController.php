<?php

namespace App\Http\Controllers;

use App\Models\Consumable;
use App\Models\HospitalUser;
use App\Models\Sterilizer;
use App\Models\Tray;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $hospitalId = session('active_hospital_id');

        // KPI Tray
        $trayStats = [
            'sterile' => Tray::where('hospital_id', $hospitalId)
                ->where('status', Tray::STATUS_STERILE)
                ->where('is_active', true)->count(),

            'in_process' => Tray::where('hospital_id', $hospitalId)
                ->whereIn('status', [
                    Tray::STATUS_ASSEMBLING,
                    Tray::STATUS_READY,
                    Tray::STATUS_IN_STERILIZATION,
                ])
                ->where('is_active', true)->count(),

            'distributed' => Tray::where('hospital_id', $hospitalId)
                ->where('status', Tray::STATUS_IN_USE)
                ->where('is_active', true)->count(),

            'stored' => Tray::where('hospital_id', $hospitalId)
                ->where('status', Tray::STATUS_STERILE)
                ->whereNotNull('current_rack_id')
                ->where('is_active', true)->count(),
        ];

        // Tray per status (untuk pipeline)
        $trayByStatus = Tray::where('hospital_id', $hospitalId)
            ->where('is_active', true)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Tray terbaru
        $recentTrays = Tray::with('template')
            ->where('hospital_id', $hospitalId)
            ->where('is_active', true)
            ->latest()
            ->take(6)
            ->get();

        // Sterilizer maintenance due
        $sterilizersDue = Sterilizer::where('hospital_id', $hospitalId)
            ->where('is_active', true)
            ->get()
            ->filter(fn ($s) => $s->isMaintenanceDue())
            ->take(3);

        // Kedaluwarsa steril — sterile trays yang akan/sudah lewat expiry_at.
        // "Sudah Kedaluwarsa" normalnya 0 kalau scheduler trays:expire jalan; kalau > 0
        // berarti scheduler belum sempat jalan (jaring pengaman, bukan alur normal).
        $sterileTrays = Tray::where('hospital_id', $hospitalId)
            ->where('status', Tray::STATUS_STERILE)
            ->where('is_active', true)
            ->get();

        $expiredTrays = $sterileTrays->filter(fn ($t) => $t->isExpired());
        $expiringSoonTrays = $sterileTrays->filter(fn ($t) => $t->expiresWithinDays(7));

        $expiryStats = [
            'expired' => $expiredTrays->count(),
            'expiring_soon' => $expiringSoonTrays->count(),
        ];

        $soonestExpiringTrays = $expiredTrays->concat($expiringSoonTrays)
            ->sortBy('expiry_at')
            ->take(5)
            ->values();

        // Consumable stok menipis
        $lowStockConsumables = Consumable::with(['stock', 'category'])
            ->where('hospital_id', $hospitalId)
            ->where('is_active', true)
            ->get()
            ->filter(fn ($c) => $c->isLowStock())
            ->take(5);

        // Total user aktif per hospital
        $totalUsers = HospitalUser::where('hospital_id', $hospitalId)
            ->where('is_active', true)
            ->count();

        return view('dashboard', compact(
            'trayStats',
            'trayByStatus',
            'recentTrays',
            'sterilizersDue',
            'lowStockConsumables',
            'totalUsers',
            'expiryStats',
            'soonestExpiringTrays',
        ));
    }
}
