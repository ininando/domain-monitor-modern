{{-- Form OPD bersama untuk tambah dan edit. Variabel: $opd, $action, $method, $submitLabel --}}

<form method="POST" action="{{ $action }}" x-data="{ mengirim: false }" @submit="mengirim = true" class="card" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-6 p-6 sm:p-8">
        <div>
            <h2 class="text-base font-semibold text-ink">Informasi OPD</h2>
            <p class="mt-1 text-sm text-ink-muted">Kolom bertanda <span class="text-red-600 dark:text-red-400">*</span> wajib diisi.</p>
        </div>

        <x-input
            name="nama_opd"
            label="Nama OPD"
            :value="$opd->nama_opd"
            required
            maxlength="200"
            autofocus
            placeholder="Dinas Komunikasi dan Informatika"
        />

        <div class="grid gap-6 sm:grid-cols-2">
            <x-input
                name="kepala_opd"
                label="Kepala OPD"
                :value="$opd->kepala_opd"
                maxlength="100"
                autocomplete="name"
                placeholder="Nama lengkap beserta gelar"
            />
            <x-input
                name="kontak"
                label="Kontak"
                :value="$opd->kontak"
                maxlength="50"
                placeholder="(0431) 351001"
                hint="Nomor telepon kantor atau email resmi."
            />
        </div>

        <x-textarea
            name="alamat"
            label="Alamat"
            :value="$opd->alamat"
            rows="3"
            placeholder="Jl. Raya Tomohon, Kel. Kolongan, Tomohon Tengah"
        />
    </div>

    <div class="flex flex-col-reverse gap-2 border-t border-line px-6 py-4 sm:flex-row sm:justify-end sm:px-8">
        <x-button :href="route('opd.index')" variant="secondary">Batal</x-button>
        <x-button icon="save" loading="mengirim">{{ $submitLabel }}</x-button>
    </div>
</form>
