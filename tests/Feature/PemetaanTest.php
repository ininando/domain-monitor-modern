<?php

namespace Tests\Feature;

use App\Exports\PemetaanExport;
use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use App\Services\StatistikDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class PemetaanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_index_lists_every_opd_with_domain_count(): void
    {
        $dinkes = Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);
        Opd::factory()->create(['nama_opd' => 'Dinas Pariwisata']);
        Domain::factory()->count(3)->for($dinkes)->create();

        $this->get(route('pemetaan.index'))
            ->assertOk()
            ->assertSeeInOrder(['Dinas Kesehatan', 'Dinas Pariwisata'])
            ->assertSee(route('pemetaan.show', $dinkes))
            ->assertViewHas('totalDomain', 3)
            ->assertViewHas('opdTanpaDomain', 1);
    }

    public function test_donut_folds_small_opd_into_lainnya(): void
    {
        foreach (range(1, 7) as $i) {
            Domain::factory()->count(8 - $i)->for(Opd::factory())->create();
        }

        $statistik = app(StatistikDomain::class);
        $donut = $statistik->donutOpd($statistik->perOpd());

        $this->assertCount(StatistikDomain::MAKS_SLICE_DONUT + 1, $donut);
        $this->assertSame('Lainnya (2 OPD)', $donut->last()['label']);
        $this->assertSame(3, $donut->last()['nilai']);
    }

    public function test_show_lists_domains_of_the_opd_only(): void
    {
        $opd = Opd::factory()->create();
        Domain::factory()->for($opd)->create(['nama_domain' => 'milik.tomohonkota.go.id']);
        Domain::factory()->create(['nama_domain' => 'lain.tomohonkota.go.id']);

        $this->get(route('pemetaan.show', $opd))
            ->assertOk()
            ->assertSee('milik.tomohonkota.go.id')
            ->assertDontSee('lain.tomohonkota.go.id');
    }

    public function test_export_downloads_excel_file(): void
    {
        Excel::fake();
        $this->travelTo(now()->setDateTime(2026, 10, 15, 9, 30, 0));
        Domain::factory()->count(2)->create();

        $this->get(route('pemetaan.export'))->assertOk();

        Excel::assertDownloaded('pemetaan-domain-opd-20261015-093000.xlsx', function (PemetaanExport $export) {
            $baris = $export->collection();

            return $baris->count() === 3 && $baris->last()['domains_count'] === 2;
        });
    }

    public function test_guest_cannot_export(): void
    {
        auth()->logout();

        $this->get(route('pemetaan.export'))->assertRedirect(route('login'));
    }
}
