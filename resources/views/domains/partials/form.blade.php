{{-- Form domain bersama untuk tambah dan edit. Variabel: $domain, $opsiOpd, $action, $method, $submitLabel, $batal --}}

@php
    use App\Enums\StatusAktif;

    $bagian = 'grid gap-x-6 gap-y-5 border-t border-line px-6 py-7 first:border-t-0 sm:px-8 lg:grid-cols-[minmax(0,14rem)_minmax(0,1fr)]';
@endphp

<form method="POST" action="{{ $action }}" x-data="{ mengirim: false }" @submit="mengirim = true" class="card" novalidate>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($errors->any())
        <div role="alert" class="m-6 mb-0 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-3.5 text-sm text-red-800 sm:mx-8 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-200">
            <x-lucide-triangle-alert class="mt-0.5 size-4 shrink-0" aria-hidden="true" />
            <p>Periksa kembali {{ $errors->count() }} isian yang ditandai di bawah.</p>
        </div>
    @endif

    <section class="{{ $bagian }}" aria-labelledby="bagian-identitas">
        <div>
            <h2 id="bagian-identitas" class="text-sm font-semibold text-ink">Identitas domain</h2>
            <p class="mt-1 text-sm text-ink-muted">Nama domain dan OPD pemiliknya.</p>
        </div>
        <div class="space-y-5">
            <x-input
                name="nama_domain"
                label="Nama domain"
                :value="$domain->nama_domain"
                required
                maxlength="255"
                autocomplete="off"
                spellcheck="false"
                placeholder="dinkes.tomohonkota.go.id"
                hint="Tanpa http:// atau https://."
            />
            <x-select name="opd_id" label="OPD pemilik" :options="$opsiOpd" :value="$domain->opd_id" placeholder="Pilih OPD" required />
        </div>
    </section>

    <section class="{{ $bagian }}" aria-labelledby="bagian-masa">
        <div>
            <h2 id="bagian-masa" class="text-sm font-semibold text-ink">Masa berlaku</h2>
            <p class="mt-1 text-sm text-ink-muted">Dipakai untuk menghitung sisa hari dan level peringatan.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-input name="tanggal_registrasi" label="Tanggal registrasi" type="date" :value="$domain->tanggal_registrasi?->toDateString()" />
            <x-input name="tanggal_expired" label="Tanggal expired" type="date" :value="$domain->tanggal_expired?->toDateString()" required />
            <x-select name="status" label="Status domain" :options="StatusAktif::opsi()" :value="$domain->status?->value" required />
        </div>
    </section>

    <section class="{{ $bagian }}" aria-labelledby="bagian-teknis">
        <div>
            <h2 id="bagian-teknis" class="text-sm font-semibold text-ink">Informasi teknis</h2>
            <p class="mt-1 text-sm text-ink-muted">Opsional, membantu saat penelusuran masalah.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-input name="registrar" label="Registrar" :value="$domain->registrar" maxlength="100" placeholder="PANDI" />
            <x-input name="hosting_provider" label="Penyedia hosting" :value="$domain->hosting_provider" maxlength="100" placeholder="Server Diskominfo Tomohon" />
            <x-input name="ip_address" label="Alamat IP" :value="$domain->ip_address" maxlength="50" inputmode="decimal" spellcheck="false" placeholder="103.31.10.20" />
            <x-select name="ssl_status" label="Status SSL" :options="StatusAktif::opsi()" :value="$domain->ssl_status?->value" placeholder="Belum diketahui" />
            <x-textarea name="nameserver" label="Nameserver" :value="$domain->nameserver" rows="2" class="sm:col-span-2" spellcheck="false" placeholder="ns1.tomohonkota.go.id" hint="Satu nameserver per baris." />
        </div>
    </section>

    <section class="{{ $bagian }}" aria-labelledby="bagian-keterangan">
        <div>
            <h2 id="bagian-keterangan" class="text-sm font-semibold text-ink">Keterangan</h2>
            <p class="mt-1 text-sm text-ink-muted">Catatan tambahan untuk tim.</p>
        </div>
        <x-textarea name="keterangan" label="Keterangan" :value="$domain->keterangan" rows="3" placeholder="Domain layanan antrean online Disdukcapil" />
    </section>

    <div class="flex flex-col-reverse gap-2 border-t border-line px-6 py-4 sm:flex-row sm:justify-end sm:px-8">
        <x-button :href="$batal" variant="secondary">Batal</x-button>
        <x-button icon="save" loading="mengirim">{{ $submitLabel }}</x-button>
    </div>
</form>
