<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Colegio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Colegio::factory()->create();
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_estudiante_can_register(): void
    {
        $colegio = Colegio::first();

        $response = $this->post('/register/estudiante', [
            'nombre' => 'Test Estudiante',
            'email' => 'estudiante@test.com',
            'colegio_id' => $colegio->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terminos' => '1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'estudiante@test.com',
            'tipo' => 'estudiante',
        ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_estudiante_is_redirected_to_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'tipo' => 'estudiante',
            'email' => 'estudiante@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'estudiante@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('estudiante.dashboard'));
    }

    public function test_docente_is_redirected_to_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'tipo' => 'docente',
            'email' => 'docente@test.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'docente@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('docente.dashboard'));
    }
}
