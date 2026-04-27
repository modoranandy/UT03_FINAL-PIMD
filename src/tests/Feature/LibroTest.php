<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Libro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LibroTest extends TestCase
{
    use RefreshDatabase; 

    public function test_un_usuario_puede_crear_un_libro()
    {
        // 1. Creamos un usuario
        $user = User::factory()->create();

        // 2. Actuamos como ese usuario (Login)
        $response = $this->actingAs($user)->post('/libros', [
            'isbn' => '1234567890123',
            'titulo' => 'Libro de Prueba',
            'autor' => 'Test Autor',
            'stock' => 10
        ]);

        // 3. Verificamos que redirige (éxito)
        $response->assertStatus(302);
        $response->assertRedirect(route('libros.index'));

        // 4. Verificamos que está en la base de datos
        $this->assertDatabaseHas('libros', [
            'titulo' => 'Libro de Prueba',
            'stock' => 10
        ]);
    }

    public function test_no_se_puede_crear_libro_sin_titulo()
    {
        $user = User::factory()->create();

        // Intentamos enviar el título vacío
        $response = $this->actingAs($user)->post('/libros', [
            'isbn' => '1234567890123',
            'titulo' => '', 
            'autor' => 'Test Autor',
            'stock' => 5
        ]);

        // Debe dar error en la sesión
        $response->assertSessionHasErrors(['titulo']);
    }
}
