<?php
// Proyectos del usuario con sesión iniciada (solo lectura por ahora).
// Cada usuario ve únicamente sus proyectos: los que ha creado o los que
// tiene asignados como diseñador.
//
//   GET ProjectController.php?action=list

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No has iniciado sesión.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$accion = $_GET['action'] ?? 'list';

if ($accion === 'list') {
    $stmt = getConnection()->prepare(
        'SELECT p.id, p.nombre_logo, p.descripcion, p.sector, p.estilo, p.estado,
                p.fecha_creacion, c.nombre AS cliente
         FROM Proyecto p LEFT JOIN Cliente c ON c.id = p.id_cliente
         WHERE p.id_usuario = ? OR p.id_disenador = ?
         ORDER BY p.fecha_creacion DESC'
    );
    $stmt->execute([$_SESSION['usuario_id'], $_SESSION['usuario_id']]);
    echo json_encode(['ok' => true, 'proyectos' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Acción no válida.'], JSON_UNESCAPED_UNICODE);
