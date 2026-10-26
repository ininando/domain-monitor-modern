<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Opd;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Memastikan setiap halaman dapat dirender dengan data seed untuk tiap peran,
 * dan hak akses per peran sesuai spesifikasi.
 */
class HalamanSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    /**
     * @return array<string, array{string, int, int}>
     */
    public static function halaman(): array
    {
        // [url, status Super Admin, status Admin]
        return [
            'dashboard' => ['/dashboard', 200, 200],
            'opd' => ['/opd', 200, 200],
            'opd tambah' => ['/opd/create', 200, 200],
            'domain' => ['/domains', 200, 200],
            'domain filter' => ['/domains?level=critical&urut=nama', 200, 200],
            'domain tambah' => ['/domains/create', 200, 200],
            'domain detail' => ['/domains/{domain}', 200, 200],
            'domain edit' => ['/domains/{domain}/edit', 200, 200],
            'pemetaan' => ['/pemetaan', 200, 200],
            'pemetaan detail' => ['/pemetaan/{opd}', 200, 200],
            'laporan' => ['/laporan', 200, 200],
            'laporan pratinjau' => ['/laporan?jenis=opd&opd_id={opd}', 200, 200],
            'user' => ['/users', 200, 403],
            'user tambah' => ['/users/create', 200, 403],
            'user edit' => ['/users/{user}/edit', 200, 403],
        ];
    }

    #[DataProvider('halaman')]
    public function test_page_status_per_role(string $url, int $statusSuperAdmin, int $statusAdmin): void
    {
        $superAdmin = User::where('email', 'superadmin@tomohonkota.go.id')->firstOrFail();
        $admin = User::where('email', 'admin@tomohonkota.go.id')->firstOrFail();

        $this->actingAs($superAdmin)->get($this->url($url))->assertStatus($statusSuperAdmin);
        $this->actingAs($admin)->get($this->url($url))->assertStatus($statusAdmin);
    }

    #[DataProvider('halaman')]
    public function test_guest_is_redirected_to_login(string $url): void
    {
        $this->get($this->url($url))->assertRedirect(route('login'));
    }

    public function test_unknown_page_uses_designed_404(): void
    {
        $this->actingAs(User::firstOrFail())
            ->get('/domains/99999')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    /**
     * Ganti placeholder {domain}, {opd}, {user} dengan id data seed.
     */
    private function url(string $pola): string
    {
        return strtr($pola, [
            '{domain}' => (string) Domain::firstOrFail()->id,
            '{opd}' => (string) Opd::firstOrFail()->id,
            '{user}' => (string) User::where('email', 'admin@tomohonkota.go.id')->firstOrFail()->id,
        ]);
    }
}
