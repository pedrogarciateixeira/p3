<?php
date_default_timezone_set('Europe/Lisbon');


$ficheiro = 'galeria.json';
$mensagem = '';
$galeria_content = '';

// --- Lógica de Processamento ---

if (isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if ($acao === 'salvar') {
        if (!empty($_POST['dados_svg']) && !empty($_POST['palavra'])) {
            
            $novo_item = array(
                'svg'     => $_POST['dados_svg'],
                'palavra' => htmlspecialchars($_POST['palavra']),
                'data'    => date('d/m/Y H:i')
            );

            $lista = array();
            if (file_exists($ficheiro)) {
                $conteudo_atual = file_get_contents($ficheiro);
                $lista = json_decode($conteudo_atual, true);
                // Fallback para versões antigas do PHP se o decode falhar
                if (!is_array($lista)) { $lista = array(); }
            }

            $lista[] = $novo_item;

            if (file_put_contents($ficheiro, json_encode($lista)) !== false) {
                $mensagem = '✅ Memória guardada com sucesso!';
            } else {
                $mensagem = '❌ Erro ao escrever no ficheiro.';
            }
        } else {
            $mensagem = '⚠️ Por favor, faça um desenho e escreva uma palavra.';
        }
    } elseif ($acao === 'mostrar') {
        if (file_exists($ficheiro)) {
            $dados = json_decode(file_get_contents($ficheiro), true);
            
            if (is_array($dados) && count($dados) > 0) {
                foreach ($dados as $item) {
                    $galeria_content .= "<div class='card'>";
                    $galeria_content .= "<div class='svg-container'>" . $item['svg'] . "</div>";
                    $galeria_content .= "<div class='info'>";
                    $galeria_content .= "<strong>Palavra:</strong> " . $item['palavra'] . "<br>";
                    $galeria_content .= "<small>" . $item['data'] . "</small>";
                    $galeria_content .= "</div>";
                    $galeria_content .= "</div>";
                }
            } else {
                $mensagem = '⚠️ A galeria está vazia.';
            }
        } else {
            $mensagem = '⚠️ O ficheiro de dados ainda não foi criado.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Memórias</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .msg { padding: 15px; background: #fff; border-left: 5px solid #28a745; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        
        .galeria { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }
        .card { background: white; border: 1px solid #ddd; border-radius: 10px; width: 250px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        
        .svg-container { background: #fff; padding: 10px; height: 180px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #eee; }
        .svg-container svg { max-width: 100%; max-height: 100%; }
        
        .info { padding: 15px; }
        .info strong { color: #1a73e8; font-size: 1.1em; }
        .info small { color: #888; display: block; margin-top: 5px; }
        
        .btn-back { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #333; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>

<div class="container">
    <?php if ($mensagem): ?>
        <div class="msg"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <h2>🖼️ Galeria de Memórias (SVG)</h2>
    
    <div class="galeria">
        <?php echo $galeria_content; ?>
    </div>

    <hr>
    <a href="index.html" class="btn-back">⬅️ Voltar ao Início</a>
</div>

</body>
</html>