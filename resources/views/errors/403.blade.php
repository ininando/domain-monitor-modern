@include('errors.layout', [
    'kode' => 403,
    'judul' => 'Akses ditolak',
    'pesan' => 'Halaman ini hanya dapat dibuka oleh pengguna dengan hak akses tertentu. Hubungi Super Admin jika Anda memerlukan akses.',
    'ikon' => 'shield-x',
    'tone' => 'critical',
])
