# Sistema de Carnets ESFAP

Sistema de identificación institucional para la Escuela Superior de Formación Artística Pública. Emite carnets digitales con código QR verificable por cualquier dispositivo, sin necesidad de instalar una aplicación.

## Requisitos

| Componente | Versión |
|---|---|
| PHP | 8.2 o superior |
| Laravel | 12 |
| MySQL | 8 o superior (vía XAMPP) |
| Composer | 2.x |

## Instalación

### 1. Clonar el repositorio

```bash
git clone https://github.com/Johanpro17/Carnet_Esfap.git
cd Carnet_Esfap
```

### 2. Dependencias

```bash
composer install
npm install
npm run build
```

### 3. Configurar Apache

El `DocumentRoot` debe apuntar a la carpeta `public`, nunca a la raíz del proyecto:

```apache
DocumentRoot "C:/xampp/htdocs/Johan/carnet_Esfap/public"
```

En `httpd.conf` hay que habilitar `mod_rewrite` y `mod_ssl` si se va a usar HTTPS:

```apache
LoadModule rewrite_module modules/mod_rewrite.so
LoadModule ssl_module modules/mod_ssl.so
```

### 4. Variables de entorno

```bash
cp .env.example .env
php artisan key:generate
```

Ajustar en `.env` los datos de conexión y las URLs:

```ini
DB_DATABASE=carnet_esfap_v2
DB_USERNAME=root
DB_PASSWORD=

APP_URL=https://localhost
QR_BASE_URL=https://192.168.1.100
```

`QR_BASE_URL` es la dirección que se codifica dentro de los códigos QR. Si se deja vacía, el sistema detecta la IP de la red local automáticamente, pero conviene fijarla para que los códigos funcionen desde los celulares.

### 5. Base de datos

```bash
mysql -u root < database/carnet_esfap_v2.sql
```

### 6. Permisos de escritura

`storage/` y `bootstrap/cache/` deben ser escribibles por Apache.

### 7. Iniciar

Desde el panel de XAMPP: **Start** en Apache y MySQL. Luego abrir `https://localhost`.

## Acceso

El sistema **reconoce el rol automáticamente**: solo se ingresa la contraseña.

- **Administradores** → la contraseña del usuario `admin` (hash bcrypt, ver `database/carnet_esfap_v2.sql`).
- **Alumnos** → su correo institucional como contraseña.

## Verificación por QR

El QR codifica una URL con el formato `https://<host>/verificar/<token>/pdf`. Al escanearse muestra el carnet con los datos del alumno, la vigencia y el estado, y permite descargar el PDF.

La búsqueda manual y la lectura de una imagen del QR están en `/verificar` (usa `jsQR` del lado del cliente).

## HTTPS en local

El certificado autofirmado cubre `localhost`, `127.0.0.1` y la IP de la red local. Está en `C:\xampp\apache\conf\ssl.crt\server.crt`. También hay que agregarlo al almacén de certificados del equipo y, para que los celulares no muestren avisos, al del teléfono.

## Estructura

```
app/
  Http/Controllers/    AlumnoController, AuthController, CarnetController,
                       DashboardController, QrController
  Http/Middleware/     SinCache, VerificarAdmin, VerificarPropio
  Lib/phpqrcode/       Generación de QR en PNG
  Models/              Alumno, Carnet, Carrera, Personal, Usuario, Rol, Auditoria
  Services/            AuditoriaService
resources/views/
  alumnos/             index, form, carnet
  auth/                login
  carnets/             card (plantilla imprimible), verificar
  layouts/             app (panel con sesión), publica (verificación)
  partials/            historial (bloqueo del botón atrás)
routes/web.php         Todas las rutas
database/              Esquema y datos iniciales
```

## Base de datos

| Tabla | Contenido |
|---|---|
| `alumnos` | Datos personales y estado (`ACTIVO`, `INACTIVO`, `EGRESADO`, `RETIRADO`, `SUSPENDIDO`) |
| `carnets` | Emisión, periodo, semestre, vigencia y `token_qr` |
| `carnet_vencimientos` | Historial de vencimientos |
| `carreras` | Carreras disponibles |
| `personal` | Docentes y administrativos |
| `usuarios` | Cuentas de acceso |
| `roles` / `roles_usuario` | `ADMIN` y `ALUMNO` |
| `usuario_personal` | Vínculo usuario ↔ personal |
| `auditorias` / `auditoria_acciones` | Registro de operaciones |

Los alumnos usan `deleted_at` (soft delete), por eso los borrados no eliminan filas de forma definitiva.

## Rutas

| Ruta | Acceso | Descripción |
|---|---|---|
| `/` | público | Portada |
| `/login` | público | Ingreso |
| `/verificar` | público | Buscador y lector de QR |
| `/verificar/{token}` | público | Datos del carnet |
| `/verificar/{token}/pdf` | público | Carnet en PDF |
| `/dashboard` | con sesión | Panel |
| `/alumnos` | admin | Listado y filtros |
| `/alumnos/crear` | admin | Emisión de carnet |
| `/alumnos/{id}/editar` | dueño o admin | Edición de datos |
| `/alumnos/{id}/carnet` | dueño | Carnet del alumno |

## Notas de seguridad

- `APP_DEBUG=false` en producción: los errores no se muestran al usuario.
- El middleware `SinCache` envía `no-store, no-cache` para que el navegador no repinte contenido de sesiones anteriores.
- Las vistas incluyen un bloqueo del botón atrás del navegador (`resources/views/partials/historial.blade.php`). Es una medida de usabilidad, **no una barrera real**: la seguridad depende de la sesión en el servidor, que rechaza cualquier POST sin cookie válida.
- `public/uploads/` está en `.gitignore`: las fotos de alumnos no se versionan.

## Licencia

Uso interno de la ESFAP.