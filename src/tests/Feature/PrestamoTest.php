<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Libro;
use App\Models\Alumno;
use App\Models\Prestamo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrestamoTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_prestamo_reduce_el_stock_y_crea_log()
    {
        $user = User::factory()->create();
        $libro = Libro::factory()->create(['stock' => 10]);
        $alumno = Alumno::factory()->create();

        
        $this->actingAs($user)->post(route('prestamos.store'), [
            'libro_id' => $libro->id,
            'alumno_id' => $alumno->id,
            'fecha_prestamo' => now()->format('Y-m-d'),
        ]);

        // 1. El stock debe haber bajado a 9
        $this->assertDatabaseHas('libros', [
            'id' => $libro->id,
            'stock' => 9
        ]);

        // 2. Debe haberse creado un registro en la tabla movimientos
        $this->assertDatabaseHas('movimientos', [
            'accion' => 'Nuevo Préstamo',
            'user_id' => $user->id
        ]);
    }

    public function test_no_se_puede_prestar_libro_sin_stock()
    {
        $user = User::factory()->create();
        $libro = Libro::factory()->create(['stock' => 0]); // Stock 0
        $alumno = Alumno::factory()->create();

        $response = $this->actingAs($user)->post(route('prestamos.store'), [
            'libro_id' => $libro->id,
            'alumno_id' => $alumno->id,
            'fecha_prestamo' => now()->format('Y-m-d'),
        ]);

        // Debe dar error y NO crear el préstamo
        $response->assertSessionHasErrors(['libro_id']);
        $this->assertEquals(0, Prestamo::count());
    }

    public function test_devolver_libro_aumenta_stock()
    {
        $user = User::factory()->create();
        $libro = Libro::factory()->create(['stock' => 5]);
        $alumno = Alumno::factory()->create();
        
        // Creamos un préstamo manualmente en la BD
        $prestamo = Prestamo::create([
            'libro_id' => $libro->id,
            'alumno_id' => $alumno->id,
            'fecha_prestamo' => now(),
            'es_moroso' => false
        ]);

        // Acción: Devolver
        $this->actingAs($user)->patch(route('prestamos.devolver', $prestamo));

        // El stock debe haber subido a 6
        $this->assertDatabaseHas('libros', [
            'id' => $libro->id,
            'stock' => 6
        ]);
        
        // El préstamo debe tener fecha de devolución
        $this->assertNotNull($prestamo->fresh()->fecha_devolucion);
    }
}
