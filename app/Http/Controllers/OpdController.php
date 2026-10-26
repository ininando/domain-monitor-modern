<?php

namespace App\Http\Controllers;

use App\Http\Requests\OpdRequest;
use App\Models\Opd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OpdController extends Controller
{
    /**
     * Daftar OPD dengan pencarian dan jumlah domain.
     */
    public function index(Request $request): View
    {
        $kataKunci = $request->string('q')->trim()->value();

        $daftarOpd = Opd::query()
            ->withCount('domains')
            ->cari($kataKunci)
            ->orderBy('nama_opd')
            ->paginate(10)
            ->withQueryString();

        return view('opd.index', [
            'daftarOpd' => $daftarOpd,
            'kataKunci' => $kataKunci,
        ]);
    }

    /**
     * Form tambah OPD.
     */
    public function create(): View
    {
        return view('opd.create', ['opd' => new Opd]);
    }

    /**
     * Simpan OPD baru.
     */
    public function store(OpdRequest $request): RedirectResponse
    {
        $opd = Opd::create($request->validated());

        return to_route('opd.index')->with('success', "OPD \"{$opd->nama_opd}\" ditambahkan.");
    }

    /**
     * Form edit OPD.
     */
    public function edit(Opd $opd): View
    {
        return view('opd.edit', ['opd' => $opd]);
    }

    /**
     * Perbarui data OPD.
     */
    public function update(OpdRequest $request, Opd $opd): RedirectResponse
    {
        $opd->update($request->validated());

        return to_route('opd.index')->with('success', "Perubahan OPD \"{$opd->nama_opd}\" disimpan.");
    }

    /**
     * Hapus OPD. Ditolak jika OPD masih memiliki domain.
     */
    public function destroy(Opd $opd): RedirectResponse
    {
        $jumlahDomain = $opd->domains()->count();

        if ($jumlahDomain > 0) {
            return back()->with(
                'error',
                "OPD \"{$opd->nama_opd}\" tidak dapat dihapus karena masih memiliki {$jumlahDomain} domain. Pindahkan atau hapus domain tersebut terlebih dahulu."
            );
        }

        $opd->delete();

        return to_route('opd.index')->with('success', "OPD \"{$opd->nama_opd}\" dihapus.");
    }
}
