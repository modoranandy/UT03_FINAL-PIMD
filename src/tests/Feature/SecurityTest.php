<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_ver_logs()
    {
        
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.logs'));

        $response->assertStatus(200); // OK
    }

    public function test_usuario_normal_NO_puede_ver_logs()
    {
        // Creamos usuario normal
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.logs'));

        $response->assertStatus(403); // PROHIBIDO
    }

    public function test_usuario_normal_NO_puede_entrar_a_gestion_usuarios()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertStatus(403); // PROHIBIDO
    }
}
