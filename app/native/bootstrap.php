<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__, 2));

function app_config(): array
{
    static $config;
    if (isset($config)) {
        return $config;
    }

    $config = [];
    $file = APP_ROOT . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($file)) {
        $file = APP_ROOT . DIRECTORY_SEPARATOR . '.env.example';
    }
    if (!is_file($file)) {
        return $config;
    }

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);
        if (strlen($value) >= 2 && (($value[0] === '"' && $value[-1] === '"') || ($value[0] === "'" && $value[-1] === "'"))) {
            $value = substr($value, 1, -1);
        }
        $config[trim($key)] = $value;
    }

    return $config;
}

function app_setting(string $key, string $default = ''): string
{
    $value = getenv($key);
    if ($value !== false) {
        return $value;
    }

    return app_config()[$key] ?? $default;
}

function database_config(): array
{
    return [
        'host' => app_setting('DB_HOST', '127.0.0.1'),
        'port' => app_setting('DB_PORT', '3306'),
        'database' => app_setting('DB_DATABASE', 'carnet_esfap_v2'),
        'username' => app_setting('DB_USERNAME', 'root'),
        'password' => app_setting('DB_PASSWORD'),
    ];
}

function database_has_table(PDO $connection, string $table): bool
{
    try {
        $statement = $connection->prepare(
            'SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? LIMIT 1'
        );
        $statement->execute([$table]);
        return $statement->fetchColumn() !== false;
    } catch (PDOException) {
        return false;
    }
}

function setup_database(array $config): void
{
    $file = APP_ROOT . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'carnet_esfap_v2.sql';
    if (!is_file($file)) {
        throw new RuntimeException('No se encontro database/carnet_esfap_v2.sql para crear la base de datos.');
    }

    $sql = (string) file_get_contents($file);
    $statements = [];
    foreach (preg_split('/\r\n|\r|\n/', $sql) as $line) {
        $trimmed = ltrim($line);
        if (preg_match('/^CREATE\s+DATABASE/i', $trimmed) || preg_match('/^USE\s+/i', $trimmed)) {
            continue;
        }
        $statements[] = $line;
    }

    $server = new PDO(
        "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $database = str_replace('`', '``', $config['database']);
    $server->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $server->exec("USE `{$database}`");
    $server->exec(implode("\n", $statements));
}

function db(): PDO
{
    static $connection;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $config = database_config();
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $autoSetup = app_setting('DB_AUTO_SETUP', 'true') === 'true';

    try {
        $connection = new PDO($dsn, $config['username'], $config['password'], $options);
    } catch (PDOException $exception) {
        if (!$autoSetup) {
            throw $exception;
        }
        setup_database($config);
        $connection = new PDO($dsn, $config['username'], $config['password'], $options);
    }

    if ($autoSetup && !database_has_table($connection, 'alumnos')) {
        setup_database($config);
        $connection = new PDO($dsn, $config['username'], $config['password'], $options);
    }

    return $connection;
}

function app_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name('carnet_esfap');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function student_full_name(array $student): string
{
    return trim(implode(' ', array_filter([
        trim((string) ($student['nombres'] ?? '')),
        trim((string) ($student['apellido_paterno'] ?? '')),
        trim((string) ($student['apellido_materno'] ?? '')),
    ], static fn(string $part): bool => $part !== '')));
}

function health_check(): bool
{
    return (int) db()->query('SELECT 1')->fetchColumn() === 1;
}

function app_base_path(): string
{
    $publicRoot = realpath(APP_ROOT . DIRECTORY_SEPARATOR . 'public');
    $documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));

    if ($publicRoot !== false && $documentRoot !== false) {
        $publicPath = str_replace('\\', '/', $publicRoot);
        $documentPath = rtrim(str_replace('\\', '/', $documentRoot), '/');

        if (strcasecmp($publicPath, $documentPath) === 0) {
            return '';
        }

        $documentPrefix = $documentPath . '/';
        if (strncasecmp($publicPath, $documentPrefix, strlen($documentPrefix)) === 0) {
            return '/' . ltrim(substr($publicPath, strlen($documentPrefix)), '/');
        }
    }

    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    return rtrim(dirname($script), '/.');
}

function app_url(string $path = ''): string
{
    $base = app_base_path();
    return $base . '/' . ltrim($path, '/');
}

function redirect_to(string $path): void
{
    header('Location: ' . app_url($path), true, 303);
    exit;
}

function csrf_token(): string
{
    app_start_session();
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    app_start_session();
    $submitted = (string) ($_POST['_csrf'] ?? '');
    if ($submitted === '' || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(419);
        exit('La sesion expiro. Recarga la pagina e intenta de nuevo.');
    }
}

function current_user(): ?array
{
    app_start_session();
    return $_SESSION['user'] ?? null;
}

function is_admin(?array $user = null): bool
{
    $user ??= current_user();
    return ($user['role'] ?? '') === 'ADMIN';
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect_to('login');
    }

    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if (!is_admin($user)) {
        http_response_code(403);
        exit('Acceso restringido.');
    }

    return $user;
}

function audit(string $action, string $table = '', ?int $recordId = null, string $description = ''): void
{
    try {
        $statement = db()->prepare('SELECT accion_id FROM auditoria_acciones WHERE nombre = ? LIMIT 1');
        $statement->execute([$action]);
        $actionId = $statement->fetchColumn();
        if (!$actionId) {
            return;
        }

        $user = current_user();
        $statement = db()->prepare(
            'INSERT INTO auditorias (user_id, accion_id, tabla, registro_id, ip_address, descripcion) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $statement->execute([
            $user['user_id'] ?? null,
            $actionId,
            $table,
            $recordId,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $description,
        ]);
    } catch (Throwable) {
    }
}

app_start_session();
