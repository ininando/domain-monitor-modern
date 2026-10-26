<?php

namespace App\Http\Controllers;

use App\Enums\LevelExpired;
use App\Enums\StatusAktif;
use App\Http\Requests\DomainRequest;
use App\Models\Domain;
use App\Models\Opd;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DomainController extends Controller
{
    /**
     * Pilihan urutan daftar domain: [kunci => label].
     *
     * @var array<string, string>
     */
    private const URUTAN = [
        'mendesak' => 'Paling mendesak',
        'nama' => 'Nama domain (A–Z)',
        'terbaru' => 'Terakhir ditambahkan',
    ];

    /**
     * Daftar domain dengan filter OPD, status, level, rentang tanggal expired, dan pencarian.
     */
    public function index(Request $request): View
    {
        $filter = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'opd_id' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(StatusAktif::class)],
            'level' => ['nullable', Rule::enum(LevelExpired::class)],
            'expired_dari' => ['nullable', 'date_format:Y-m-d'],
            'expired_sampai' => array_filter([
                'nullable',
                'date_format:Y-m-d',
                $request->filled('expired_dari') ? 'after_or_equal:expired_dari' : null,
            ]),
            'urut' => ['nullable', Rule::in(array_keys(self::URUTAN))],
        ], [
            'expired_sampai.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
        ], [
            'expired_dari' => 'tanggal expired awal',
            'expired_sampai' => 'tanggal expired akhir',
        ]);

        $urut = $filter['urut'] ?? 'mendesak';

        $daftarDomain = Domain::query()
            ->with('opd:id,nama_opd')
            ->cari($filter['q'] ?? null)
            ->when($filter['opd_id'] ?? null, fn ($query, $opdId) => $query->where('opd_id', $opdId))
            ->when($filter['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filter['level'] ?? null, fn ($query, $level) => $query->level(LevelExpired::from($level)))
            ->when($filter['expired_dari'] ?? null, fn ($query, $dari) => $query->whereDate('tanggal_expired', '>=', $dari))
            ->when($filter['expired_sampai'] ?? null, fn ($query, $sampai) => $query->whereDate('tanggal_expired', '<=', $sampai))
            ->when($urut === 'mendesak', fn ($query) => $query->orderBy('tanggal_expired'))
            ->when($urut === 'nama', fn ($query) => $query->orderBy('nama_domain'))
            ->when($urut === 'terbaru', fn ($query) => $query->latest())
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $jumlahFilterAktif = collect($filter)
            ->except(['q', 'urut'])
            ->filter(fn ($nilai) => filled($nilai))
            ->count();

        return view('domains.index', [
            'daftarDomain' => $daftarDomain,
            'opsiOpd' => Opd::orderBy('nama_opd')->pluck('nama_opd', 'id'),
            'opsiUrutan' => self::URUTAN,
            'filter' => $filter,
            'jumlahFilterAktif' => $jumlahFilterAktif,
        ]);
    }

    /**
     * Form tambah domain.
     */
    public function create(Request $request): View
    {
        $domain = new Domain([
            'status' => StatusAktif::Aktif,
            'opd_id' => $request->integer('opd_id') ?: null,
        ]);

        return view('domains.create', [
            'domain' => $domain,
            'opsiOpd' => Opd::orderBy('nama_opd')->pluck('nama_opd', 'id'),
        ]);
    }

    /**
     * Simpan domain baru.
     */
    public function store(DomainRequest $request): RedirectResponse
    {
        $domain = Domain::create($request->validated());

        return to_route('domains.show', $domain)->with('success', "Domain {$domain->nama_domain} ditambahkan.");
    }

    /**
     * Detail domain.
     */
    public function show(Domain $domain): View
    {
        $domain->load('opd');

        return view('domains.show', ['domain' => $domain]);
    }

    /**
     * Form edit domain.
     */
    public function edit(Domain $domain): View
    {
        return view('domains.edit', [
            'domain' => $domain,
            'opsiOpd' => Opd::orderBy('nama_opd')->pluck('nama_opd', 'id'),
        ]);
    }

    /**
     * Perbarui domain.
     */
    public function update(DomainRequest $request, Domain $domain): RedirectResponse
    {
        $domain->update($request->validated());

        return to_route('domains.show', $domain)->with('success', "Perubahan domain {$domain->nama_domain} disimpan.");
    }

    /**
     * Hapus domain.
     */
    public function destroy(Domain $domain): RedirectResponse
    {
        $domain->delete();

        return to_route('domains.index')->with('success', "Domain {$domain->nama_domain} dihapus.");
    }
}
