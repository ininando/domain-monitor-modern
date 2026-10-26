@include('errors.layout', [
    'kode' => 500,
    'judul' => 'Terjadi kesalahan pada server',
    'pesan' => 'Kami tidak dapat memproses permintaan Anda saat ini. Coba lagi beberapa saat lagi.',
    'ikon' => 'triangle-alert',
    'tone' => 'critical',
])
