<?php
require 'db.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT * FROM studenti ORDER BY id DESC");
    $studenti = $stmt->fetchAll();
    echo json_encode($studenti);
} 

elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['nume']) || !isset($input['an']) || !isset($input['media'])) {
        echo json_encode(['success' => false, 'message' => 'Date incomplete']);
        exit;
    }

    $nume = $input['nume'];
    $an = $input['an'];
    $media = $input['media'];

    $sql = "INSERT INTO studenti (nume, an, media) VALUES (:nume, :an, :media)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute(['nume' => $nume, 'an' => $an, 'media' => $media]);
        echo json_encode(['success' => true, 'message' => 'Student adăugat!']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Eroare SQL']);
    }
}
?>