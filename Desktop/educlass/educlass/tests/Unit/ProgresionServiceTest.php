<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Tema;
use App\Models\Asignatura;
use App\Models\ProgresoEstudiante;
use App\Services\ProgresionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProgresionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProgresionService $progresionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->progresionService = new ProgresionService();
    }

    public function test_puede_acceder_tema_returns_true_for_available_topic(): void
    {
        $estudiante = User::factory()->create(['tipo' => 'estudiante']);
        $asignatura = Asignatura::factory()->create();
        $tema = Tema::factory()->create([
            'asignatura_id' => $asignatura->id,
            'activo' => true,
        ]);

        ProgresoEstudiante::create([
            'estudiante_id' => $estudiante->id,
            'tema_id' => $tema->id,
            'estado' => 'disponible',
        ]);

        $result = $this->progresionService->puedeAccederTema($estudiante, $tema);

        $this->assertTrue($result);
    }

    public function test_puede_acceder_tema_returns_false_for_blocked_topic(): void
    {
        $estudiante = User::factory()->create(['tipo' => 'estudiante']);
        $asignatura = Asignatura::factory()->create();
        $tema = Tema::factory()->create([
            'asignatura_id' => $asignatura->id,
            'activo' => true,
        ]);

        ProgresoEstudiante::create([
            'estudiante_id' => $estudiante->id,
            'tema_id' => $tema->id,
            'estado' => 'bloqueado',
        ]);

        $result = $this->progresionService->puedeAccederTema($estudiante, $tema);

        $this->assertFalse($result);
    }

    public function test_calcular_nivel_actual_returns_correct_level(): void
    {
        $this->assertEquals(1, $this->progresionService->calcularNivelActual(0));
        $this->assertEquals(1, $this->progresionService->calcularNivelActual(10));
        $this->assertEquals(2, $this->progresionService->calcularNivelActual(30));
        $this->assertEquals(3, $this->progresionService->calcularNivelActual(60));
        $this->assertEquals(4, $this->progresionService->calcularNivelActual(80));
        $this->assertEquals(4, $this->progresionService->calcularNivelActual(100));
    }

    public function test_completar_tema_marks_topic_as_completed(): void
    {
        $estudiante = User::factory()->create(['tipo' => 'estudiante']);
        $asignatura = Asignatura::factory()->create();
        $tema = Tema::factory()->create([
            'asignatura_id' => $asignatura->id,
            'activo' => true,
        ]);

        ProgresoEstudiante::create([
            'estudiante_id' => $estudiante->id,
            'tema_id' => $tema->id,
            'estado' => 'en_progreso',
        ]);

        $result = $this->progresionService->completarTema($estudiante, $tema);

        $this->assertTrue($result['exito']);
        $this->assertDatabaseHas('progreso_estudiantes', [
            'estudiante_id' => $estudiante->id,
            'tema_id' => $tema->id,
            'estado' => 'completado',
        ]);
    }
}
