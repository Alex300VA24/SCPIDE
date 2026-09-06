# SCPIDE Demo

Demostración navegable del Sistema de Consultas PIDE. Permite explorar la
interfaz, autenticación local, CUI, usuarios, roles, módulos, consultas y
reportes PDF usando exclusivamente información ficticia.

> Esta distribución no contiene endpoints, clientes HTTP, credenciales ni
> implementaciones para conectarse a servicios del Estado. Los resultados se
> generan localmente en `app/Services/PideDemo` y no son válidos para trámites.

## Funciones disponibles

- Inicio y cierre de sesión con usuarios locales.
- Validación CUI demostrativa.
- Creación y edición de usuarios, roles y módulos.
- Consultas ficticias RENIEC, SUNAT, SUNARP, MTC, CONADIS y ambientales.
- Generación de PDFs demostrativos.
- Auditoría local y cambio de contraseña local.

## Ejecución local

Requisitos: PHP 8.1+, Composer, Node.js 20+ y MySQL/MariaDB.

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

La base configurada en `.env.example` se llama `scpide_demo`. Créala antes de
ejecutar las migraciones.

## Despliegue en Render

El repositorio incluye `Dockerfile`, `.dockerignore`, `render.yaml` y el script
de arranque necesario.

1. Publica este directorio en un repositorio de GitHub o GitLab.
2. En Render selecciona **New > Blueprint** y conecta el repositorio.
3. Confirma la creación del servicio web y PostgreSQL definidos en
   `render.yaml`.
4. Genera `APP_KEY` localmente con `php artisan key:generate --show` y copia
   el resultado completo en la variable solicitada por Render.
5. En `APP_URL`, usa la URL pública completa asignada al servicio, por ejemplo
   `https://scpide-demo.onrender.com`.
6. Inicia el despliegue.

En cada arranque se ejecutan las migraciones. El seeder solo carga los datos
iniciales cuando la tabla de usuarios está vacía, por lo que los usuarios
creados desde la demo se conservan en PostgreSQL.

## Variables principales de Render

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-servicio.onrender.com
DB_CONNECTION=pgsql
DATABASE_URL=<administrada por Render>
SESSION_SECURE_COOKIE=true
```

No existen variables `PIDE_*` en esta distribución.

## Verificación

```bash
php artisan test
npm run build
composer validate --no-check-publish
```

## Datos de acceso

El acceso inicial aparece en el propio formulario:

```text
Usuario: admin
Contraseña: DemoSCPIDE2026!
CUI: 0
```

Puedes cambiar la contraseña inicial mediante `DEMO_ADMIN_PASSWORD` antes del
primer despliegue de la base de datos.
