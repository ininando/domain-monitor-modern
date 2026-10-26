<?php

namespace Tests\Feature;

use App\Enums\StatusAktif;
use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /**
     * @return array<string, mixed>
     */
    private function dataValid(array $override = []): array
    {
        return array_merge([
            'opd_id' => Opd::factory()->create()->id,
            'nama_domain' => 'dinkes.tomohonkota.go.id',
            'tanggal_registrasi' => '2025-01-10',
            'tanggal_expired' => '2027-01-10',
            'status' => 'aktif',
            'ip_address' => '103.31.10.20',
            'ssl_status' => 'aktif',
        ], $override);
    }

    public function test_store_creates_domain_and_redirects_to_detail(): void
    {
        $response = $this->post(route('domains.store'), $this->dataValid());

        $domain = Domain::firstOrFail();
        $response->assertRedirect(route('domains.show', $domain))->assertSessionHas('success');
        $this->assertSame(StatusAktif::Aktif, $domain->status);
    }

    public function test_store_normalizes_domain_name(): void
    {
        $this->post(route('domains.store'), $this->dataValid(['nama_domain' => '  HTTPS://Dinkes.TomohonKota.go.id/ ']));

        $this->assertDatabaseHas('domains', ['nama_domain' => 'dinkes.tomohonkota.go.id']);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->post(route('domains.store'), [])
            ->assertSessionHasErrors(['opd_id', 'nama_domain', 'tanggal_expired', 'status']);
    }

    public function test_nama_domain_must_be_unique(): void
    {
        Domain::factory()->create(['nama_domain' => 'dinkes.tomohonkota.go.id']);

        $this->post(route('domains.store'), $this->dataValid())
            ->assertSessionHasErrors(['nama_domain' => 'Nama domain ini sudah terdaftar.']);
    }

    public function test_nama_domain_format_is_validated(): void
    {
        $this->post(route('domains.store'), $this->dataValid(['nama_domain' => 'bukan domain']))
            ->assertSessionHasErrors('nama_domain');
    }

    public function test_tanggal_expired_cannot_be_before_registration(): void
    {
        $this->post(route('domains.store'), $this->dataValid([
            'tanggal_registrasi' => '2026-05-01',
            'tanggal_expired' => '2026-04-30',
        ]))->assertSessionHasErrors(['tanggal_expired' => 'Tanggal expired tidak boleh lebih awal dari tanggal registrasi.']);
    }

    public function test_tanggal_expired_must_be_a_valid_date(): void
    {
        $this->post(route('domains.store'), $this->dataValid(['tanggal_expired' => '31-12-2026']))
            ->assertSessionHasErrors('tanggal_expired');
    }

    public function test_registration_date_is_optional(): void
    {
        $this->post(route('domains.store'), $this->dataValid(['tanggal_registrasi' => null]))
            ->assertSessionHasNoErrors();
    }

    public function test_ip_address_must_be_valid_when_filled(): void
    {
        $this->post(route('domains.store'), $this->dataValid(['ip_address' => '999.1.1.1']))
            ->assertSessionHasErrors(['ip_address' => 'Alamat IP harus berupa alamat IP yang valid.']);

        $this->post(route('domains.store'), $this->dataValid(['nama_domain' => 'lain.tomohonkota.go.id', 'ip_address' => '']))
            ->assertSessionHasNoErrors();
    }

    public function test_update_keeps_own_name_unique_check(): void
    {
        $domain = Domain::factory()->create(['nama_domain' => 'dinkes.tomohonkota.go.id']);

        $this->put(route('domains.update', $domain), $this->dataValid(['status' => 'tidak_aktif']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('domains.show', $domain));

        $this->assertSame(StatusAktif::TidakAktif, $domain->fresh()->status);
    }

    public function test_destroy_deletes_domain(): void
    {
        $domain = Domain::factory()->create();

        $this->delete(route('domains.destroy', $domain))->assertRedirect(route('domains.index'));

        $this->assertModelMissing($domain);
    }

    public function test_show_displays_countdown_and_technical_info(): void
    {
        $domain = Domain::factory()->expiredDalam(-4)->create([
            'nama_domain' => 'ppdb.tomohonkota.go.id',
            'registrar' => 'PANDI',
            'ip_address' => '103.31.12.24',
        ]);

        $this->get(route('domains.show', $domain))
            ->assertOk()
            ->assertSee('ppdb.tomohonkota.go.id')
            ->assertSee('Expired 4 hari lalu')
            ->assertSee('PANDI')
            ->assertSee('103.31.12.24');
    }

    public function test_index_filters_by_opd_status_and_search(): void
    {
        $opdA = Opd::factory()->create();
        $opdB = Opd::factory()->create();
        Domain::factory()->for($opdA)->create(['nama_domain' => 'alpha.tomohonkota.go.id']);
        Domain::factory()->for($opdA)->tidakAktif()->create(['nama_domain' => 'beta.tomohonkota.go.id']);
        Domain::factory()->for($opdB)->create(['nama_domain' => 'gamma.tomohonkota.go.id']);

        $this->get(route('domains.index', ['opd_id' => $opdA->id, 'status' => 'aktif']))
            ->assertSee('alpha.tomohonkota.go.id')
            ->assertDontSee('beta.tomohonkota.go.id')
            ->assertDontSee('gamma.tomohonkota.go.id');

        $this->get(route('domains.index', ['q' => 'gamma']))
            ->assertSee('gamma.tomohonkota.go.id')
            ->assertDontSee('alpha.tomohonkota.go.id');
    }

    public function test_index_filters_by_expiry_range_and_level(): void
    {
        $kritis = Domain::factory()->expiredDalam(3)->create();
        $waspada = Domain::factory()->expiredDalam(20)->create();
        $aman = Domain::factory()->expiredDalam(200)->create();

        // Diperiksa lewat data view karena lonceng notifikasi di topbar ikut menampilkan domain kritis.
        $this->get(route('domains.index', ['level' => 'warning']))
            ->assertViewHas('daftarDomain', fn ($daftar) => $daftar->pluck('id')->all() === [$waspada->id]);

        $this->get(route('domains.index', [
            'expired_dari' => today()->toDateString(),
            'expired_sampai' => today()->addDays(10)->toDateString(),
        ]))
            ->assertViewHas('daftarDomain', fn ($daftar) => $daftar->pluck('id')->all() === [$kritis->id]);

        $this->assertNotNull($aman);
    }

    public function test_index_orders_most_urgent_first(): void
    {
        Domain::factory()->expiredDalam(90)->create(['nama_domain' => 'nanti.tomohonkota.go.id']);
        Domain::factory()->expiredDalam(-2)->create(['nama_domain' => 'lewat.tomohonkota.go.id']);

        $this->get(route('domains.index'))
            ->assertSeeInOrder(['lewat.tomohonkota.go.id', 'nanti.tomohonkota.go.id']);
    }
}
