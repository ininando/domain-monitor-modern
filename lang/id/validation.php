<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Pesan Validasi (Bahasa Indonesia)
    |--------------------------------------------------------------------------
    |
    | Hanya aturan yang dipakai aplikasi ini. Aturan lain jatuh ke pesan
    | bahasa fallback (en).
    |
    */

    'after_or_equal' => ':Attribute harus berupa tanggal yang sama dengan atau setelah :date.',
    'before_or_equal' => ':Attribute harus berupa tanggal yang sama dengan atau sebelum :date.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':Attribute bukan tanggal yang valid.',
    'date_format' => ':Attribute harus berformat :format.',
    'email' => ':Attribute harus berupa alamat email yang valid.',
    'exists' => ':Attribute yang dipilih tidak valid.',
    'in' => ':Attribute yang dipilih tidak valid.',
    'integer' => ':Attribute harus berupa bilangan bulat.',
    'ip' => ':Attribute harus berupa alamat IP yang valid.',
    'max' => [
        'array' => ':Attribute maksimal berisi :max item.',
        'file' => ':Attribute maksimal berukuran :max kilobyte.',
        'numeric' => ':Attribute maksimal bernilai :max.',
        'string' => ':Attribute maksimal :max karakter.',
    ],
    'min' => [
        'array' => ':Attribute minimal berisi :min item.',
        'file' => ':Attribute minimal berukuran :min kilobyte.',
        'numeric' => ':Attribute minimal bernilai :min.',
        'string' => ':Attribute minimal :min karakter.',
    ],
    'regex' => 'Format :attribute tidak valid.',
    'required' => ':Attribute wajib diisi.',
    'string' => ':Attribute harus berupa teks.',
    'unique' => ':Attribute sudah digunakan.',

    'custom' => [],

    'attributes' => [
        'nama_opd' => 'nama OPD',
        'alamat' => 'alamat',
        'kontak' => 'kontak',
        'kepala_opd' => 'kepala OPD',
        'opd_id' => 'OPD',
        'nama_domain' => 'nama domain',
        'tanggal_registrasi' => 'tanggal registrasi',
        'tanggal_expired' => 'tanggal expired',
        'status' => 'status',
        'registrar' => 'registrar',
        'nameserver' => 'nameserver',
        'ip_address' => 'alamat IP',
        'hosting_provider' => 'penyedia hosting',
        'ssl_status' => 'status SSL',
        'keterangan' => 'keterangan',
        'name' => 'nama',
        'email' => 'email',
        'password' => 'password',
        'role' => 'peran',
    ],

];
