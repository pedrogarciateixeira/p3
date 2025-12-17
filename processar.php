<?php
// Configurações de erro e fuso horário
ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Lisbon');

$ficheiro = 'galeria.json';
$mensagem = '';
$galeria_content = '';
$desafio_content = ''; 

// =================================================================
//                 FUNÇÕES DE SELEÇÃO ALEATÓRIA (AGORA 6 OBJETOS)
// =================================================================

/**
 * Função 1: Devolve 6 pares correspondentes (Desenho + O seu Nome)
 */
function obterCorrespondentes($ficheiro) {
    if (!file_exists($ficheiro)) return null;
    $dados = json_decode(file_get_contents($ficheiro), true);
    // Verificar se existem pelo menos 6 desenhos
    if (!is_array($dados) || count($dados) < 2) return null;

    // Baralha e retira 6 itens
    shuffle($dados);
    $selecionados = array_slice($dados, 0, 2);

    $resultado = array();
    foreach ($selecionados as $item) {
        $art = isset($item['artigo']) ? $item['artigo'] : '';
        $pal = isset($item['palavra']) ? $item['palavra'] : '';
        $resultado[] = array(
            'svg' => $item['svg'],
            'nome_completo' => trim("$art $pal")
        );
    }
    return $resultado;
}

/**
 * Função 2: Devolve 6 desenhos e 6 nomes de forma totalmente aleatória (Misturados)
 */
function obterMisturados($ficheiro) {
    if (!file_exists($ficheiro)) return null;
    $dados = json_decode(file_get_contents($ficheiro), true);
    
    // Precisamos de pelo menos 6 para o seu pedido, 
    // mas a lógica de "nunca ser o correto" exige uma lista variada.
    if (!is_array($dados) || count($dados) < 2) return null;

    // 1. Escolher 6 objetos aleatórios para serem os "Desenhos"
    shuffle($dados);
    $selecionados_desenhos = array_slice($dados, 0, 2);

    $resultado = array();

    foreach ($selecionados_desenhos as $desenho) {
        // 2. Criar uma lista de todos os outros nomes disponíveis 
        // filtrando para remover o ID do desenho atual
        $nomes_possiveis = array_filter($dados, function($item) use ($desenho) {
            return $item['id'] !== $desenho['id'];
        });

        // 3. Escolher um nome aleatório desta lista filtrada
        shuffle($nomes_possiveis);
        $nome_escolhido = $nomes_possiveis[0];

        $art = isset($nome_escolhido['artigo']) ? $nome_escolhido['artigo'] : '';
        $pal = isset($nome_escolhido['palavra']) ? $nome_escolhido['palavra'] : '';

        $resultado[] = array(
            'svg' => $desenho['svg'],
            'nome_sugerido' => trim("$art $pal")
        );
    }

    return $resultado;
}


 function obterAleatorio() {
    // Gerar nome aleatório
    $caracteres = 'abcdefghijklmnopqrstuvwxyz';
    $palavra = '';
    for ($i = 0; $i < rand(5, 8); $i++) {
        $palavra .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }

    // Regra do Inglês: an antes de vogais, a antes de consoantes
    $vogais = ['a', 'e', 'i', 'o', 'u'];
    $artigo = in_array($palavra[0], $vogais) ? 'an' : 'a';

    // Usar um ID aleatório no URL para o Unsplash não repetir a mesma imagem 6 vezes
    $randomID = rand(1, 1000);
    $url = "https://picsum.photos/seed/$randomID/500/500"; 
    // Nota: Mudei para Picsum Photos porque o Source Unsplash está instável em alguns servidores

    return [
        'svg' => $url,
        'nome_sugerido' => "$artigo $palavra"
    ];
}

/* function obterAleatorio() {
    // 1. Gerar nome aleatório
    $caracteres = 'abcdefghijklmnopqrstuvwxyz';
    $palavra = '';
    $tamanho = rand(5, 8);
    for ($i = 0; $i < $tamanho; $i++) {
        $palavra .= $caracteres[rand(0, strlen($caracteres) - 1)];
    }

    // 2. Regra do Inglês: an antes de vogais, a antes de consoantes
    $vogais = ['a', 'e', 'i', 'o', 'u'];
    $artigo = in_array($palavra[0], $vogais) ? 'an' : 'a';

    // 3. Obter imagem via LoremFlickr
    // Usamos um "lock" aleatório para garantir que cada um dos 6 objetos seja uma imagem diferente
    $randomLock = rand(1, 10000);
    // Definimos um tema genérico como 'abstract' ou 'thing' para variar os objetos
    $url_imagem = "https://loremflickr.com/500/500/object,thing?lock=$randomLock";

    return [
        'svg' => $url_imagem, // Mantemos 'svg' para não quebrar o teu código de exibição
        'nome_sugerido' => "$artigo $palavra"
    ];
} */


// =================================================================
//                 LÓGICA PRINCIPAL DE AÇÕES
// =================================================================

if (isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if ($acao === 'salvar') {
        if (!empty($_POST['dados_svg']) && !empty($_POST['palavra'])) {
            
            $input_palavra = trim($_POST['palavra']);
            $palavra_para_analise = strtolower($input_palavra);
            $artigo = '';
            $palavra_limpa = $input_palavra;

            // Lógica de extração/atribuição do artigo
            if (substr($palavra_para_analise, 0, 2) === 'a ') {
                $artigo = 'a';
                $palavra_limpa = trim(substr($input_palavra, 2));
            } elseif (substr($palavra_para_analise, 0, 3) === 'an ') {
                $artigo = 'an';
                $palavra_limpa = trim(substr($input_palavra, 3));
            } else {
                $primeira_letra = strtolower(substr($input_palavra, 0, 1));
                $vogais = array('a', 'e', 'i', 'o', 'u');
                $artigo = in_array($primeira_letra, $vogais) ? 'an' : 'a';
                $palavra_limpa = $input_palavra;
            }

            $id_unico = uniqid('id_', true);

            $novo_item = array(
                'id'      => $id_unico,
                'svg'     => $_POST['dados_svg'],
                'artigo'  => $artigo,
                'palavra' => htmlspecialchars($palavra_limpa),
                'data'    => date('d/m/Y H:i:s')
            );

            $lista = array();
            if (file_exists($ficheiro)) {
                $conteudo = file_get_contents($ficheiro);
                $dados_existentes = json_decode($conteudo, true);
                if (is_array($dados_existentes)) { $lista = $dados_existentes; }
            }

            $lista[] = $novo_item;

            if (file_put_contents($ficheiro, json_encode($lista, JSON_PRETTY_PRINT)) !== false) {
                $mensagem = "✅ Guardado! Artigo: $artigo";
            } else {
                $mensagem = "❌ Erro ao guardar o ficheiro.";
            }

        } else {
            $mensagem = '⚠️ Por favor, faça um desenho e escreva uma palavra.';
        }
    
    // BLOCO: Ligar Pares Certos (AGORA 6 CORRESPONDENTES)
} elseif ($acao === 'correspondentes') {
    $pares = obterCorrespondentes($ficheiro);
    if ($pares) {
        $desafio_content .= '<h3>✅ 6 Pares Correspondentes</h3>';
        $desafio_content .= '<p class="msg-desafio">Cada desenho está associado ao nome correto, proveniente do mesmo registo.</p>';
        $desafio_content .= '<div class="galeria">';
        
        foreach ($pares as $par) {
            $desafio_content .= "<div class='card desafio-card'>";
            $desafio_content .= "<div class='svg-container'>" . $par['svg'] . "</div>";
            $desafio_content .= "<div class='info'>";
            $desafio_content .= "<strong>" . $par['nome_completo'] . "</strong>";
            $desafio_content .= "</div>";
            $desafio_content .= "</div>";
        }
        $desafio_content .= '</div>';
        $mensagem = 'Foram apresentados 6 pares de desenhos e nomes correspondentes.';
    } else {
        $mensagem = '⚠️ São necessários pelo menos 6 desenhos na galeria para este desafio.';
    }

// BLOCO: Ligar Pares Aleatórios (AGORA 6 MISTURADOS)
} elseif ($acao === 'misturados') {
    $misturados = obterMisturados($ficheiro);
    if ($misturados) {
        $desafio_content .= '<h3>❓ 6 Pares Aleatórios</h3>';
        $desafio_content .= '<p class="msg-desafio">O desenho e o nome foram escolhidos de forma independente e podem não corresponder.</p>';
        $desafio_content .= '<div class="galeria">';
        
        foreach ($misturados as $par) {
            $desafio_content .= "<div class='card desafio-card'>";
            $desafio_content .= "<div class='svg-container'>" . $par['svg'] . "</div>";
            $desafio_content .= "<div class='info'>";
            $desafio_content .= "<strong>" . $par['nome_sugerido'] . "</strong>";
            $desafio_content .= "</div>";
            $desafio_content .= "</div>";
        }
        $desafio_content .= '</div>';
        $mensagem = 'Foram apresentados 6 desenhos com 6 nomes aleatórios.';
    } else {
        $mensagem = '⚠️ São necessários pelo menos 6 desenhos na galeria para este desafio.';
    }

// BLOCO: Mostrar Galeria Padrão
} elseif ($acao === 'mostrar') {
    if (file_exists($ficheiro)) {
        $dados = json_decode(file_get_contents($ficheiro), true);
        if (is_array($dados) && count($dados) > 0) {
            foreach ($dados as $item) {
                $id = isset($item['id']) ? $item['id'] : 'N/A';
                $art = isset($item['artigo']) ? $item['artigo'] : '';
                $pal = isset($item['palavra']) ? $item['palavra'] : '';

                $galeria_content .= "<div class='card' id='{$id}'>";
                $galeria_content .= "<div class='svg-container'>" . $item['svg'] . "</div>";
                $galeria_content .= "<div class='info'>";
                $galeria_content .= "<strong>" . trim("$art $pal") . "</strong><br>";
                $galeria_content .= "<small>ID: {$id}</small><br>";
                $galeria_content .= "<small>Data: " . $item['data'] . "</small>";
                $galeria_content .= "</div>";
                $galeria_content .= "</div>";
            }
        } else {
            $mensagem = '⚠️ Galeria vazia.';
        }
    }



} elseif ($acao === 'aleatorios') {
    $aleatorios = [];
// Gerar 6 pares aleatórios
for ($i = 0; $i < 6; $i++) {
    $par = obterAleatorio();
    if ($par) {
        $aleatorios[] = $par;
    }
}

if (count($aleatorios) >= 6) {
    $desafio_content .= '<h3>❓ 6 Pares Aleatórios</h3>';
    $desafio_content .= '<p class="msg-desafio">O desenho e o nome foram escolhidos de forma independente e podem não corresponder.</p>';
    $desafio_content .= '<div class="galeria">';
    
    foreach ($aleatorios as $par) {
        $desafio_content .= "<div class='card desafio-card'>";
        // Correção: Adicionada a fechar a tag img e alt para acessibilidade
        $desafio_content .= "<div class='svg-container'>";
        $desafio_content .= "<img src='" . $par['svg'] . "' alt='Imagem Aleatória' style='width:100%; height:auto; border-radius:4px;'>";
        $desafio_content .= "</div>";
        $desafio_content .= "<div class='info'>";
        $desafio_content .= "<strong>" . htmlspecialchars($par['nome_sugerido']) . "</strong>";
        $desafio_content .= "</div>";
        $desafio_content .= "</div>";
    }
    $desafio_content .= '</div>';
    $mensagem = 'Foram apresentados 6 desenhos com 6 nomes aleatórios.';
} else {
    $mensagem = '⚠️ Erro ao gerar as imagens aleatórias.';
}
}
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Galeria de Memórias e Desafios</title>
<style>
    /* ... (Estilos básicos mantidos) ... */
    body { font-family: sans-serif; background: #f4f4f9; padding: 20px; }
    .container { max-width: 900px; margin: 0 auto; }
    .msg { padding: 15px; background: #fff; border-left: 5px solid #28a745; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    
    /* ESTILOS PARA GALERIA E DESAFIO (USAM A MESMA CLASSE .galeria) */
    .galeria { 
        display: flex; 
        flex-wrap: wrap; 
        gap: 20px; 
        justify-content: center; /* Centraliza cartões */
        margin-top: 20px;
    }
    .card { 
        background: white; 
        border: 1px solid #ddd; 
        border-radius: 10px; 
        width: 250px; /* Largura padrão mantida */
        overflow: hidden; 
        box-shadow: 0 4px 8px rgba(0,0,0,0.05); 
    }
    
    /* Ajuste para 6 colunas (3 por linha no max-width 900px) */
    .desafio-card {
         width: calc(33.33% - 15px); /* Três cartões por linha com gap de 20px */
         min-width: 250px;
    }

    .svg-container { background: #fff; padding: 10px; height: 180px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #eee; }
    .svg-container svg { max-width: 100%; max-height: 100%; }
    .info { padding: 15px; text-align: center; }
    .info strong { color: #1a73e8; font-size: 1.1em; }
    .info small { color: #888; font-size: 0.8em; display: block; }
    .btn-back { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #333; color: white; text-decoration: none; border-radius: 5px; }

    /* Estilos específicos do Desafio */
    h3 { text-align: center; color: #dc3545; }
    .msg-desafio { font-style: italic; color: #555; text-align: center; margin-bottom: 15px; background: #fff; padding: 10px; border-radius: 5px; }
    
    .svg-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain; /* Mantém a proporção sem cortar a imagem */
}
</style>
</head>
<body>
<div class="container">
    <?php if ($mensagem): ?> <div class="msg"><?php echo $mensagem; ?></div> <?php endif; ?>
    
    <?php if ($desafio_content): ?>
        <div class="desafio">
            <?php echo $desafio_content; ?>
        </div>
    <?php else: ?>
        <h2>🖼️ Galeria de Memórias</h2>
        <div class="galeria">
            <?php echo $galeria_content ?: '<p>Nenhum desenho encontrado.</p>'; ?>
        </div>
    <?php endif; ?>
    
    <hr>
    <a href="index.html" class="btn-back">⬅️ Voltar ao Início</a>
</div>
</body>
</html>