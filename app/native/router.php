<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

const ALUMNO_ESTADOS = ['ACTIVO', 'INACTIVO', 'EGRESADO', 'RETIRADO'];

function render_page(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/views/' . $view . '.php';
    exit;
}

function flash(string $key, ?string $value = null): ?string
{
    app_start_session();
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $message;
}

function query_all(string $sql, array $params = []): array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->fetchAll();
}

function query_one(string $sql, array $params = []): ?array
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    $row = $statement->fetch();
    return $row === false ? null : $row;
}

function execute_sql(string $sql, array $params = []): int
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement->rowCount();
}

function student_by_id(int $id): ?array
{
    return query_one(
        'SELECT a.*, c.nombre AS carrera_nombre FROM alumnos a LEFT JOIN carreras c ON c.carrera_id = a.carrera_id WHERE a.alumno_id = ? AND a.deleted_at IS NULL',
        [$id]
    );
}

function student_carnet(int $studentId): ?array
{
    return query_one(
        'SELECT ca.*, ca.estado AS carnet_estado, a.estado AS alumno_estado, cv.fecha_vigencia, a.*, c.nombre AS carrera_nombre
         FROM carnets ca
         JOIN alumnos a ON a.alumno_id = ca.alumno_id
         LEFT JOIN carreras c ON c.carrera_id = a.carrera_id
         LEFT JOIN carnet_vencimientos cv ON cv.carnet_id = ca.carnet_id
           AND cv.carnet_vencimiento_id = (SELECT MAX(v2.carnet_vencimiento_id) FROM carnet_vencimientos v2 WHERE v2.carnet_id = ca.carnet_id)
         WHERE ca.alumno_id = ? AND a.deleted_at IS NULL
         ORDER BY ca.fecha_emision DESC, ca.carnet_id DESC LIMIT 1',
        [$studentId]
    );
}

function carnet_by_token(string $token): ?array
{
    return query_one(
        'SELECT ca.*, ca.estado AS carnet_estado, a.estado AS alumno_estado, cv.fecha_vigencia, a.*, c.nombre AS carrera_nombre
         FROM carnets ca
         JOIN alumnos a ON a.alumno_id = ca.alumno_id
         LEFT JOIN carreras c ON c.carrera_id = a.carrera_id
         LEFT JOIN carnet_vencimientos cv ON cv.carnet_id = ca.carnet_id
           AND cv.carnet_vencimiento_id = (SELECT MAX(v2.carnet_vencimiento_id) FROM carnet_vencimientos v2 WHERE v2.carnet_id = ca.carnet_id)
         WHERE LOWER(ca.token_qr) = LOWER(?) AND a.deleted_at IS NULL LIMIT 1',
        [$token]
    );
}

function validate_student_data(array $input, ?int $studentId, bool $creating, bool $partial = false): array
{
    $data = [];
    foreach (['codigo_alumno', 'nombres', 'apellido_paterno', 'apellido_materno', 'carrera_id', 'estado', 'fecha_nacimiento', 'sexo', 'correo', 'periodo', 'semestre', 'fecha_vigencia'] as $key) {
        $data[$key] = trim((string) ($input[$key] ?? ''));
    }

    $errors = [];
    if ($creating || array_key_exists('codigo_alumno', $input)) {
        if ($data['codigo_alumno'] === '' || mb_strlen($data['codigo_alumno']) > 30) {
            $errors[] = 'El codigo de alumno es obligatorio y debe tener como maximo 30 caracteres.';
        }
    }
    if (($creating || array_key_exists('nombres', $input)) && ($data['nombres'] === '' || mb_strlen($data['nombres']) > 80)) {
        $errors[] = 'Los nombres son obligatorios y deben tener como maximo 80 caracteres.';
    }
    if (($creating || array_key_exists('nombres', $input)) && $data['nombres'] !== '' && !preg_match('/^[\p{L}\p{M}\s\'\.\-]+$/u', $data['nombres'])) {
        $errors[] = 'Los nombres solo pueden contener letras (incluidas tildes y la letra ene), espacios, apostrofes, puntos y guiones.';
    }
    if (($creating || array_key_exists('apellido_paterno', $input)) && ($data['apellido_paterno'] === '' || mb_strlen($data['apellido_paterno']) > 60)) {
        $errors[] = 'El apellido paterno es obligatorio y debe tener como maximo 60 caracteres.';
    }
    if (($creating || array_key_exists('apellido_materno', $input)) && $data['apellido_materno'] !== '' && !preg_match('/^[\p{L}\p{M}\s\'\.\-]+$/u', $data['apellido_materno'])) {
        $errors[] = 'El apellido materno solo puede contener letras (incluidas tildes y la letra ene), espacios, apostrofes, puntos y guiones.';
    }
    if (($creating || array_key_exists('apellido_paterno', $input)) && $data['apellido_paterno'] !== '' && !preg_match('/^[\p{L}\p{M}\s\'\.\-]+$/u', $data['apellido_paterno'])) {
        $errors[] = 'El apellido paterno solo puede contener letras (incluidas tildes y la letra ene), espacios, apostrofes, puntos y guiones.';
    }
    if ($data['correo'] !== '' && (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL) || $data['correo'] !== '' && mb_strlen($data['correo']) > 150)) {
        $errors[] = 'El correo no es valido.';
    }
    if (!$partial && ($creating || array_key_exists('carrera_id', $input))) {
        if (!ctype_digit($data['carrera_id']) || !query_one('SELECT carrera_id FROM carreras WHERE carrera_id = ? AND estado = 1', [(int) $data['carrera_id']])) {
            $errors[] = 'Selecciona una carrera valida.';
        }
    }
    if ($creating || array_key_exists('estado', $input)) {
        if (!in_array($data['estado'], ALUMNO_ESTADOS, true)) {
            $errors[] = 'El estado academico no es valido.';
        }
    }
    if (!$partial && ($creating || array_key_exists('fecha_nacimiento', $input))) {
        if ($data['fecha_nacimiento'] === '' || !valid_date($data['fecha_nacimiento'])) {
            $errors[] = 'La fecha de nacimiento no es valida.';
        }
    }
    if ($data['sexo'] !== '' && !in_array($data['sexo'], ['M', 'F', 'O'], true)) {
        $errors[] = 'Selecciona una opcion valida para sexo.';
    }
    if ($creating && ($data['periodo'] < 2000 || $data['periodo'] > 2100 || !in_array($data['semestre'], ['1', '2'], true) || !valid_date($data['fecha_vigencia']))) {
        $errors[] = 'Completa un periodo, semestre y fecha de vigencia validos.';
    }

    $uniqueFields = ['codigo_alumno' => 'codigo de alumno', 'correo' => 'correo'];
    foreach ($uniqueFields as $field => $label) {
        if (($partial) || $data[$field] === '') {
            continue;
        }
        $sql = "SELECT alumno_id FROM alumnos WHERE {$field} = ?";
        $params = [$data[$field]];
        if ($studentId !== null) {
            $sql .= ' AND alumno_id <> ?';
            $params[] = $studentId;
        }
        if (query_one($sql . ' LIMIT 1', $params)) {
            $errors[] = 'Ese ' . $label . ' ya esta registrado.';
        }
    }

    if ($errors) {
        throw new InvalidArgumentException(implode(' ', $errors));
    }

    return $data;
}

function valid_date(string $value): bool
{
    $date = DateTime::createFromFormat('!Y-m-d', $value);
    return $date !== false && $date->format('Y-m-d') === $value;
}

function save_photo(?array $file, string $code, ?string $oldPhoto = null, bool $required = false): ?string
{
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            throw new InvalidArgumentException('La foto del alumno es obligatoria.');
        }
        return $oldPhoto;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || ($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new InvalidArgumentException('La foto debe pesar como maximo 5 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
    if (!isset($extensions[$mime])) {
        throw new InvalidArgumentException('La foto debe ser JPG, PNG o GIF.');
    }

    $directory = APP_ROOT . '/public/uploads';
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('No se pudo crear la carpeta de fotos.');
    }
    $filename = preg_replace('/[^A-Za-z0-9]+/', '_', $code) . '_' . bin2hex(random_bytes(5)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('No se pudo guardar la foto.');
    }

    return $filename;
}

function delete_photo(?string $filename): void
{
    if (!$filename) {
        return;
    }

    $path = APP_ROOT . '/public/uploads/' . basename($filename);
    if (is_file($path)) {
        unlink($path);
    }
}

function qr_base_url(): string
{
    $configured = trim(app_setting('QR_BASE_URL'));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return 'http://' . $host . rtrim(app_url(), '/');
}

function output_qr(string $token): void
{
    require_once APP_ROOT . '/app/Lib/phpqrcode/phpqrcode.php';
    $url = qr_base_url() . '/verificar/' . rawurlencode($token);
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=86400');
    \QRcode::png($url, false, 'M', 6, 2);
    exit;
}

function authenticate(string $password): ?array
{
    $admin = query_one(
        "SELECT u.user_id, u.alumno_id, u.password, u.estado,
                r.nombre AS role,
                p.nombres,
                TRIM(CONCAT_WS(' ', p.apellido_paterno, p.apellido_materno)) AS apellidos,
                p.correo
         FROM usuarios u
         JOIN roles_usuario ru ON ru.user_id = u.user_id
         JOIN roles r ON r.rol_id = ru.rol_id
         LEFT JOIN usuario_personal up ON up.user_id = u.user_id
         LEFT JOIN personal p ON p.personal_id = up.personal_id
         WHERE u.alumno_id IS NULL AND u.estado = 'ACTIVO' AND r.nombre = 'ADMIN' AND r.estado = 1
         LIMIT 1"
    );
    if ($admin && password_verify($password, $admin['password'])) {
        return $admin;
    }

    return null;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'user_id' => (int) $user['user_id'],
        'alumno_id' => isset($user['alumno_id']) ? (int) $user['alumno_id'] : null,
        'nombres' => $user['nombres'] ?? '',
        'apellidos' => $user['apellidos'] ?? '',
        'correo' => $user['correo'] ?? '',
        'role' => $user['role'] ?? 'ALUMNO',
    ];
    execute_sql('UPDATE usuarios SET ultimo_login = NOW() WHERE user_id = ?', [(int) $user['user_id']]);
    audit('LOGIN', 'usuarios', (int) $user['user_id'], 'Inicio de sesion');
}

function end_session(): void
{
    $user = current_user();
    if ($user) {
        audit('LOGOUT', 'usuarios', (int) $user['user_id'], 'Cierre de sesion');
    }
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function not_found(): void
{
    http_response_code(404);
    render_page('error', ['code' => 404, 'message' => 'No se encontro la pagina solicitada.']);
}

function route_request(): void
{
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $scriptDirectory = rtrim(str_replace('\\', '/', dirname($scriptName)), '/.');
    $basePath = app_base_path();
    if ($path === $scriptName) {
        $path = ($basePath !== '' ? $basePath : $scriptDirectory) . '/';
    }
    foreach (array_unique(array_filter([$basePath, $scriptDirectory])) as $prefix) {
        if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
            $path = substr($path, strlen($prefix));
            break;
        }
    }
    $path = rawurldecode($path);
    $legacyPrefix = '/' . basename(APP_ROOT) . '/public';
    $legacyPosition = stripos($path, $legacyPrefix);
    if ($legacyPosition !== false) {
        $suffix = substr($path, $legacyPosition + strlen($legacyPrefix));
        if ($suffix === '' || str_starts_with($suffix, '/')) {
            $path = $suffix !== '' ? $suffix : '/';
        }
    }
    $path = '/' . trim($path, '/');
    if ($path === '//') {
        $path = '/';
    }

    if ($method === 'GET' && $path === '/up') {
        header('Content-Type: text/plain; charset=utf-8');
        echo health_check() ? 'OK' : 'Unavailable';
        exit;
    }

    if ($method === 'GET' && $path === '/') {
        redirect_to(current_user() ? 'dashboard' : 'login');
    }

    if ($path === '/login' && $method === 'GET') {
        if (current_user()) {
            redirect_to('dashboard');
        }
        render_page('login', ['error' => flash('error')]);
    }

    if ($path === '/login' && $method === 'POST') {
        verify_csrf();
        $user = authenticate(trim((string) ($_POST['password'] ?? '')));
        if (!$user) {
            flash('error', 'La contrasena no corresponde al administrador.');
            redirect_to('login');
        }
        login_user($user);
        redirect_to('dashboard');
    }

    if ($path === '/logout' && $method === 'POST') {
        verify_csrf();
        end_session();
        redirect_to('login');
    }

    if ($method === 'GET' && preg_match('#^/qr/([a-f0-9]{16,64})$#i', $path, $match)) {
        output_qr($match[1]);
    }

    if ($path === '/verificar' && $method === 'GET') {
        $input = trim((string) ($_GET['qr'] ?? ''));
        if ($input !== '') {
            if (preg_match('#/verificar/([a-f0-9]{16,64})#i', $input, $match)) {
                redirect_to('verificar/' . strtolower($match[1]));
            }
            if (preg_match('/^[a-f0-9]{16,64}$/i', $input)) {
                redirect_to('verificar/' . strtolower($input));
            }
            $error = 'Codigo no valido. Verifica el enlace o el codigo escaneado.';
        } else {
            $error = null;
        }
        render_page('verify-search', compact('error', 'input'));
    }

    if ($method === 'GET' && preg_match('#^/verificar/([a-f0-9]{16,64})$#i', $path, $match)) {
        $carnet = carnet_by_token($match[1]);
        if (!$carnet) {
            http_response_code(404);
            render_page('verify-not-found', ['token' => strtolower($match[1])]);
        }
        audit('VERIFICAR_CARNET', 'carnets', (int) $carnet['carnet_id'], 'Verificacion publica');
        render_page('verify-card', ['carnet' => $carnet, 'printOnly' => false]);
    }

    if ($method === 'GET' && preg_match('#^/verificar/([a-f0-9]{16,64})/pdf$#i', $path, $match)) {
        $carnet = carnet_by_token($match[1]);
        if (!$carnet) {
            http_response_code(404);
            render_page('verify-not-found', ['token' => strtolower($match[1])]);
        }
        audit('IMPRIMIR_CARNET', 'carnets', (int) $carnet['carnet_id'], 'Impresion / guardar como PDF');
        render_page('verify-card', ['carnet' => $carnet, 'printOnly' => true]);
    }

    if ($method === 'GET' && $path === '/dashboard') {
        $user = require_admin();

        $today = date('Y-m-d');
        $until = date('Y-m-d', strtotime('+30 days'));
        $states = [];
        foreach (ALUMNO_ESTADOS as $state) {
            $states[$state] = (int) db()->query("SELECT COUNT(*) FROM alumnos WHERE deleted_at IS NULL AND estado = " . db()->quote($state))->fetchColumn();
        }
        $metrics = [
            'students' => (int) db()->query('SELECT COUNT(*) FROM alumnos WHERE deleted_at IS NULL')->fetchColumn(),
            'cards' => (int) db()->query('SELECT COUNT(*) FROM carnets')->fetchColumn(),
            'expiring' => (int) query_one('SELECT COUNT(DISTINCT ca.alumno_id) AS total FROM carnets ca JOIN carnet_vencimientos cv ON cv.carnet_id = ca.carnet_id WHERE cv.fecha_vigencia BETWEEN ? AND ?', [$today, $until])['total'],
        ];
        $careers = query_all('SELECT c.nombre, COUNT(a.alumno_id) AS total FROM carreras c LEFT JOIN alumnos a ON a.carrera_id = c.carrera_id AND a.deleted_at IS NULL GROUP BY c.carrera_id, c.nombre ORDER BY total DESC');
        $recent = query_all('SELECT a.alumno_id, a.nombres, a.apellido_paterno, a.apellido_materno, a.fecha_registro, c.nombre AS carrera_nombre FROM alumnos a LEFT JOIN carreras c ON c.carrera_id = a.carrera_id WHERE a.deleted_at IS NULL ORDER BY a.fecha_registro DESC LIMIT 5');
        render_page('dashboard', compact('user', 'states', 'metrics', 'careers', 'recent'));
    }

    if ($method === 'GET' && $path === '/alumnos') {
        require_admin();
        $search = trim((string) ($_GET['search'] ?? ''));
        $careerId = trim((string) ($_GET['carrera'] ?? ''));
        $state = trim((string) ($_GET['estado'] ?? ''));
        $expiring = isset($_GET['proximos']);
        $conditions = ['a.deleted_at IS NULL'];
        $params = [];
        if ($search !== '') {
            $conditions[] = '(a.nombres LIKE ? OR a.apellido_paterno LIKE ? OR a.apellido_materno LIKE ? OR a.codigo_alumno LIKE ?)';
            array_push($params, ...array_fill(0, 4, '%' . $search . '%'));
        }
        if ($careerId !== '' && ctype_digit($careerId)) {
            $conditions[] = 'a.carrera_id = ?';
            $params[] = (int) $careerId;
        }
        if (in_array($state, ALUMNO_ESTADOS, true)) {
            $conditions[] = 'a.estado = ?';
            $params[] = $state;
        } else {
            $state = '';
        }
        if ($expiring) {
            $conditions[] = 'EXISTS (SELECT 1 FROM carnets cx JOIN carnet_vencimientos vx ON vx.carnet_id = cx.carnet_id WHERE cx.alumno_id = a.alumno_id AND vx.fecha_vigencia BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY))';
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 50;
        $where = implode(' AND ', $conditions);
        $total = (int) query_one("SELECT COUNT(*) AS total FROM alumnos a WHERE {$where}", $params)['total'];
        $students = query_all(
            "SELECT a.*, c.nombre AS carrera_nombre, ca.token_qr, ca.carnet_id FROM alumnos a LEFT JOIN carreras c ON c.carrera_id = a.carrera_id LEFT JOIN carnets ca ON ca.carnet_id = (SELECT MAX(c2.carnet_id) FROM carnets c2 WHERE c2.alumno_id = a.alumno_id) WHERE {$where} ORDER BY a.carrera_id, a.apellido_paterno, a.nombres LIMIT {$perPage} OFFSET " . (($page - 1) * $perPage),
            $params
        );
        $careers = query_all('SELECT carrera_id, nombre FROM carreras WHERE estado = 1 ORDER BY nombre');
        render_page('students', compact('students', 'careers', 'search', 'careerId', 'state', 'expiring', 'page', 'perPage', 'total'));
    }

    if ($method === 'GET' && $path === '/alumnos/crear') {
        require_admin();
        $careers = query_all('SELECT carrera_id, nombre FROM carreras WHERE estado = 1 ORDER BY nombre');
        $nextCode = (int) db()->query("SELECT COALESCE(MAX(CAST(codigo_alumno AS UNSIGNED)), 0) + 1 FROM alumnos WHERE codigo_alumno REGEXP '^[0-9]+$'")->fetchColumn();
        $currentPeriod = (int) date('Y');
        $currentSemester = (int) date('n') >= 8 ? 2 : 1;
        render_page('student-form', compact('careers', 'nextCode', 'currentPeriod', 'currentSemester'));
    }

    if ($method === 'POST' && $path === '/alumnos') {
        require_admin();
        verify_csrf();
        try {
            $data = validate_student_data($_POST, null, true);
            $photo = save_photo($_FILES['foto'] ?? null, $data['codigo_alumno'], required: true);
            db()->beginTransaction();
            execute_sql('INSERT INTO alumnos (carrera_id, codigo_alumno, nombres, apellido_paterno, apellido_materno, fecha_nacimiento, sexo, correo, foto, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                (int) $data['carrera_id'],
                $data['codigo_alumno'],
                $data['nombres'],
                $data['apellido_paterno'],
                $data['apellido_materno'],
                $data['fecha_nacimiento'],
                $data['sexo'] ?: null,
                $data['correo'] ?: null,
                $photo,
                $data['estado'],
            ]);
            $studentId = (int) db()->lastInsertId();
            execute_sql('INSERT INTO carnets (alumno_id, codigo, token_qr, periodo, semestre, estado) VALUES (?, ?, ?, ?, ?, ?)', [$studentId, $data['codigo_alumno'], bin2hex(random_bytes(20)), (int) $data['periodo'], (int) $data['semestre'], 'ACTIVO']);
            $cardId = (int) db()->lastInsertId();
            execute_sql('INSERT INTO carnet_vencimientos (carnet_id, fecha_vigencia, fecha_emision) VALUES (?, ?, CURDATE())', [$cardId, $data['fecha_vigencia']]);
            audit('CREAR_ALUMNO', 'alumnos', $studentId, 'Registro del alumno');
            audit('GENERAR_CARNET', 'carnets', $cardId, 'Carnet ' . $data['codigo_alumno'] . ' emitido');
            db()->commit();
            flash('status', 'Alumno registrado y carnet generado correctamente.');
            redirect_to('alumnos/' . $studentId . '/carnet');
        } catch (InvalidArgumentException $exception) {
            flash('error', $exception->getMessage());
            redirect_to('alumnos/crear');
        } catch (Throwable $exception) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            delete_photo($photo ?? null);
            throw $exception;
        }
    }

    if ($method === 'GET' && $path === '/reportes') {
        $user = require_admin();

        $stateFilter = strtoupper(trim((string) ($_GET['estado'] ?? '')));
        if (!in_array($stateFilter, ALUMNO_ESTADOS, true)) {
            $stateFilter = '';
        }

        $countByState = [];
        foreach (ALUMNO_ESTADOS as $state) {
            $countByState[$state] = (int) db()->query('SELECT COUNT(*) FROM alumnos WHERE deleted_at IS NULL AND estado = ' . db()->quote($state))->fetchColumn();
        }

        $perPage = 40;
        $studentsByState = [];
        $pagesByState = [];
        $pageByState = [];
        foreach (ALUMNO_ESTADOS as $state) {
            if ($stateFilter !== '' && $stateFilter !== $state) {
                continue;
            }
            $stateTotal = (int) ($countByState[$state] ?? 0);
            $pages = max(1, (int) ceil($stateTotal / $perPage));
            $page = min($pages, max(1, (int) ($_GET['p'][$state] ?? 1)));
            $offset = ($page - 1) * $perPage;
            $pageByState[$state] = $page;
            $pagesByState[$state] = $pages;
            $studentsByState[$state] = query_all(
                'SELECT a.alumno_id, a.nombres, a.apellido_paterno, a.apellido_materno, a.codigo_alumno, a.correo, a.estado, c.nombre AS carrera_nombre
                 FROM alumnos a LEFT JOIN carreras c ON c.carrera_id = a.carrera_id
                 WHERE a.deleted_at IS NULL AND a.estado = ?
                 ORDER BY a.apellido_paterno, a.apellido_materno, a.nombres
                 LIMIT ' . $perPage . ' OFFSET ' . $offset,
                [$state]
            );
        }

        $careerRows = query_all('SELECT c.carrera_id, c.nombre AS carrera, a.estado, COUNT(a.alumno_id) AS total FROM carreras c LEFT JOIN alumnos a ON a.carrera_id = c.carrera_id AND a.deleted_at IS NULL GROUP BY c.carrera_id, c.nombre, a.estado ORDER BY c.nombre');
        $careerMatrix = [];
        foreach ($careerRows as $row) {
            if ($row['estado'] === null) {
                continue;
            }
            $careerMatrix[$row['carrera']][$row['estado']] = (int) $row['total'];
        }

        render_page('reports', compact('countByState', 'studentsByState', 'pagesByState', 'pageByState', 'perPage', 'stateFilter', 'careerMatrix'));
    }

    if (preg_match('#^/alumnos/(\d+)(?:/(editar|carnet|renovar))?$#', $path, $match)) {
        $studentId = (int) $match[1];
        $action = $match[2] ?? '';
        $student = student_by_id($studentId);
        if (!$student) {
            not_found();
        }

        if ($method === 'GET' && $action === 'carnet') {
            $user = require_admin();
            $carnet = student_carnet($studentId);
            render_page('student-card', compact('user', 'student', 'carnet'));
        }

        if ($method === 'GET' && $action === 'editar') {
            require_admin();
            $user = current_user();
            $careers = query_all('SELECT carrera_id, nombre FROM carreras WHERE estado = 1 ORDER BY nombre');
            render_page('student-form', compact('user', 'student', 'careers'));
        }

        if (($method === 'POST' || $method === 'PUT') && $action === '' && ($_POST['_method'] ?? '') !== 'DELETE') {
            require_admin();
            $user = current_user();
            verify_csrf();
            try {
                $data = validate_student_data($_POST, $studentId, false);
                $photo = save_photo($_FILES['foto'] ?? null, $data['codigo_alumno'], $student['foto']);
                try {
                    execute_sql('UPDATE alumnos SET carrera_id = ?, codigo_alumno = ?, nombres = ?, apellido_paterno = ?, apellido_materno = ?, fecha_nacimiento = ?, sexo = ?, correo = ?, foto = ?, estado = ? WHERE alumno_id = ?', [
                        (int) $data['carrera_id'],
                        $data['codigo_alumno'],
                        $data['nombres'],
                        $data['apellido_paterno'],
                        $data['apellido_materno'],
                        $data['fecha_nacimiento'] ?: null,
                        $data['sexo'] ?: null,
                        $data['correo'] ?: null,
                        $photo,
                        $data['estado'],
                        $studentId,
                    ]);
                } catch (Throwable $exception) {
                    if ($photo !== $student['foto']) {
                        delete_photo($photo);
                    }
                    throw $exception;
                }
                if ($photo !== $student['foto']) {
                    delete_photo($student['foto']);
                }
                audit('EDITAR_ALUMNO', 'alumnos', $studentId, 'Alumno actualizado');
                flash('status', 'Alumno actualizado correctamente.');
                redirect_to('alumnos');
            } catch (InvalidArgumentException $exception) {
                flash('error', $exception->getMessage());
                redirect_to('alumnos/' . $studentId . '/editar');
            }
        }

        if ($method === 'POST' && $action === 'renovar') {
            require_admin();
            verify_csrf();
            $date = trim((string) ($_POST['fecha_vigencia'] ?? ''));
            if (!valid_date($date)) {
                flash('error', 'Indica una fecha de vigencia valida.');
                redirect_to('alumnos/' . $studentId . '/carnet');
            }
            $carnet = student_carnet($studentId);
            if (!$carnet) {
                flash('error', 'El alumno no tiene carnets registrados.');
                redirect_to('alumnos/' . $studentId . '/carnet');
            }
            $semester = (int) date('n') >= 8 ? 2 : 1;
            db()->beginTransaction();
            execute_sql('INSERT INTO carnet_vencimientos (carnet_id, fecha_vigencia, fecha_emision) VALUES (?, ?, CURDATE())', [(int) $carnet['carnet_id'], $date]);
            execute_sql('UPDATE carnets SET periodo = ?, semestre = ?, estado = ?, fecha_emision = NOW() WHERE carnet_id = ?', [(int) date('Y'), $semester, 'ACTIVO', (int) $carnet['carnet_id']]);
            audit('RENOVAR_CARNET', 'carnets', (int) $carnet['carnet_id'], 'Vigencia renovada hasta ' . $date);
            db()->commit();
            flash('status', 'Carnet renovado correctamente.');
            redirect_to('alumnos/' . $studentId . '/carnet');
        }

        if ($method === 'POST' && $action === '' && ($_POST['_method'] ?? '') === 'DELETE') {
            require_admin();
            verify_csrf();
            execute_sql('UPDATE alumnos SET deleted_at = NOW() WHERE alumno_id = ?', [$studentId]);
            audit('ELIMINAR_ALUMNO', 'alumnos', $studentId, 'Alumno eliminado');
            flash('status', 'Alumno eliminado correctamente.');
            redirect_to('alumnos');
        }
    }

    not_found();
}

try {
    route_request();
} catch (PDOException $exception) {
    http_response_code(503);
    $message = app_setting('APP_DEBUG') === 'true'
        ? 'Error de base de datos: ' . $exception->getMessage()
        : 'No se pudo conectar con MySQL. Comprueba que Apache y MySQL esten activos y revisa la configuracion de .env.';
    render_page('error', ['code' => 503, 'message' => $message]);
} catch (Throwable $exception) {
    http_response_code(500);
    $message = app_setting('APP_DEBUG') === 'true'
        ? $exception->getMessage()
        : 'Ocurrio un error inesperado.';
    render_page('error', ['code' => 500, 'message' => $message]);
}
