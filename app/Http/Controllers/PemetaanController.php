<?php

namespace App\Http\Controllers;

use App\Enums\LevelExpired;
use App\Exports\PemetaanExport;
use App\Models\Opd;
use App\Services\StatistikDomain;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PemetaanController extends Controller
{
    /**
     * Pemetaan kepemilikan domain per OPD: tabel, bar chart, dan donut.
     */
    public function index(StatistikDomain $statistik): View
    {
        $perOpd = $statistik->perOpd();

        return view('pemetaan.index', [
            'perOpd' => $perOpd,
            'donut' => $statistik->donutOpd($perOpd),
            'totalDomain' => $perOpd->sum('domains_count'),
            'opdTanpaDomain' => $perOpd->where('domains_count', 0)->count(),
        ]);
    }

    /**
     * Detail domain milik satu OPD.
     */
    public function show(Opd $opd): View
    {
        $domains = $opd->domains()->orderBy('tanggal_expired')->get();

        $perLevel = collect(LevelExpired::cases())->mapWithKeys(
            fn (LevelExpired $level): array => [$level->value => $domains->filter(fn ($domain) => $domain->level_expired === $level)->count()]
        );

        return view('pemetaan.show', [
            'opd' => $opd,
            'domains' => $domains,
            'perLevel' => $perLevel,
        ]);
    }

    /**
     * Export tabel pemetaan ke Excel.
     */
    public function export(StatistikDomain $statistik): BinaryFileResponse
    {
        return Excel::download(
            new PemetaanExport($statistik->perOpd()),
            'pemetaan-domain-opd-'.now()->format('Ymd-His').'.xlsx',
        );
    }
}
