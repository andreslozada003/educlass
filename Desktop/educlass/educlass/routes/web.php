<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\NotificationController;

// Rutas públicas
Route::get('/', function () {
    return redirect()->route('login');
});

// Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    
    Route::get('/register/estudiante', [RegisterController::class, 'showEstudianteRegistrationForm'])->name('register.estudiante');
    Route::post('/register/estudiante', [RegisterController::class, 'registerEstudiante']);
    
    Route::get('/register/docente', [RegisterController::class, 'showDocenteRegistrationForm'])->name('register.docente');
    Route::post('/register/docente', [RegisterController::class, 'registerDocente']);
    
    Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/logout', [LoginController::class, 'logout'])->middleware('auth');

// Notificaciones (disponible para cualquier usuario autenticado)
Route::middleware('auth')->prefix('notificaciones')->name('notificaciones.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/{id}/abrir', [NotificationController::class, 'open'])->name('open');
    Route::post('/leer-todas', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::post('/{id}/leer', [NotificationController::class, 'markAsRead'])->name('read');
});

// Rutas de Estudiante
Route::middleware(['auth', 'role:estudiante'])
    ->prefix('estudiante')
    ->name('estudiante.')
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Estudiante\DashboardController::class, 'index'])->name('dashboard');
        
        // Asignaturas
        Route::get('/asignaturas', [\App\Http\Controllers\Estudiante\AsignaturaController::class, 'index'])->name('asignaturas.index');
        Route::get('/asignaturas/{slug}', [\App\Http\Controllers\Estudiante\AsignaturaController::class, 'show'])->name('asignaturas.show');
        
        // Temas
        Route::get('/temas/{slug}', [\App\Http\Controllers\Estudiante\TemaController::class, 'show'])->name('temas.show');
        Route::post('/temas/{temaId}/lectura', [\App\Http\Controllers\Estudiante\TemaController::class, 'actualizarLectura'])->name('temas.lectura');
        Route::post('/temas/{temaId}/completar', [\App\Http\Controllers\Estudiante\TemaController::class, 'completar'])->name('temas.completar');
        
        // Juegos
        Route::get('/juegos/{juegoId}/jugar', [\App\Http\Controllers\Estudiante\JuegoController::class, 'jugar'])->name('juegos.jugar');
        Route::post('/juegos/{juegoId}/resultado', [\App\Http\Controllers\Estudiante\JuegoController::class, 'guardarResultado'])->name('juegos.guardar');
        Route::get('/juegos/intento/{intentoId}', [\App\Http\Controllers\Estudiante\JuegoController::class, 'resultado'])->name('juegos.resultado');
        Route::get('/juegos/historial', [\App\Http\Controllers\Estudiante\JuegoController::class, 'historial'])->name('juegos.historial');
        
        // ============================================================
        // EVALUACIONES - CORREGIDO PARA COINCIDIR CON TUS BLADE
        // ============================================================
        
        // Index - Lista de evaluaciones (pendientes y completadas)
        Route::get('/evaluaciones', [\App\Http\Controllers\Estudiante\EvaluacionController::class, 'index'])->name('evaluaciones.index');
        
        // Realizar evaluación (cambiado de 'realizar' a 'take' para coincidir con tus Blade)
        Route::get('/evaluaciones/{evaluacion}/realizar', [\App\Http\Controllers\Estudiante\EvaluacionController::class, 'take'])->name('evaluaciones.take');
        
        // Enviar resultados (cambiado de 'guardar' a 'submit' para coincidir con tus Blade)
        Route::post('/evaluaciones/{evaluacion}/enviar', [\App\Http\Controllers\Estudiante\EvaluacionController::class, 'submit'])->name('evaluaciones.submit');
        
        // Ver resultado específico
        Route::get('/evaluaciones/resultado/{resultado}', [\App\Http\Controllers\Estudiante\EvaluacionController::class, 'resultado'])->name('evaluaciones.resultado');
        
        // Historial de evaluaciones
        Route::get('/evaluaciones/historial', [\App\Http\Controllers\Estudiante\EvaluacionController::class, 'historial'])->name('evaluaciones.historial');
        
        // ============================================================
        
        // Progreso
        Route::get('/progreso', [\App\Http\Controllers\Estudiante\ProgresoController::class, 'index'])->name('progreso.index');
        
        // Perfil
        Route::get('/perfil', [\App\Http\Controllers\Estudiante\PerfilController::class, 'show'])->name('perfil.show');
        Route::put('/perfil', [\App\Http\Controllers\Estudiante\PerfilController::class, 'update'])->name('perfil.update');
        Route::post('/perfil/avatar', [\App\Http\Controllers\Estudiante\PerfilController::class, 'updateAvatar'])->name('perfil.avatar');
        Route::post('/perfil/password', [\App\Http\Controllers\Estudiante\PerfilController::class, 'cambiarPassword'])->name('perfil.password');
        Route::delete('/perfil', [\App\Http\Controllers\Estudiante\PerfilController::class, 'eliminarCuenta'])->name('perfil.eliminar');
    });

// Rutas de Docente
Route::middleware(['auth', 'role:docente'])
    ->prefix('docente')
    ->name('docente.')
    ->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Docente\DashboardController::class, 'index'])->name('dashboard');
        
        // Gestión de Temas
        Route::resource('temas', \App\Http\Controllers\Docente\TemaManagementController::class);
        Route::post('/temas/reorder', [\App\Http\Controllers\Docente\TemaManagementController::class, 'reorder'])->name('temas.reorder');
        Route::post('/temas/{id}/duplicate', [\App\Http\Controllers\Docente\TemaManagementController::class, 'duplicate'])->name('temas.duplicate');
        
        // Gestión de Juegos
        Route::resource('juegos', \App\Http\Controllers\Docente\JuegoManagementController::class);
        Route::get('/juegos/{juegoId}/preview', [\App\Http\Controllers\Docente\JuegoManagementController::class, 'preview'])->name('juegos.preview');
        Route::get('/juegos/{juegoId}/preguntas', [\App\Http\Controllers\Docente\JuegoManagementController::class, 'preguntas'])->name('juegos.preguntas');
        Route::post('/juegos/{juegoId}/preguntas', [\App\Http\Controllers\Docente\JuegoManagementController::class, 'agregarPregunta'])->name('juegos.preguntas.agregar');
        Route::delete('/juegos/{juegoId}/preguntas/{preguntaId}', [\App\Http\Controllers\Docente\JuegoManagementController::class, 'eliminarPregunta'])->name('juegos.preguntas.eliminar');
        
        // Gestión de Evaluaciones
        Route::resource('evaluaciones', \App\Http\Controllers\Docente\EvaluacionManagementController::class);
        Route::get('/evaluaciones/{evaluacionId}/preguntas', [\App\Http\Controllers\Docente\EvaluacionManagementController::class, 'preguntas'])->name('evaluaciones.preguntas');
        Route::post('/evaluaciones/{evaluacionId}/preguntas', [\App\Http\Controllers\Docente\EvaluacionManagementController::class, 'agregarPregunta'])->name('evaluaciones.preguntas.agregar');
        Route::delete('/evaluaciones/{evaluacionId}/preguntas/{preguntaId}', [\App\Http\Controllers\Docente\EvaluacionManagementController::class, 'eliminarPregunta'])->name('evaluaciones.preguntas.eliminar');
        Route::get('/evaluaciones/{evaluacionId}/resultados', [\App\Http\Controllers\Docente\EvaluacionManagementController::class, 'resultados'])->name('evaluaciones.resultados');
        Route::post('/evaluaciones/{evaluacionId}/resultados/{estudianteId}/reiniciar-intentos', [\App\Http\Controllers\Docente\EvaluacionManagementController::class, 'reiniciarIntentosEstudiante'])->name('evaluaciones.resultados.reiniciar-intentos');
        
        // Calificaciones
        Route::get('/calificaciones', [\App\Http\Controllers\Docente\CalificacionesController::class, 'index'])->name('calificaciones.index');
        Route::get('/calificaciones/estudiante/{estudianteId}', [\App\Http\Controllers\Docente\CalificacionesController::class, 'showEstudiante'])->name('calificaciones.estudiante');
        Route::get('/calificaciones/exportar', [\App\Http\Controllers\Docente\CalificacionesController::class, 'exportarExcel'])->name('calificaciones.exportar');
        Route::post('/calificaciones/recalcular', [\App\Http\Controllers\Docente\CalificacionesController::class, 'recalcular'])->name('calificaciones.recalcular');
        Route::get('/calificaciones/boletin/{estudianteId}', [\App\Http\Controllers\Docente\CalificacionesController::class, 'generarBoletin'])->name('calificaciones.boletin');
        
        // Rankings
        Route::get('/rankings', [\App\Http\Controllers\Docente\RankingController::class, 'index'])->name('rankings.index');
        Route::post('/rankings/actualizar', [\App\Http\Controllers\Docente\RankingController::class, 'actualizar'])->name('rankings.actualizar');
        Route::get('/rankings/exportar', [\App\Http\Controllers\Docente\RankingController::class, 'exportar'])->name('rankings.exportar');
        
        // Gestión de Estudiantes
        Route::get('/estudiantes', [\App\Http\Controllers\Docente\EstudianteManagementController::class, 'index'])->name('estudiantes.index');
        Route::get('/estudiantes/exportar',[\App\Http\Controllers\Docente\EstudianteManagementController::class, 'exportar'])->name('estudiantes.export');
        Route::get('/estudiantes/{id}', [\App\Http\Controllers\Docente\EstudianteManagementController::class, 'show'])->name('estudiantes.show');
        Route::post('/estudiantes', [\App\Http\Controllers\Docente\EstudianteManagementController::class, 'store'])->name('estudiantes.store');
        Route::post('/estudiantes/{id}/reset-password', [\App\Http\Controllers\Docente\EstudianteManagementController::class, 'resetPassword'])->name('estudiantes.reset-password');
        Route::post('/estudiantes/{id}/desactivar', [\App\Http\Controllers\Docente\EstudianteManagementController::class, 'desactivar'])->name('estudiantes.desactivar');
        Route::post('/estudiantes/{id}/reactivar', [\App\Http\Controllers\Docente\EstudianteManagementController::class, 'reactivar'])->name('estudiantes.reactivar');
        Route::post('/estudiantes/{id}/mensaje', [\App\Http\Controllers\Docente\EstudianteManagementController::class, 'enviarMensaje'])->name('estudiantes.mensaje');

        // Recuperacion de contrasena de estudiantes (atencion docente)
        Route::get('/recuperaciones', [\App\Http\Controllers\Docente\PasswordRecoveryRequestController::class, 'index'])->name('recuperaciones.index');
        Route::post('/recuperaciones/{id}/responder', [\App\Http\Controllers\Docente\PasswordRecoveryRequestController::class, 'responder'])->name('recuperaciones.responder');
    });
