<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Route uji untuk memeriksa middleware role tanpa bergantung pada fitur Fase 4.
        Route::middleware(['web', 'auth', 'role:super_admin'])
            ->get('/_uji/khusus-super-admin', fn () => 'ok');
    }

    public function test_super_admin_passes_super_admin_only_route(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get('/_uji/khusus-super-admin')
            ->assertOk();
    }

    public function test_admin_gets_designed_403_page_on_super_admin_only_route(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/_uji/khusus-super-admin')
            ->assertForbidden()
            ->assertSee('Akses ditolak');
    }

    public function test_admin_and_super_admin_can_access_opd_and_domains(): void
    {
        foreach ([User::factory()->create(), User::factory()->superAdmin()->create()] as $user) {
            $this->actingAs($user)->get(route('opd.index'))->assertOk();
            $this->actingAs($user)->get(route('domains.index'))->assertOk();
        }
    }
}
