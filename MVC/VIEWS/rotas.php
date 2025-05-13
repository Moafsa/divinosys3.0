<?php
// Arquivo de rotas do sistema
if (!isset($view)) {
    $view = 'Dashboard1';
}

error_log("View solicitada: " . $view);

switch ($view) {
    case 'Dashboard1':
        include_once(__DIR__ . "/Dashboard1.php");
        break;
        
    case 'Dashboard2':
        include_once(__DIR__ . "/Dashboard2.php");
        break;
        
    case 'Pedidos':
    case 'pedidos':
    case 'gerenciar_pedidos':
    case 'pedidos_cards':
        include_once(__DIR__ . "/pedidos.php"); // Usando a nova página de pedidos
        break;
        
    case 'editar_pedido':
        include_once(__DIR__ . "/editar_pedido.php");
        break;
        
    case 'Produtos':
        include_once(__DIR__ . "/produtos.php");
        break;
        
    case 'Configuracoes':
        include_once(__DIR__ . "/configuracoes.php");
        break;
        
    case 'tabela':
        include_once(__DIR__ . "/tabela.php");
        break;
        
    case 'gerar_pedido':
        include_once(__DIR__ . "/gerar_pedido.php");
        break;
        
    case 'gerenciar_categorias':
        include_once(__DIR__ . "/gerenciar_categorias.php");
        break;
        
    case 'gerenciar_produtos':
        include_once(__DIR__ . "/gerenciar_produtos.php");
        break;
        
    case 'mesas':
        include_once(__DIR__ . "/mesas.php");
        break;
        
    case 'delivery':
        include_once(__DIR__ . "/delivery.php");
        break;
        
    case 'novo_pedido':
        include_once(__DIR__ . "/novo_pedido.php");
        break;
        
    case 'configuracao':
        include_once(__DIR__ . "/configuracao.php");
        break;
        
    case 'financeiro':
        include_once(__DIR__ . "/financeiro/index.php");
        break;
        
    default:
        include_once(__DIR__ . "/404.php");
        break;
} 