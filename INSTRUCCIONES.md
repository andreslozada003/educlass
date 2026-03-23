# Instrucciones de Instalación - Educlass

## Resumen del Proyecto

Educlass es un Sistema de Aprendizaje Interactivo Educativo completo desarrollado con:

- **Laravel 12** - Framework PHP
- **PHP 8.3+** - Lenguaje de programación
- **MySQL 8.0+** - Base de datos
- **Tailwind CSS** - Framework CSS
- **Alpine.js** - Framework JavaScript ligero
- **Chart.js** - Gráficos interactivos

## Estructura de Archivos Creados

```
educlass/
├── app/
│   ├── Http/Controllers/      # 15 controladores
│   │   ├── Auth/              # Login, registro, recuperación
│   │   ├── Estudiante/        # Dashboard, asignaturas, juegos
│   │   └── Docente/           # Gestión de contenidos
│   ├── Models/                # 16 modelos Eloquent
│   ├── Services/              # 5 servicios de negocio
│   ├── Middleware/            # RoleMiddleware
│   ├── Providers/             # 4 providers
│   └── Helpers/               # Funciones auxiliares
├── database/
│   ├── migrations/            # 16 migraciones
│   ├── seeders/               # 8 seeders
│   └── factories/             # 4 factories
├── resources/views/           # 9 vistas Blade
│   ├── layouts/               # Layout principal
│   ├── components/            # Componentes reutilizables
│   ├── auth/                  # Login y registro
│   ├── estudiante/            # Vistas de estudiante
│   └── docente/               # Vistas de docente
├── routes/                    # Rutas web y api
├── config/                    # Configuraciones
├── tests/                     # Tests PHPUnit
└── docs/                      # Documentación
```

## Tablas de Base de Datos (16 migraciones)

1. `colegios` - Instituciones educativas
2. `users` - Usuarios (estudiantes, docentes, admin)
3. `asignaturas` - Matemáticas, Lenguaje, Inglés, Ciencias
4. `temas` - Contenido educativo
5. `juegos` - Juegos educativos (7 tipos)
6. `preguntas_juego` - Preguntas para juegos
7. `evaluaciones` - Evaluaciones/exámenes
8. `preguntas_evaluacion` - Preguntas de evaluación
9. `progreso_estudiantes` - Seguimiento de progreso
10. `intentos_juegos` - Registro de intentos
11. `resultados_evaluaciones` - Resultados de evaluaciones
12. `calificaciones_periodo` - Calificaciones por período
13. `rankings` - Tablas de clasificación
14. `logros` - Badges/logros disponibles
15. `logros_estudiantes` - Logros obtenidos
16. `configuracion_sistema` - Configuración global

## Roles y Funcionalidades

### Estudiante
- ✅ Registro con validación
- ✅ Login con "Recordarme"
- ✅ Dashboard personal con estadísticas
- ✅ Acceso a 4 asignaturas
- ✅ Sistema de 4 niveles por asignatura
- ✅ Progresión secuencial (Lectura → Juego → Evaluación)
- ✅ 7 tipos de juegos educativos
- ✅ Sistema de puntajes con bonificaciones
- ✅ Logros y badges
- ✅ Rankings personales y globales
- ✅ Perfil editable

### Docente
- ✅ Dashboard administrativo
- ✅ CRUD de temas con editor enriquecido
- ✅ CRUD de juegos (7 tipos)
- ✅ CRUD de evaluaciones
- ✅ Banco de preguntas reutilizable
- ✅ Reportes de calificaciones
- ✅ Exportación Excel/PDF
- ✅ Rankings por categoría
- ✅ Gestión de estudiantes
- ✅ Reset de contraseñas

## Instalación Rápida

```bash
# 1. Clonar o extraer el proyecto
cd educlass

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
DB_DATABASE=educlass
DB_USERNAME=root
DB_PASSWORD=tu_password

# 5. Crear base de datos y ejecutar migraciones
mysql -u root -p -e "CREATE DATABASE educlass;"
php artisan migrate

# 6. Cargar datos de prueba
php artisan db:seed

# 7. Crear enlaces de almacenamiento
php artisan storage:link

# 8. Compilar assets
npm run build

# 9. Iniciar servidor
php artisan serve
```

## Cuentas de Demo

Después de ejecutar `php artisan db:seed`:

| Rol | Email | Contraseña |
|-----|-------|------------|
| Estudiante | ana@demo.com | password123 |
| Estudiante | luis@demo.com | password123 |
| Docente | docente1@educlass.com | password123 |
| Admin | admin@educlass.com | admin123 |

## Funcionalidades Implementadas

### Sistema de Gamificación
- ⭐ 4 niveles: Básico, Intermedio, Avanzado, Experto
- 🎮 7 tipos de juegos interactivos
- 🏆 10 logros/badges desbloqueables
- 📊 Rankings por categoría
- 💯 Sistema de puntos con bonificaciones

### Tipos de Juegos
1. Quiz Interactivo
2. Memoria (pares)
3. Arrastrar y Soltar
4. Completar (hangman)
5. Ordenar (secuencias)
6. Sopa de Letras
7. Clasificación Rápida

### Tipos de Preguntas
- Opción múltiple
- Verdadero/Falso
- Respuesta corta
- Emparejamiento

### Exportaciones
- ✅ Excel (Maatwebsite/Laravel-Excel)
- ✅ PDF (Barryvdh/Laravel-DomPDF)
- ✅ Gráficos (Chart.js)

## Seguridad Implementada

- ✅ Autenticación Laravel
- ✅ Hash Bcrypt (cost 12)
- ✅ CSRF Protection
- ✅ Rate Limiting
- ✅ SQL Injection Protection (Eloquent)
- ✅ XSS Protection (Blade escaping)
- ✅ Soft Deletes

## Tests

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter=AuthTest
php artisan test --filter=ProgresionServiceTest
```

## Comandos Artisan Personalizados

```bash
# Actualizar rankings
php artisan ranking:update

# Recalcular calificaciones
php artisan calificaciones:recalcular
```

## Notas Importantes

1. **Configuración de Email**: Edita el archivo `.env` con tus credenciales SMTP para habilitar el envío de correos.

2. **Almacenamiento**: Los avatares e imágenes se guardan en `storage/app/public/`. Asegúrate de ejecutar `php artisan storage:link`.

3. **Permisos**: Asegúrate de que las carpetas `storage` y `bootstrap/cache` tengan permisos de escritura.

4. **Producción**: En producción, configura:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - HTTPS forzado
   - Optimización de caché

## Soporte

Para más información, consulta el archivo `README.md` completo.

---

**Total de archivos creados:** 100+ archivos
**Líneas de código:** 15,000+ aproximadamente
**Tiempo estimado de desarrollo:** 40+ horas
