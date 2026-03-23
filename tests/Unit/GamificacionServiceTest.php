<?php

namespace Tests\Unit;

use App\Services\GamificacionService;
use Tests\TestCase;

class GamificacionServiceTest extends TestCase
{
    protected GamificacionService $gamificacionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gamificacionService = new GamificacionService();
    }

    public function test_calcular_puntaje_returns_correct_score(): void
    {
        $result = $this->gamificacionService->calcularPuntaje(100, 60, 120, 0);

        $this->assertEquals(100, $result['puntaje_base']);
        $this->assertGreaterThan(0, $result['bonificacion_tiempo']);
        $this->assertEquals(0, $result['bonificacion_racha']);
        $this->assertGreaterThan(100, $result['puntaje_final']);
    }

    public function test_calcular_puntaje_with_racha_bonus(): void
    {
        $result = $this->gamificacionService->calcularPuntaje(100, 60, 120, 5);

        $this->assertGreaterThan(0, $result['bonificacion_racha']);
        $this->assertGreaterThan(100, $result['puntaje_final']);
    }

    public function test_calcular_puntaje_without_time_limit(): void
    {
        $result = $this->gamificacionService->calcularPuntaje(100, 60, null, 0);

        $this->assertEquals(0, $result['bonificacion_tiempo']);
        $this->assertEquals(100, $result['puntaje_final']);
    }

    public function test_calcular_puntaje_with_no_time_remaining(): void
    {
        $result = $this->gamificacionService->calcularPuntaje(100, 120, 120, 0);

        $this->assertEquals(0, $result['bonificacion_tiempo']);
    }
}
