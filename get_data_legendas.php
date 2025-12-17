<?php
// Impede que erros de PHP sujem a saída JSON
error_reporting(0); 
header('Content-Type: application/json; charset=utf-8');

$legendas = 'legendas2.json';


if (file_exists($legendas)) {
    $data_legendas = file_get_contents($legendas);
    if ($data_legendas === false) {
        echo json_encode(["error" => "Erro ao ler o ficheiro"]);
    } else {
        echo $data_legendas; // Saída do JSON puro
    }
} else {
    // Retorna um erro em formato JSON se o ficheiro não existir
    echo json_encode(["error" => "Ficheiro dados.json nao encontrado no servidor"]);
}

?>