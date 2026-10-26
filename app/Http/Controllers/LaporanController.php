<?php

namespace App\Http\Controllers;

use App\Enums\StatusAktif;
use App\Exports\LaporanExport;
use App\Http\Requests\LaporanRequest;
use App\Models\Opd;
use App\Services\LaporanDomain;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    /**
     * Form laporan, beserta pratinjau jika parameter sudah dipilih.
     */
    public function index(LaporanRequest $request, LaporanDomain $laporan): View
    {
        $filter = $request->validated();

        return view('laporan.index', [
            'filter' => $filter,
            'pratinjau' => isset($filter['jenis']) ? $laporan->susun($filter) : null,
            'opsiJenis' => LaporanRequest::JENIS,
            'opsiOpd' => Opd::orderBy('nama_opd')->pluck('nama_opd', 'id'),
            'opsiStatus' => StatusAktif::opsi(),
        ]);
    }

    /**
     * Unduh laporan dalam format PDF (A4 landscape).
     */
    public function pdf(LaporanRequest $request, LaporanDomain $laporan): Response
    {
        $data = $laporan->susun($request->validated());

        return Pdf::loadView('laporan.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download($data['nama_file'].'.pdf');
    }

    /**
     * Unduh laporan dalam format Excel.
     */
    public function excel(LaporanRequest $request, LaporanDomain $laporan): BinaryFileResponse
    {
        $data = $laporan->susun($request->validated());

        return Excel::download(new LaporanExport($data), $data['nama_file'].'.xlsx');
    }
}
