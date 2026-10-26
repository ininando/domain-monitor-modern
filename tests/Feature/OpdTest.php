<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_index_lists_opd_with_domain_count(): void
    {
        $opd = Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);
        Domain::factory()->count(2)->for($opd)->create();

        $this->get(route('opd.index'))
            ->assertOk()
            ->assertSee('Dinas Kesehatan')
            ->assertViewHas('daftarOpd', fn ($daftar) => $daftar->first()->domains_count === 2);
    }

    public function test_index_can_search_by_name(): void
    {
        Opd::factory()->create(['nama_opd' => 'Dinas Pariwisata']);
        Opd::factory()->create(['nama_opd' => 'Sekretariat Daerah']);

        $this->get(route('opd.index', ['q' => 'pariwisata']))
            ->assertOk()
            ->assertSee('Dinas Pariwisata')
            ->assertDontSee('Sekretariat Daerah');
    }

    public function test_store_creates_opd(): void
    {
        $this->post(route('opd.store'), [
            'nama_opd' => 'Dinas Komunikasi dan Informatika',
            'kontak' => '(0431) 351001',
        ])->assertRedirect(route('opd.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('opd', ['nama_opd' => 'Dinas Komunikasi dan Informatika']);
    }

    public function test_store_requires_nama_opd(): void
    {
        $this->post(route('opd.store'), ['nama_opd' => ''])
            ->assertSessionHasErrors(['nama_opd' => 'Nama OPD wajib diisi.']);

        $this->assertDatabaseCount('opd', 0);
    }

    public function test_update_changes_opd(): void
    {
        $opd = Opd::factory()->create();

        $this->put(route('opd.update', $opd), ['nama_opd' => 'Badan Kepegawaian'])
            ->assertRedirect(route('opd.index'));

        $this->assertSame('Badan Kepegawaian', $opd->fresh()->nama_opd);
    }

    public function test_destroy_deletes_opd_without_domains(): void
    {
        $opd = Opd::factory()->create();

        $this->delete(route('opd.destroy', $opd))
            ->assertRedirect(route('opd.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($opd);
    }

    public function test_destroy_is_rejected_when_opd_still_has_domains(): void
    {
        $opd = Opd::factory()->create(['nama_opd' => 'Dinas Pendidikan']);
        Domain::factory()->for($opd)->create();

        $this->from(route('opd.index'))
            ->delete(route('opd.destroy', $opd))
            ->assertRedirect(route('opd.index'))
            ->assertSessionHas('error', fn (string $pesan) => str_contains($pesan, 'masih memiliki 1 domain'));

        $this->assertModelExists($opd);
    }
}
