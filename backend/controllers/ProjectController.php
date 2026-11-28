<?php
// Proyectos del usuario con sesion iniciada (solo lectura por ahora).
// Cada usuario ve unicamente sus proyectos: los que ha creado o los que
// tiene asignados como disenador.
//
//   GET ProjectController.php?action=list

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

//Comprobamos sesion
if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No has iniciado sesión.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$pdo= getConnection();
$usuarioID=(int) $_SESSION['usuario_id'];

$accion = $_GET['action'] ?? 'list';

//datos json(lectura)
$datos=json_decode(file_get_contents('php://input'), true)??[];

//listado de proyectos
if($accion=='list'){
    $stmt=$pdo->prepare('SELECT p.id, p.nombre_logo, p.descripcion, p.sector, p.estilo, p.estado,
                p.fecha_creacion, c.nombre AS cliente
         FROM Proyecto p LEFT JOIN Cliente c ON c.id = p.id_cliente
         WHERE p.id_usuario = ? OR p.id_disenador = ?
         ORDER BY p.fecha_creacion DESC'
    );
    $stmt->execute([$usuarioID, $usuarioID]);
    echo json_encode(['ok' => true, 'proyectos' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
    exit;
}

//Creacion proyecto y cliente
if ($accion === 'create') {

    $cliente = $datos['cliente'] ?? [];

    $nombreProyecto = trim($datos['nombre_logo'] ?? '');
    $nombreCliente = trim($cliente['nombre'] ?? '');
    $email = trim($cliente['email'] ?? '');
    $telefono = trim($cliente['telefono'] ?? '');
    $empresa = trim($cliente['empresa'] ?? '');

    // Validar campos
    if ($nombreProyecto === '' || $nombreCliente === '' || $email === '') {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'El nombre del proyecto, nombre del cliente y email son obligatorios.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'El email del cliente no es válido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {

        // Rol del usuario
        $stmt = $pdo->prepare(
            'SELECT rol FROM Usuario WHERE id = ? AND activo = 1'
        );
        $stmt->execute([$usuarioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            http_response_code(401);
            echo json_encode([
                'ok' => false,
                'error' => 'El usuario no existe o está inactivo.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $idDisenador = ($usuario['rol'] === 'diseñador')
            ? $usuarioId
            : null;

        $pdo->beginTransaction();

        // Crear cliente
        $stmt = $pdo->prepare(
            'INSERT INTO Cliente (nombre, email, telefono, empresa)
             VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $nombreCliente,
            $email,
            $telefono,
            $empresa
        ]);

        $idCliente = $pdo->lastInsertId();

        // Crear proyecto relacionado con el cliente
        $stmt = $pdo->prepare(
            'INSERT INTO Proyecto
             (id_usuario, id_disenador, id_cliente,
              nombre_logo, descripcion, colores,
              sector, estilo, referencias, estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $usuarioId,
            $idDisenador,
            $idCliente,
            $nombreProyecto,
            $datos['descripcion'] ?? null,
            $datos['colores'] ?? null,
            $datos['sector'] ?? null,
            $datos['estilo'] ?? null,
            $datos['referencias'] ?? null,
            'pendiente'
        ]);

        $idProyecto = $pdo->lastInsertId();

        $pdo->commit();

        http_response_code(201);
        echo json_encode([
            'ok' => true,
            'mensaje' => 'Proyecto y cliente creados correctamente.',
            'id_proyecto' => $idProyecto,
            'id_cliente' => $idCliente
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'Error al crear el proyecto y el cliente.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}


//Detalle del proyecto en curso
if ($accion === 'detail') {

    $idProyecto = filter_var(
        $_GET['id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$idProyecto || $idProyecto < 1) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Identificador de proyecto no válido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT
            p.*,
            c.nombre AS cliente_nombre,
            c.email AS cliente_email,
            c.telefono AS cliente_telefono,
            c.empresa AS cliente_empresa
         FROM Proyecto p
         LEFT JOIN Cliente c ON c.id = p.id_cliente
         WHERE p.id = ?
         AND (p.id_usuario = ? OR p.id_disenador = ?)'
    );

    $stmt->execute([
        $idProyecto,
        $usuarioId,
        $usuarioId
    ]);

    $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proyecto) {
        http_response_code(404);
        echo json_encode([
            'ok' => false,
            'error' => 'Proyecto no encontrado.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'proyecto' => $proyecto
    ], JSON_UNESCAPED_UNICODE);
    exit;
}


// edicion proyecto y usuario
if ($accion === 'update') {

    $idProyecto = filter_var(
        $_GET['id'] ?? null,
        FILTER_VALIDATE_INT
    );

    if (!$idProyecto || $idProyecto < 1) {
        http_response_code(400);
        echo json_encode([
            'ok' => false,
            'error' => 'Identificador de proyecto no válido.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    try {

        $pdo->beginTransaction();

        // Comprobacion de que el proyecto pertenece al cliente
        $stmt = $pdo->prepare(
            'SELECT p.*, c.nombre AS cliente_nombre,
                    c.email AS cliente_email,
                    c.telefono AS cliente_telefono,
                    c.empresa AS cliente_empresa
             FROM Proyecto p
             LEFT JOIN Cliente c ON c.id = p.id_cliente
             WHERE p.id = ?
             AND (p.id_usuario = ? OR p.id_disenador = ?)'
        );

        $stmt->execute([
            $idProyecto,
            $usuarioId,
            $usuarioId
        ]);

        $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$proyecto) {
            $pdo->rollBack();

            http_response_code(404);
            echo json_encode([
                'ok' => false,
                'error' => 'Proyecto no encontrado.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $cliente = $datos['cliente'] ?? [];

        // En caso de no realizar cambios, se mantienen valores
        $nombreProyecto = trim(
            $datos['nombre_logo'] ?? $proyecto['nombre_logo']
        );

        $nombreCliente = trim(
            $cliente['nombre'] ?? $proyecto['cliente_nombre']
        );

        $email = trim(
            $cliente['email'] ?? $proyecto['cliente_email']
        );

        if ($nombreProyecto === '' || $nombreCliente === '') {
            $pdo->rollBack();

            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => 'Los nombres del proyecto y cliente son obligatorios.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $pdo->rollBack();

            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'error' => 'El email del cliente no es válido.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Actualizar cliente
        $stmt = $pdo->prepare(
            'UPDATE Cliente
             SET nombre = ?, email = ?, telefono = ?, empresa = ?
             WHERE id = ?'
        );

        $stmt->execute([
            $nombreCliente,
            $email,
            $cliente['telefono'] ?? $proyecto['cliente_telefono'],
            $cliente['empresa'] ?? $proyecto['cliente_empresa'],
            $proyecto['id_cliente']
        ]);

        // Actualizar proyecto
        $stmt = $pdo->prepare(
            'UPDATE Proyecto
             SET nombre_logo = ?,
                 descripcion = ?,
                 colores = ?,
                 sector = ?,
                 estilo = ?,
                 referencias = ?
             WHERE id = ?'
        );

        $stmt->execute([
            $nombreProyecto,
            $datos['descripcion'] ?? $proyecto['descripcion'],
            $datos['colores'] ?? $proyecto['colores'],
            $datos['sector'] ?? $proyecto['sector'],
            $datos['estilo'] ?? $proyecto['estilo'],
            $datos['referencias'] ?? $proyecto['referencias'],
            $idProyecto
        ]);

        $pdo->commit();

        echo json_encode([
            'ok' => true,
            'mensaje' => 'Proyecto y cliente actualizados correctamente.'
        ], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        http_response_code(500);
        echo json_encode([
            'ok' => false,
            'error' => 'Error al actualizar el proyecto y el cliente.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

//accion no valida
http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Accion no valida.'], JSON_UNESCAPED_UNICODE);
