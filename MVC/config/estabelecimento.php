<?php
/**
 * Configurações do estabelecimento para impressão de cupons fiscais
 */
return [
    'nome_estabelecimento' => 'NOME DO ESTABELECIMENTO',
    'cnpj' => 'XX.XXX.XXX/0001-XX',
    'endereco' => 'Endereço do Estabelecimento',
    'telefone' => '(XX) XXXX-XXXX',
    'site' => 'www.seusite.com.br',
    'email' => 'contato@seusite.com.br',
    
    // Configurações da impressora
    'printer' => [
        'type' => 'thermal', // thermal, matrix, laser
        'width' => 80, // largura do papel em mm
        'chars_per_line' => 42, // caracteres por linha
    ],
    
    // Mensagens personalizadas
    'messages' => [
        'header' => '', // mensagem opcional no topo do cupom
        'footer' => 'Obrigado pela preferência!', // mensagem no rodapé do cupom
    ]
]; 