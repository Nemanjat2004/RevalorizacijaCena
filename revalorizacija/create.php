<?php

require_once '../db.class.7.php';

$db = DBstambena();

$create = $_POST['create'] ?? '';

if ($create === 'revalorizacija') {
    $iznos = $_POST['iznos'] ?? null;
    $datum = $_POST['datum'] ?? null;

    if (! $iznos || ! $datum) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid form data.']);
        exit;
    }

    $sql    = "INSERT INTO revalorizacija (koef, datum_rev) VALUES (?, ?)";
    $result = $db->executeNonSelect($sql, "ds", $iznos, $datum);

    if ($result['success']) {
        echo json_encode([
            'success'   => true,
            'insert_id' => $result['insert_id'],
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database error',
        ]);
    }
    exit;
} else {
    http_response_code(400);
    echo "Nepoznata akcija.";
}

$db->close();
