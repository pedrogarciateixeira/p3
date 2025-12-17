<?php
// Impede que erros de PHP sujem a saída JSON
error_reporting(0); 
header('Content-Type: application/json; charset=utf-8');

$ficheiro = 'galeria.json';

if (file_exists($ficheiro)) {
    $conteudo = file_get_contents($ficheiro);
    $galeriaCompleta = json_decode($conteudo, true);

    // Verifica se o JSON é válido e se é um array
    if (is_array($galeriaCompleta)) {
        
        // 1. Fazemos o shuffle (baralhar) de todos os itens da galeria
        shuffle($galeriaCompleta);

        // 2. Extraímos apenas os primeiros 6 itens
        // O array_slice garante que se houver menos de 6, ele envia o que existir
        $selecaoAleatoria = array_slice($galeriaCompleta, 0, 6);

        // 3. Devolvemos apenas os 6 objetos selecionados
        echo json_encode($selecaoAleatoria);

    } else {
        echo json_encode(["error" => "Conteudo do ficheiro invalido"]);
    }
} else {
    echo json_encode(["error" => "Ficheiro galeria.json nao encontrado"]);
}
?>