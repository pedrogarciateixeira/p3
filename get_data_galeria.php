<?php
// Impede que erros de PHP sujem a saída JSON
error_reporting(0); 
header('Content-Type: application/json; charset=utf-8');

$galeria = 'galeria.json';


if (file_exists($galeria)) {
    $data_galeria = file_get_contents($galeria);
    if ($data_galeria === false) {
        echo json_encode(["error" => "Erro ao ler o ficheiro"]);
    } else {
        echo $data_galeria; // Saída do JSON puro
    }
} else {
    // Retorna um erro em formato JSON se o ficheiro não existir
    echo json_encode(["error" => "Ficheiro dados.json nao encontrado no servidor"]);
}
?>