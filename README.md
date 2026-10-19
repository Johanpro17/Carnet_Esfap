# Carnets ESFAP

Sistema de carnets estudiantiles con verificación por código QR, desarrollado en **PHP nativo** (sin frameworks ni Composer) sobre **MySQL**. Funciona en Windows (XAMPP) y en Linux con Apache o Nginx.

## Requisitos

| Componente | Versión / Detalle |
|---|---|
| PHP | **8.0 o superior** |
| Extensiones PHP | `pdo_mysql`, `fileinfo`, **`gd`** |
| MySQL / MariaDB | 5.7+ / 10.2+ |
| Servidor web | Apache con `mod_rewrite` **y `AllowOverride All`**, o Nginx |
| Navegador | Moderno (cualquiera) |

> `gd` es obligatorio para generar las imágenes PNG de los códigos QR. `fileinfo` se usa al validar las fotos subidas.

## Estructura del proyecto

```
carnet_Esfap/
├── app/
│   ├── Lib/phpqrcode/        Generador de QR (incluido)
│   └── native/               Núcleo de la aplicación
│       ├── bootstrap.php     Configuración, sesión, BD, helpers
│       ├── router.php        Definición de rutas y controladores
│       └── views/            Plantillas PHP
├── database/carnet_esfap_v2.sql   Esquema + datos iniciales
├── public/                   ← DocumentRoot del sitio
│   ├── index.php             Punto de entrada
│   ├── .htaccess             Reescritura de URLs (Apache)
│   ├── css/, imagenes/, vendor/, uploads/
├── tests/run.php             Comprobaciones
├── .env.example              Plantilla de configuración
└── index.php                 Entrada alternativa (si el DocumentRoot es la raíz del proyecto)
```

## Arranque rápido en XAMPP (Windows)

1. Copia la carpeta del proyecto dentro de `htdocs`, por ejemplo `C:\xampp\htdocs\carnet_Esfap`.
2. Inicia **Apache** y **MySQL** desde el panel de XAMPP.
3. Abre en el navegador: `http://localhost/carnet_Esfap/public/`

No hace falta importar la base de datos a mano: al primer arranque el sistema **crea la base `carnet_esfap_v2` e importa `database/carnet_esfap_v2.sql` automáticamente** si detecta que no existe (opción `DB_AUTO_SETUP`, activada por defecto). Usa el usuario de MySQL habitual de XAMPP (`root` sin contraseña).

## Acceso

| Rol | Usuario | Contraseña |
|---|---|---|
| Administrador | `admin` | `123esf@p` |
| Alumno | su correo institucional | — |

> Los alumnos escriben su **correo** en el campo de contraseña del formulario de ingreso (no usan contraseña propia).

## Instalación manual (Linux / servidor)

1. Copia el proyecto al servidor (por ejemplo `/var/www/carnet_esfap`).
2. (Opcional) Importa `database/carnet_esfap_v2.sql`. Si lo omites, `DB_AUTO_SETUP` lo hará solo.
3. Copia `.env.example` como `.env` y configura:
   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=carnet_esfap_v2
   DB_USERNAME=tu_usuario
   DB_PASSWORD=tu_clave
   DB_AUTO_SETUP=true
   APP_DEBUG=false
   QR_BASE_URL=https://carnets.tu-dominio.edu.pe
   ```
4. Apunta el **DocumentRoot al directorio `public/`** (ver configuración abajo).
5. Da permisos de escritura a `public/uploads` (para las fotos de los alumnos).
6. Abre el sitio en el navegador.

## Configuración de Apache

`.htaccess` y las URLs limpias requieren `mod_rewrite` y `AllowOverride All`.
Ejemplo de VirtualHost (Windows o Linux):

```apache
<VirtualHost *:80>
    ServerName carnets.local
    DocumentRoot "C:/xampp/htdocs/carnet_Esfap/public"

    <Directory "C:/xampp/htdocs/carnet_Esfap/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

En Linux, además habilita los módulos y recarga Apache:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## Configuración de Nginx

Nginx no lee `.htaccess`; usa esta configuración equivalente (archivo `deploy/nginx.conf.example`):

```nginx
server {
    listen 80;
    server_name carnets.tu-dominio.edu.pe;
    root /var/www/carnet_Esfap/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include /etc/nginx/fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~* \.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?)$ {
        expires 30d;
        access_log off;
    }
}
```

## Prueba rápida con el servidor embebido de PHP

Sin configurar Apache/Nginx:

```bash
php -S localhost:8000 -t public
```

Luego abre `http://localhost:8000`.

## Comprobación

Con MySQL activo:

```bash
php tests/run.php          # Suite de pruebas (5/5)
curl http://localhost/up   # Devuelve "OK" si la BD responde
```

## Códigos QR y PDF

- `QR_BASE_URL` define el host que se graba en los QR. Si queda vacío se detecta automáticamente.
- La lectura de imágenes QR usa `public/vendor/jsqr`. La generación usa `app/Lib/phpqrcode` (requiere GD).
- La ruta `/verificar/{token}/pdf` abre una versión imprimible; se imprime o se guarda como PDF desde el navegador, sin paquetes externos.

## Rutas principales

| Ruta | Acceso | Función |
|---|---|---|
| `/` | Público | Inicio |
| `/login` | Público | Ingreso |
| `/verificar` | Público | Búsqueda y lectura QR |
| `/verificar/{token}` | Público | Validación del carnet |
| `/verificar/{token}/pdf` | Público | Vista para imprimir o guardar PDF |
| `/dashboard` | Sesión | Panel administrativo |
| `/alumnos` | Administrador | Búsqueda y gestión de alumnos |
| `/alumnos/crear` | Administrador | Registro y emisión de carnet |
| `/alumnos/{id}/editar` | Titular o administrador | Edición autorizada |
| `/alumnos/{id}/carnet` | Titular o administrador | Ver carnet |
| `/reportes` | Administrador | Reportes por estado académico |
| `/up` | Público | Health check (texto plano) |

La base de datos conserva eliminaciones lógicas mediante `deleted_at`.

## Despliegue (checklist)

- [ ] DocumentRoot apuntando a `public/` (o usar la config de Apache/Nginx de ejemplo).
- [ ] `.htaccess`/`try_files` activos (URLs limpias funcionan: `/login`, `/reportes`, ...).
- [ ] `.env` creado y con credenciales correctas; `APP_DEBUG=false` en producción.
- [ ] `QR_BASE_URL` con el dominio real que escanearán los teléfonos.
- [ ] Extensiones PHP `pdo_mysql`, `fileinfo`, `gd` habilitadas.
- [ ] `public/uploads` con permisos de escritura (`chmod 775`, usuario del servidor web).
- [ ] Base de datos creada: automática con `DB_AUTO_SETUP=true`, o importada a mano desde `database/carnet_esfap_v2.sql`.
- [ ] HTTPS recomendado (y `secure` en cookies se activa automáticamente).

## Seguridad

- **No subas `.env` al repositorio**: contiene credenciales. Ya está excluido en `.gitignore`.
- Las contraseñas de usuario se almacenan con `password_hash()` y se validan con `password_verify()`.
- Todas las peticiones POST usan token CSRF.
- Las consultas usan sentencias preparadas (PDO).
- Las fotos de los alumnos se guardan en `public/uploads` y no se versionan.
