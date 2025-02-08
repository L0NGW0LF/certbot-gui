
<?php
header('Content-Type: application/json');

try {
    $result = file_put_contents('/tmp/certbot_input', '1');
    if ($result === false) {
        throw new Exception("Failed to write to input file");
    }
    echo json_encode([
        'status' => 'success',
        'message' => 'Enter key signal sent'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
