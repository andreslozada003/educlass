# Educlass - Sistema de Aprendizaje Educativo Interactivo

Sistema educativo gamificado desarrollado con Laravel 12, PHP 8.3+ y MySQL 8.0+. Permite a estudiantes aprender a través de juegos interactivos y evaluaciones, mientras que los docentes pueden gestionar contenido, calificaciones y monitorear el progreso.

## 🚀 Características

### Para Estudiantes
- **4 Asignaturas**: Matemáticas, Ciencias, Lenguaje y Sociales
- **Progresión Secuencial**: 4 niveles por asignatura con desbloqueo progresivo
- **7 Tipos de Juegos**: Quiz, Memoria, Rompecabezas, Sopa de Letras, Ordenar, Verdadero/Falso, Completar
- **Sistema de Puntos**: Gana puntos por completar juegos y evaluaciones
- **Logros y Badges**: Desbloquea reconocimientos por tus logros
- **Rankings**: Compite con otros estudiantes por el primer lugar
- **Evaluaciones**: Diagnósticas, formativas y sumativas

### Para Docentes
- **Gestión de Contenido**: Crea y edita temas, juegos y evaluaciones
- **Calificaciones**: Sistema automático con fórmula 30% juegos + 70% evaluaciones
- **Reportes**: Exporta calificaciones a Excel y PDF
- **Rankings**: Visualiza el desempeño de los estudiantes
- **Monitoreo**: Seguimiento detallado del progreso de cada estudiante

## 📋 Requisitos

- PHP 8.3+
- MySQL 8.0+
- Composer 2.0+
- Node.js 18+
- NPM 9+

## 🔧 Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/tu-usuario/educlass.git
cd educlass
```

2. **Instalar dependencias**
```bash
composer install
npm install
```

3. **Configurar el entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar la base de datos**
Edita el archivo `.env` con tus credenciales:
```env
DB_DATABASE=educlass
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate --seed
```

6. **Compilar assets**
```bash
npm run build
```

7. **Iniciar el servidor**
```bash
php artisan serve
```

## 🗄️ Estructura de la Base de Datos

El sistema cuenta con 16 tablas principales:

| Tabla | Descripción |
|-------|-------------|
| `colegios` | Instituciones educativas |
| `users` | Usuarios (estudiantes y docentes) |
| `asignaturas` | Materias académicas |
| `temas` | Contenido por asignatura y nivel |
| `juegos` | Juegos educativos |
| `preguntas_juego` | Preguntas para cada juego |
| `evaluaciones` | Evaluaciones por tema |
| `preguntas_evaluacion` | Preguntas de evaluaciones |
| `progreso_estudiantes` | Progreso por estudiante y asignatura |
| `intentos_juegos` | Registro de intentos de juegos |
| `resultados_evaluaciones` | Resultados de evaluaciones |
| `calificaciones_periodo` | Calificaciones finales |
| `rankings` | Posiciones en rankings |
| `logros` | Logros disponibles |
| `logros_estudiantes` | Logros obtenidos |
| `configuracion_sistema` | Configuración general |

## 🎮 Tipos de Juegos

1. **Quiz**: Selección múltiple con opciones
2. **Memoria**: Juego de cartas para emparejar
3. **Rompecabezas**: Armar imágenes o conceptos
4. **Sopa de Letras**: Buscar palabras
5. **Ordenar**: Secuenciar elementos
6. **Verdadero/Falso**: Preguntas de dos opciones
7. **Completar**: Llenar espacios en blanco

## 📊 Sistema de Calificación

La nota final se calcula con la siguiente fórmula:

```
Nota Final = (Promedio Juegos × 0.30) + (Promedio Evaluaciones × 0.70)
```

- **Aprobación**: 60% o más
- **Períodos**: Primer, segundo, tercer y cuarto período

## 👥 Roles de Usuario

### Estudiante
- Registro y autenticación
- Dashboard con progreso
- Acceso a asignaturas y temas
- Juegos interactivos
- Evaluaciones
- Visualización de calificaciones
- Rankings

### Docente
- Gestión completa de contenido
- Creación de temas, juegos y evaluaciones
- Calificación y reportes
- Exportación a Excel/PDF
- Visualización de rankings
- Seguimiento de estudiantes

## 🛠️ Servicios Principales

### ProgresionService
Gestiona el progreso secuencial de los estudiantes:
- Verificación de temas completados
- Desbloqueo de niveles
- Cálculo de porcentaje de progreso

### GamificacionService
Maneja la gamificación:
- Cálculo de puntos
- Sistema de niveles
- Logros y badges
- Actualización de rankings

### CalificacionService
Gestiona las calificaciones:
- Cálculo de promedios
- Fórmula ponderada
- Generación de reportes

### JuegoEngineService
Motor de juegos:
- Generación de datos para cada tipo de juego
- Validación de respuestas
- Cálculo de puntuaciones

### ExportService
Exportación de datos:
- Excel (usando PhpSpreadsheet)
- PDF (usando DomPDF)

## 📁 Estructura del Proyecto

```
educlass/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/           # Login, registro, recuperación
│   │   │   ├── Estudiante/     # Panel de estudiante
│   │   │   └── Docente/        # Panel de docente
│   │   └── Middleware/
│   │       └── RoleMiddleware.php  # Control de roles
│   ├── Models/                 # 16 modelos Eloquent
│   └── Services/               # 5 servicios de negocio
├── database/
│   └── migrations/             # 16 migraciones
├── resources/
│   └── views/                  # 33 vistas Blade
│       ├── layouts/
│       ├── components/
│       ├── auth/
│       ├── docente/
│       └── estudiante/
└── routes/
    └── web.php                 # Rutas de la aplicación
```

## 🔐 Seguridad

- Autenticación con Laravel Breeze
- Middleware de roles (estudiante/docente)
- Protección CSRF en todos los formularios
- Validación de datos en controladores
- Soft deletes para preservar datos

## 🎨 Frontend

- **Tailwind CSS**: Estilos utilitarios
- **Alpine.js**: Reactividad en componentes
- **Blade**: Motor de plantillas
- **Font Awesome**: Iconos

## 📝 API Endpoints

### Estudiante
- `GET /estudiante/dashboard` - Dashboard
- `GET /estudiante/asignaturas` - Lista de asignaturas
- `GET /estudiante/asignaturas/{id}` - Detalle de asignatura
- `GET /estudiante/temas/{id}` - Detalle de tema
- `GET /estudiante/juegos/{id}/play` - Jugar juego
- `GET /estudiante/evaluaciones` - Lista de evaluaciones
- `GET /estudiante/evaluaciones/{id}/take` - Realizar evaluación
- `GET /estudiante/progreso` - Mi progreso
- `GET /estudiante/perfil` - Mi perfil

### Docente
- `GET /docente/dashboard` - Dashboard
- `GET /docente/temas` - Gestión de temas
- `GET /docente/juegos` - Gestión de juegos
- `GET /docente/evaluaciones` - Gestión de evaluaciones
- `GET /docente/calificaciones` - Calificaciones
- `GET /docente/rankings` - Rankings
- `GET /docente/estudiantes` - Gestión de estudiantes

## 🧪 Testing

```bash
# Ejecutar tests
php artisan test

# Tests con cobertura
php artisan test --coverage
```

## 📦 Dependencias Principales

```json
{
    "php": "^8.3",
    "laravel/framework": "^12.0",
    "laravel/breeze": "^2.0",
    "phpoffice/phpspreadsheet": "^2.0",
    "barryvdh/laravel-dompdf": "^3.0"
}
```

## 🚀 Despliegue

1. **Configurar servidor de producción**
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. **Configurar permisos**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

3. **Configurar cron para tareas programadas**
```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

## 🤝 Contribución

1. Fork el proyecto
2. Crea tu rama de características (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

Desarrollado con ❤️ para mejorar la educación.

## 📞 Soporte

Para soporte técnico o consultas, contactar a: soporte@educlass.com

---

**Educlass** - Aprender jugando, enseñar conectando.
