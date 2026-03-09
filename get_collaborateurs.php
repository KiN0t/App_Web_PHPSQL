<?php
// get_collaborateurs.php - Endpoint AJAX
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit;
}

require_once 'db.php';

$projet_id = (int)($_GET['projet_id'] ?? 0);

if ($projet_id <= 0) {
    echo json_encode([]);
    exit;
}

$pdo  = getDB();
$stmt = $pdo->prepare('
    SELECT u.id, u.prenom, u.nom, u.email
    FROM users u
    JOIN projet_collaborateurs pc ON u.id = pc.user_id
    WHERE pc.projet_id = :pid AND u.role = "collaborateur"
    ORDER BY u.nom, u.prenom
');
$stmt->execute([':pid' => $projet_id]);

header('Content-Type: application/json');
echo json_encode($stmt->fetchAll());