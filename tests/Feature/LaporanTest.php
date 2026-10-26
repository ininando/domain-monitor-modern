<?php

namespace Tests\Feature;

use App\Exports\LaporanExport;
use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use App\Services\LaporanDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_index_shows_form_without_preview(): void
    {
        $this->get(route('laporan.index'))
            ->assertOk()
            ->assertSee('Pilih parameter laporan')
            ->assertViewHas('pratinjau', null);
    }

    public function test_per_opd_report_filters_by_opd_and_expiry_period(): void
    {
        $opd = Opd::factory()->create(['nama_opd' => 'Dinas Kesehatan']);
        $dalamPeriode = Domain::factory()->for($opd)->create(['tanggal_expired' => '2026-11-10']);
        Domain::factory()->for($opd)->create(['tanggal_expired' => '2027-03-01']);
        Domain::factory()->create(['tanggal_expired' => '2026-11-10']);

        $this->get(route('laporan.index', [
            'jenis' => 'opd',
            'opd_id' => $opd->id,
            'dari' => '2026-11-01',
            'sampai' => '2026-11-30',
        ]))
            ->assertOk()
            ->assertViewHas('pratinjau', fn (array $laporan) => $laporan['subjudul'] === 'Dinas Kesehatan'
                && $laporan['domains']->pluck('id')->all() === [$dalamPeriode->id]
                && $laporan['periode'] === '01 November 2026 s.d. 30 November 2026');
    }

    public function test_per_status_report_only_includes_selected_status(): void
    {
        $tidakAktif = Domain::factory()->tidakAktif()->create();
        Domain::factory()->create();

        $laporan = app(LaporanDomain::class)->susun(['jenis' => 'status', 'status' => 'tidak_aktif']);

        $this->assertSame([$tidakAktif->id], $laporan['domains']->pluck('id')->all());
        $this->assertSame('Status: Tidak Aktif', $laporan['subjudul']);
    }

    public function test_expired_report_includes_only_past_domains_by_default(): void
    {
        $lewat = Domain::factory()->expiredDalam(-3)->create();
        $segera = Domain::factory()->expiredDalam(10)->create();
        Domain::factory()->expiredDalam(100)->create();

        $default = app(LaporanDomain::class)->susun(['jenis' => 'expired']);
        $denganAkanExpired = app(LaporanDomain::class)->susun(['jenis' => 'expired', 'sertakan_akan_expired' => '1']);

        $this->assertSame([$lewat->id], $default['domains']->pluck('id')->all());
        $this->assertSame([$lewat->id, $segera->id], $denganAkanExpired['domains']->pluck('id')->all());
    }

    public function test_per_opd_report_requires_opd(): void
    {
        $this->get(route('laporan.pdf', ['jenis' => 'opd']))
            ->assertSessionHasErrors(['opd_id' => 'Pilih OPD untuk laporan per OPD.']);
    }

    public function test_period_end_cannot_be_before_start(): void
    {
        $this->get(route('laporan.excel', ['jenis' => 'expired', 'dari' => '2026-12-01', 'sampai' => '2026-11-01']))
            ->assertSessionHasErrors('sampai');
    }

    public function test_pdf_is_downloaded(): void
    {
        Domain::factory()->expiredDalam(-1)->create(['nama_domain' => 'ppdb.tomohonkota.go.id']);

        $response = $this->get(route('laporan.pdf', ['jenis' => 'expired']));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('attachment; filename=laporan-expired-', $response->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_excel_is_downloaded_with_report_rows(): void
    {
        Excel::fake();
        $this->travelTo(now()->setDateTime(2026, 10, 15, 8, 0, 0));
        Domain::factory()->tidakAktif()->count(2)->create();

        $this->get(route('laporan.excel', ['jenis' => 'status', 'status' => 'tidak_aktif']))->assertOk();

        Excel::assertDownloaded('laporan-status-tidak_aktif-20261015-080000.xlsx', function (LaporanExport $export) {
            return $export->collection()->count() === 2
                && $export->headings() === ['No', 'Nama Domain', 'OPD', 'Status', 'Tanggal Expired', 'Sisa Waktu', 'Keterangan'];
        });
    }
}
