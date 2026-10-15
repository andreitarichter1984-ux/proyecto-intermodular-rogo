<?php
// Endpoint de autenticación: registro, inicio de sesión, cierre de sesión
// y consulta de la sesión activa. Responde siempre en JSON.
//
//   POST auth.php?action=register  (nombre, email, contrasena)
//   POST auth.php?action=login     (email, contrasena)
//   POST auth.php?action=logout
//   GET  auth.php?action=session

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/db.php';

function responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

// Comprueba la contraseña. Los usuarios de prueba del script SQL usan SHA2-256;
// los nuevos se guardan con password_hash(). Si un usuario entra con SHA2,
// su contraseña se actualiza a password_hash().
function verificarContrasena(PDO $pdo, array $usuario, string $contrasena): bool
{
    if (password_verify($contrasena, $usuario['contrasena'])) {
        return true;
    }
    if (hash_equals($usuario['contrasena'], hash('sha256', $contrasena))) {
        $stmt = $pdo->prepare('UPDATE Usuario SET contrasena = ? WHERE id = ?');
        $stmt->execute([password_hash($contrasena, PASSWORD_DEFAULT), $usuario['id']]);
        return true;
    }
    return false;
}

function iniciarSesion(array $usuario): void
{
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int) $usuario['id'];
    $_SESSION['nombre']     = $usuario['nombre'];
    $_SESSION['email']      = $usuario['email'];
    $_SESSION['rol']        = $usuario['rol'];
}

$accion = $_GET['action'] ?? '';
$metodo = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getConnection();
} catch (PDOException $e) {
    responder(500, ['ok' => false, 'error' => 'No se pudo conectar con la base de datos.']);
}

switch ($accion) {
    case 'register':
        if ($metodo !== 'POST') {
            responder(405, ['ok' => false, 'error' => 'Método no permitido.']);
        }
        $nombre     = trim($_POST['nombre'] ?? '');
        $email      = strtolower(trim($_POST['email'] ?? ''));
        $contrasena = $_POST['contrasena'] ?? '';

        if ($nombre === '' || $email === '' || $contrasena === '') {
            responder(400, ['ok' => false, 'error' => 'Todos los campos son obligatorios.']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            responder(400, ['ok' => false, 'error' => 'El correo electrónico no es válido.']);
        }
        if (strlen($contrasena) < 8) {
            responder(400, ['ok' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres.']);
        }

        $stmt = $pdo->prepare('SELECT id FROM Usuario WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            responder(409, ['ok' => false, 'error' => 'Ya existe una cuenta con ese correo.']);
        }

        $stmt = $pdo->prepare('INSERT INTO Usuario (nombre, email, contrasena) VALUES (?, ?, ?)');
        $stmt->execute([$nombre, $email, password_hash($contrasena, PASSWORD_DEFAULT)]);

        $stmt = $pdo->prepare('SELECT id, nombre, email, rol FROM Usuario WHERE id = ?');
        $stmt->execute([$pdo->lastInsertId()]);
        iniciarSesion($stmt->fetch());
        responder(201, ['ok' => true, 'redirect' => 'dashboard.html']);

    case 'login':
        if ($metodo !== 'POST') {
            responder(405, ['ok' => false, 'error' => 'Método no permitido.']);
        }
        $email      = strtolower(trim($_POST['email'] ?? ''));
        $contrasena = $_POST['contrasena'] ?? '';

        if ($email === '' || $contrasena === '') {
            responder(400, ['ok' => false, 'error' => 'Introduce tu correo y tu contraseña.']);
        }

        $stmt = $pdo->prepare('SELECT id, nombre, email, contrasena, rol, activo FROM Usuario WHERE email = ?');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if (!$usuario || !verificarContrasena($pdo, $usuario, $contrasena)) {
            responder(401, ['ok' => false, 'error' => 'Correo o contraseña incorrectos.']);
        }
        if (!$usuario['activo']) {
            responder(403, ['ok' => false, 'error' => 'Esta cuenta está desactivada.']);
        }

        iniciarSesion($usuario);
        responder(200, ['ok' => true, 'redirect' => 'dashboard.html']);

    case 'logout':
        $_SESSION = [];
        session_destroy();
        responder(200, ['ok' => true, 'redirect' => 'index.html']);

    case 'session':
        if (empty($_SESSION['usuario_id'])) {
            responder(401, ['ok' => false, 'error' => 'No has iniciado sesión.']);
        }
        $stmt = $pdo->prepare(
            'SELECT u.id, u.nombre, u.email, u.rol, p.nombre AS plan
             FROM Usuario u LEFT JOIN Plan p ON p.id = u.id_plan
             WHERE u.id = ?'
        );
        $stmt->execute([$_SESSION['usuario_id']]);
        $usuario = $stmt->fetch();
        if (!$usuario) {
            responder(401, ['ok' => false, 'error' => 'No has iniciado sesión.']);
        }
        responder(200, ['ok' => true, 'usuario' => $usuario]);

    default:
        responder(400, ['ok' => false, 'error' => 'Acción no válida.']);
}
