<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->superAdmin()->create();
    }

    public function test_admin_gets_403_on_every_user_route(): void
    {
        $admin = User::factory()->create();
        $lain = User::factory()->create();

        $this->actingAs($admin);

        $this->get(route('users.index'))->assertForbidden()->assertSee('Akses ditolak');
        $this->get(route('users.create'))->assertForbidden();
        $this->post(route('users.store'), [])->assertForbidden();
        $this->get(route('users.edit', $lain))->assertForbidden();
        $this->put(route('users.update', $lain), [])->assertForbidden();
        $this->delete(route('users.destroy', $lain))->assertForbidden();

        $this->assertModelExists($lain);
    }

    public function test_user_menu_is_hidden_for_admin(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertDontSee('Manajemen User');

        $this->actingAs($this->superAdmin)
            ->get(route('dashboard'))
            ->assertSee('Manajemen User');
    }

    public function test_super_admin_can_list_and_search_users(): void
    {
        User::factory()->create(['name' => 'Budi Lumi']);
        User::factory()->create(['name' => 'Sari Wenas']);

        $this->actingAs($this->superAdmin)
            ->get(route('users.index', ['q' => 'budi']))
            ->assertOk()
            ->assertSee('Budi Lumi')
            ->assertDontSee('Sari Wenas');
    }

    public function test_super_admin_can_create_user_with_hashed_password(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'Admin Baru',
                'email' => 'Baru@TomohonKota.go.id',
                'role' => 'admin',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
            ])
            ->assertRedirect(route('users.index'));

        $user = User::where('email', 'baru@tomohonkota.go.id')->firstOrFail();
        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_create_validates_email_uniqueness_and_password_confirmation(): void
    {
        User::factory()->create(['email' => 'ada@tomohonkota.go.id']);

        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'X',
                'email' => 'ada@tomohonkota.go.id',
                'role' => 'admin',
                'password' => 'rahasia123',
                'password_confirmation' => 'berbeda123',
            ])
            ->assertSessionHasErrors(['email' => 'Email ini sudah dipakai akun lain.', 'password']);
    }

    public function test_update_without_password_keeps_old_password(): void
    {
        $user = User::factory()->create();
        $hashLama = $user->password;

        $this->actingAs($this->superAdmin)
            ->put(route('users.update', $user), [
                'name' => 'Nama Baru',
                'email' => $user->email,
                'role' => 'super_admin',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('users.index'));

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame(UserRole::SuperAdmin, $user->role);
        $this->assertSame($hashLama, $user->password);
    }

    public function test_super_admin_can_delete_other_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($this->superAdmin)
            ->delete(route('users.destroy', $user))
            ->assertRedirect(route('users.index'));

        $this->assertModelMissing($user);
    }

    public function test_super_admin_cannot_delete_own_account(): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('users.index'))
            ->delete(route('users.destroy', $this->superAdmin))
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');

        $this->assertModelExists($this->superAdmin);
    }

    public function test_super_admin_cannot_demote_own_account(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('users.update', $this->superAdmin), [
                'name' => $this->superAdmin->name,
                'email' => $this->superAdmin->email,
                'role' => 'admin',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::SuperAdmin, $this->superAdmin->fresh()->role);
    }
}
