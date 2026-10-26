<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use App\Services\StatistikDomain;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect('/dashboard');
    }

    public function test_dashboard_shows_summary_counts(): void
    {
        Domain::factory()->expiredDalam(100)->count(3)->create();
        Domain::factory()->expiredDalam(100)->tidakAktif()->create();
        Domain::factory()->expiredDalam(10)->create();
        Domain::factory()->expiredDalam(-2)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('ringkasan', [
                'total' => 6,
                'aktif' => 5,
                'tidak_aktif' => 1,
                'akan_expired' => 1,
                'sudah_expired' => 1,
            ])
            ->assertSee('1 domain sudah melewati tanggal expired');
    }

    public function test_attention_list_is_ordered_most_urgent_first_and_excludes_safe_domains(): void
    {
        $aman = Domain::factory()->expiredDalam(90)->create();
        Domain::factory()->expiredDalam(25)->create(['nama_domain' => 'b.tomohonkota.go.id']);
        Domain::factory()->expiredDalam(-5)->create(['nama_domain' => 'a.tomohonkota.go.id']);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertViewHas('perluPerhatian', fn ($daftar) => $daftar->pluck('nama_domain')->all() === ['a.tomohonkota.go.id', 'b.tomohonkota.go.id'])
            ->assertSeeInOrder(['Expired 5 hari lalu', '25 hari lagi']);

        $this->assertNotContains($aman->id, app(StatistikDomain::class)->perluPerhatian()->pluck('id'));
    }

    public function test_growth_series_is_cumulative_over_twelve_months(): void
    {
        Domain::factory()->create(['tanggal_registrasi' => today()->subYears(3)]);
        Domain::factory()->create(['tanggal_registrasi' => today()->startOfMonth()->subMonths(2)]);
        Domain::factory()->create(['tanggal_registrasi' => today()]);

        $pertumbuhan = app(StatistikDomain::class)->pertumbuhanBulanan();

        $this->assertCount(12, $pertumbuhan['labels']);
        $this->assertSame(1, $pertumbuhan['total'][0]);
        $this->assertSame(2, $pertumbuhan['total'][9]);
        $this->assertSame(3, $pertumbuhan['total'][11]);
        $this->assertSame(1, $pertumbuhan['baru'][11]);
    }

    public function test_notification_bell_counts_critical_domains(): void
    {
        Domain::factory()->expiredDalam(3)->create();
        Domain::factory()->expiredDalam(-1)->create();
        Domain::factory()->expiredDalam(20)->create();

        $this->actingAs(User::factory()->create())
            ->get(route('domains.index'))
            ->assertSee('Notifikasi: 2 domain kritis');
    }
}
