<?php
header('Content-Type: application/json');

try {
    $output = shell_exec('xdg-open ~ 2>&1');
    echo json_encode(['success' => true, 'message' => 'Folder opened successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}