<?php

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

// Comprobar si el usuario ha iniciado sesion
if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);

    echo json_encode([
        'ok' => false,
        'error' => 'No has iniciado sesion.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

$pdo = getConnection();
$usuarioId = (int) $_SESSION['usuario_id'];

$accion = $_GET['action'] ?? 'list';

//Lista clientes del diseñador
if ($accion === 'list') {

    // Comprobar que el usuario es diseñador
    $stmt = $pdo->prepare(
        'SELECT rol FROM Usuario WHERE id = ?'
    );

    $stmt->execute([$usuarioId]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || $usuario['rol'] !== 'disenador') {

        http_response_code(403);

        echo json_encode([
            'ok' => false,
            'error' => 'No tienes permisos para consultar esta lista.'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // Obtener los clientes relacionados con los proyectos del disenador
    $stmt = $pdo->prepare(
        'SELECT DISTINCT
            c.id,
            c.nombre,
            c.email,
            c.telefono,
            c.empresa
         FROM Cliente c
         INNER JOIN Proyecto p
            ON p.id_cliente = c.id
         WHERE p.id_disenador = ?
         ORDER BY c.nombre ASC'
    );

    $stmt->execute([$usuarioId]);

    // Recuperar todos los clientes
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver respuesta JSON
    echo json_encode([
        'ok' => true,
        'total' => count($clientes),
        'clientes' => $clientes
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// Accion no valida
http_response_code(400);

echo json_encode([
    'ok' => false,
    'error' => 'Accion no valida.'
], JSON_UNESCAPED_UNICODE);

exit;