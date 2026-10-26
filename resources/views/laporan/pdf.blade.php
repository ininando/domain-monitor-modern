<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        @page { margin: 28px 34px 46px; }
        * { font-family: 'DejaVu Sans', sans-serif; }
        body { font-size: 9.5px; color: #0f172a; }
        .kop { width: 100%; border-bottom: 2px solid #0369a1; padding-bottom: 10px; }
        .kop td { vertical-align: middle; }
        .kop .logo { width: 64px; }
        .kop .instansi-atas { font-size: 10px; letter-spacing: 1px; color: #334155; }
        .kop .instansi { font-size: 14px; font-weight: bold; color: #0f172a; margin-top: 2px; }
        .kop .alamat { font-size: 8.5px; color: #475569; margin-top: 2px; }
        .kop .meta { text-align: right; font-size: 8.5px; color: #475569; }
        h1 { font-size: 15px; margin: 16px 0 2px; color: #0f172a; }
        .sub { font-size: 10px; color: #334155; margin: 0; }
        .periode { font-size: 9px; color: #475569; margin: 2px 0 0; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.data th { background: #0369a1; color: #ffffff; font-size: 9px; text-align: left; padding: 7px 8px; }
        table.data td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8fafc; }
        .no { width: 26px; text-align: center; }
        .status-aktif { color: #047857; font-weight: bold; }
        .status-tidak { color: #475569; font-weight: bold; }
        .level { font-size: 8px; }
        .level-critical { color: #b91c1c; }
        .level-warning { color: #b45309; }
        .level-normal { color: #047857; }
        .total td { background: #e0f2fe !important; font-weight: bold; border-bottom: 0; }
        .kosong { text-align: center; color: #475569; padding: 18px; }
        footer { position: fixed; bottom: -30px; left: 0; right: 0; font-size: 8px; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 5px; }
        footer .kanan { float: right; }
    </style>
</head>
<body>
    <footer>
        Dicetak dari Sistem Monitoring Domain OPD · Diskominfo Kota Tomohon
        <span class="kanan">Dibuat {{ now()->translatedFormat('d F Y, H:i') }} WITA</span>
    </footer>

    <table class="kop">
        <tr>
            <td class="logo"><img src="{{ public_path('images/logo-tomohon.png') }}" width="54" height="54" alt="Logo Pemerintah Kota Tomohon"></td>
            <td>
                <div class="instansi-atas">PEMERINTAH KOTA TOMOHON</div>
                <div class="instansi">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                <div class="alamat">Kompleks Kantor Wali Kota Tomohon, Sulawesi Utara</div>
            </td>
            <td class="meta">
                Tanggal generate:<br>
                <strong>{{ now()->translatedFormat('d F Y, H:i') }}</strong>
            </td>
        </tr>
    </table>

    <h1>{{ $judul }}</h1>
    <p class="sub">{{ $subjudul }}</p>
    @if ($periode)
        <p class="periode">Periode tanggal expired: {{ $periode }}</p>
    @endif

    <table class="data">
        <thead>
            <tr>
                <th class="no">No</th>
                <th>Nama Domain</th>
                <th>OPD</th>
                <th>Status</th>
                <th>Tanggal Expired</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($domains as $domain)
                <tr>
                    <td class="no">{{ $loop->iteration }}</td>
                    <td>{{ $domain->nama_domain }}</td>
                    <td>{{ $domain->opd->nama_opd }}</td>
                    <td class="{{ $domain->status->value === 'aktif' ? 'status-aktif' : 'status-tidak' }}">{{ $domain->status->label() }}</td>
                    <td>
                        {{ $domain->tanggal_expired->translatedFormat('d F Y') }}<br>
                        <span class="level level-{{ $domain->level_expired->value }}">{{ $domain->level_expired->label() }} · {{ $domain->label_expired }}</span>
                    </td>
                    <td>{{ $domain->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="kosong">Tidak ada domain yang sesuai dengan parameter laporan.</td>
                </tr>
            @endforelse
            <tr class="total">
                <td colspan="5">Total domain</td>
                <td>{{ $domains->count() }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
