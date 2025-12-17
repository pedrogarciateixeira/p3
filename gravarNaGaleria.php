<?php
header('Content-Type: application/json');

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Recebe os dados JSON do corpo da requisição
    $json_data = file_get_contents('php://input');
    $new_data = json_decode($json_data, true);

    if (empty($new_data)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "No data received or invalid JSON."]);
        exit;
    }

    $file_path = 'galeria.json';
    $existing_data = [];

    // 2. Tenta ler o ficheiro existente
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        // Garante que o conteúdo é JSON válido antes de decodificar
        if (!empty($content)) {
            $decoded_content = json_decode($content, true);
            if (is_array($decoded_content)) {
                $existing_data = $decoded_content;
            }
        }
    }

    // 3. Adiciona os novos desenhos (já formatados no JS) aos dados existentes
    // Assumimos que $new_data é um array de objetos [ {desenho1}, {desenho2}, ... ]
    $updated_data = array_merge($existing_data, $new_data);

    // 4. Escreve os dados atualizados de volta no ficheiro JSON
    if (file_put_contents($file_path, json_encode($updated_data, JSON_PRETTY_PRINT))) {
        http_response_code(200);
        echo json_encode(["success" => true, "count" => count($new_data), "message" => "Gallery data saved successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to write to galeria.json."]);
    }

} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use POST."]);
}
?>