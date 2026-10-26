<?php

namespace App\Http\Controllers;

use App\Services\StatistikDomain;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard: ringkasan, domain yang perlu perhatian, dan chart.
     */
    public function __invoke(StatistikDomain $statistik): View
    {
        $perOpd = $statistik->perOpd();

        return view('dashboard', [
            'ringkasan' => $statistik->ringkasan(),
            'perluPerhatian' => $statistik->perluPerhatian(),
            'perOpd' => $perOpd->where('domains_count', '>', 0)->values(),
            'pertumbuhan' => $statistik->pertumbuhanBulanan(),
        ]);
    }
}
