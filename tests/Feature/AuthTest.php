<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_shown_to_guests(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk ke akun Anda');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('domains.index'))->assertRedirect(route('login'));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create(['email' => 'admin@tomohonkota.go.id']);

        $this->post(route('login.store'), [
            'email' => 'admin@tomohonkota.go.id',
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_regenerates_session(): void
    {
        User::factory()->create(['email' => 'admin@tomohonkota.go.id']);

        $this->get(route('login'));
        $sessionLama = session()->getId();

        $this->post(route('login.store'), [
            'email' => 'admin@tomohonkota.go.id',
            'password' => 'password',
        ]);

        $this->assertNotSame($sessionLama, session()->getId());
    }

    public function test_wrong_password_shows_generic_error(): void
    {
        User::factory()->create(['email' => 'admin@tomohonkota.go.id']);

        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'admin@tomohonkota.go.id',
            'password' => 'salah',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Email/Password salah.']);

        $this->assertGuest();
    }

    public function test_unknown_email_shows_same_generic_error(): void
    {
        $this->post(route('login.store'), [
            'email' => 'tidakada@tomohonkota.go.id',
            'password' => 'password',
        ])->assertSessionHasErrors(['email' => 'Email/Password salah.']);
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        User::factory()->create(['email' => 'admin@tomohonkota.go.id']);

        foreach (range(1, 5) as $percobaan) {
            $this->post(route('login.store'), ['email' => 'admin@tomohonkota.go.id', 'password' => 'salah']);
        }

        $this->post(route('login.store'), ['email' => 'admin@tomohonkota.go.id', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('login'))
            ->assertRedirect(route('home'));
    }
}
