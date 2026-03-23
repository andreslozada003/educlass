<?php

namespace App\Services;

use App\Models\User;
use App\Models\Asignatura;
use App\Models\CalificacionesPeriodo;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

/**
 * Service ExportService - Gestiona exportaciones a Excel y PDF
 */
class ExportService
{
    /**
     * Exportar calificaciones a Excel
     */
    public function exportarCalificacionesExcel(int $asignaturaId, ?int $periodo = null, ?int $colegioId = null)
    {
        $asignatura = Asignatura::find($asignaturaId);
        $calificacionService = app(CalificacionService::class);

        // Obtener datos
        $query = User::estudiantes()
            ->where('activo', true)
            ->with(['calificacionesPeriodo' => function ($q) use ($asignaturaId, $periodo) {
                $q->where('asignatura_id', $asignaturaId);
                if ($periodo) {
                    $q->where('periodo', $periodo);
                }
            }]);

        if ($colegioId) {
            $query->where('colegio_id', $colegioId);
        }

        $estudiantes = $query->get();

        // Preparar datos para exportar
        $datos = [];
        foreach ($estudiantes as $estudiante) {
            $fila = [
                'ID' => $estudiante->id,
                'Nombre' => $estudiante->nombre,
                'Email' => $estudiante->email,
                'Colegio' => $estudiante->colegio?->nombre ?? 'N/A',
            ];

            if ($periodo) {
                $calificacion = $estudiante->calificacionesPeriodo->first();
                $fila['Período'] = $periodo;
                $fila['Promedio Juegos'] = $calificacion?->promedio_juegos ?? 0;
                $fila['Promedio Evaluaciones'] = $calificacion?->promedio_evaluaciones ?? 0;
                $fila['Promedio Ponderado'] = $calificacion?->promedio_ponderado ?? 0;
                $fila['Estado'] = ($calificacion?->aprobo ?? false) ? 'Aprobado' : 'Reprobado';
            } else {
                // Todos los períodos
                foreach ([1, 2, 3] as $p) {
                    $calificacion = $estudiante->calificacionesPeriodo->firstWhere('periodo', $p);
                    $fila["P{$p} Juegos"] = $calificacion?->promedio_juegos ?? 0;
                    $fila["P{$p} Evaluaciones"] = $calificacion?->promedio_evaluaciones ?? 0;
                    $fila["P{$p} Ponderado"] = $calificacion?->promedio_ponderado ?? 0;
                }
                $promedioAnual = $calificacionService->calcularPromedioAnual($estudiante->id, $asignaturaId);
                $fila['Promedio Anual'] = $promedioAnual;
                $fila['Estado Anual'] = $promedioAnual >= 3.0 ? 'Aprobado' : 'Reprobado';
            }

            $datos[] = $fila;
        }

        // Crear exportación
        return Excel::download(new class($datos, $asignatura->nombre) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $datos;
            protected $asignatura;

            public function __construct(array $datos, string $asignatura)
            {
                $this->datos = $datos;
                $this->asignatura = $asignatura;
            }

            public function array(): array
            {
                return $this->datos;
            }

            public function headings(): array
            {
                return array_keys($this->datos[0] ?? []);
            }
        }, "calificaciones_{$asignatura->slug}_" . date('Y-m-d') . '.xlsx');
    }

    /**
     * Exportar boletín a PDF
     */
    public function generarBoletinPDF(int $estudianteId)
    {
        $calificacionService = app(CalificacionService::class);
        $boletin = $calificacionService->generarBoletin($estudianteId);

        $pdf = Pdf::loadView('pdf.boletin', $boletin);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download("boletin_{$boletin['estudiante']->id}_" . date('Y-m-d') . '.pdf');
    }

    /**
     * Generar reporte de progreso en PDF
     */
    public function generarReporteProgresoPDF(int $estudianteId)
    {
        $estudiante = User::find($estudianteId);
        $progresionService = app(ProgresionService::class);
        $gamificacionService = app(GamificacionService::class);

        $resumenProgreso = $progresionService->getResumenProgreso($estudiante);
        $estadisticas = $gamificacionService->getEstadisticas($estudiante);

        // Progreso por asignatura
        $asignaturas = Asignatura::activas()->get();
        $progresoAsignaturas = [];

        foreach ($asignaturas as $asignatura) {
            $progresoAsignaturas[] = [
                'asignatura' => $asignatura,
                'progreso' => $progresionService->getProgresoPorAsignatura($estudiante, $asignatura->id),
            ];
        }

        $data = [
            'estudiante' => $estudiante,
            'resumen_progreso' => $resumenProgreso,
            'estadisticas' => $estadisticas,
            'progreso_asignaturas' => $progresoAsignaturas,
            'fecha_generacion' => now(),
        ];

        $pdf = Pdf::loadView('pdf.reporte_progreso', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download("reporte_progreso_{$estudiante->id}_" . date('Y-m-d') . '.pdf');
    }

    /**
     * Generar certificado de logro
     */
    public function generarCertificadoLogro(int $logroEstudianteId)
    {
        $logroEstudiante = \App\Models\LogrosEstudiante::with(['estudiante', 'logro'])->find($logroEstudianteId);

        if (!$logroEstudiante) {
            throw new \Exception('Logro no encontrado');
        }

        $data = [
            'estudiante' => $logroEstudiante->estudiante,
            'logro' => $logroEstudiante->logro,
            'fecha_obtenido' => $logroEstudiante->fecha_obtenido,
            'fecha_generacion' => now(),
        ];

        $pdf = Pdf::loadView('pdf.certificado_logro', $data);
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download("certificado_{$logroEstudiante->logro->slug}_" . date('Y-m-d') . '.pdf');
    }

    /**
     * Exportar ranking a Excel
     */
    public function exportarRankingExcel(string $categoria, ?int $asignaturaId = null, int $limite = 50)
    {
        $query = \App\Models\Ranking::with('estudiante')
            ->where('categoria', $categoria)
            ->orderBy('posicion')
            ->limit($limite);

        if ($asignaturaId) {
            $query->where('asignatura_id', $asignaturaId);
        } else {
            $query->whereNull('asignatura_id');
        }

        $rankings = $query->get();

        $datos = [];
        foreach ($rankings as $ranking) {
            $datos[] = [
                'Posición' => $ranking->posicion,
                'Estudiante' => $ranking->estudiante->nombre,
                'Colegio' => $ranking->estudiante->colegio?->nombre ?? 'N/A',
                'Puntaje Total' => $ranking->puntaje_total,
                'Nivel Alcanzado' => $ranking->nivel_nombre,
            ];
        }

        $categoriaNombre = match ($categoria) {
            'juegos' => 'Juegos',
            'evaluaciones' => 'Evaluaciones',
            'temas' => 'Temas',
            'general' => 'General',
            default => 'Ranking',
        };

        return Excel::download(new class($datos) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $datos;

            public function __construct(array $datos)
            {
                $this->datos = $datos;
            }

            public function array(): array
            {
                return $this->datos;
            }

            public function headings(): array
            {
                return array_keys($this->datos[0] ?? []);
            }
        }, "ranking_{$categoria}_" . date('Y-m-d') . '.xlsx');
    }

    /**
     * Generar reporte estadístico para docentes
     */
    public function generarReporteEstadisticoPDF(int $asignaturaId, ?int $periodo = null)
    {
        $asignatura = Asignatura::find($asignaturaId);
        $calificacionService = app(CalificacionService::class);

        $resumen = $calificacionService->getResumenPorAsignatura($asignaturaId, $periodo);

        // Estadísticas adicionales
        $temas = $asignatura->temasActivos;
        $estadisticasTemas = [];

        foreach ($temas as $tema) {
            $totalCompletados = \App\Models\ProgresoEstudiante::where('tema_id', $tema->id)
                ->completados()
                ->count();

            $estadisticasTemas[] = [
                'tema' => $tema,
                'total_completados' => $totalCompletados,
            ];
        }

        $data = [
            'asignatura' => $asignatura,
            'periodo' => $periodo,
            'resumen' => $resumen,
            'estadisticas_temas' => $estadisticasTemas,
            'fecha_generacion' => now(),
        ];

        $pdf = Pdf::loadView('pdf.reporte_estadistico', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf->download("reporte_estadistico_{$asignatura->slug}_" . date('Y-m-d') . '.pdf');
    }

    /**
     * Exportar lista de estudiantes a Excel
     */
    public function exportarListaEstudiantes(?int $colegioId = null)
    {
        $query = User::estudiantes()
            ->where('activo', true)
            ->with('colegio');

        if ($colegioId) {
            $query->where('colegio_id', $colegioId);
        }

        $estudiantes = $query->get();

        $datos = [];
        foreach ($estudiantes as $estudiante) {
            $datos[] = [
                'ID' => $estudiante->id,
                'Nombre' => $estudiante->nombre,
                'Email' => $estudiante->email,
                'Teléfono' => $estudiante->telefono ?? 'N/A',
                'Colegio' => $estudiante->colegio?->nombre ?? 'N/A',
                'Fecha Registro' => $estudiante->created_at->format('d/m/Y'),
                'Último Acceso' => $estudiante->ultimo_acceso?->format('d/m/Y H:i') ?? 'Nunca',
            ];
        }

        return Excel::download(new class($datos) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
            protected $datos;

            public function __construct(array $datos)
            {
                $this->datos = $datos;
            }

            public function array(): array
            {
                return $this->datos;
            }

            public function headings(): array
            {
                return array_keys($this->datos[0] ?? []);
            }
        }, 'lista_estudiantes_' . date('Y-m-d') . '.xlsx');
    }
}
