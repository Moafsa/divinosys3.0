<?php
// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir o arquivo de conexão
include_once(__DIR__ . "/include_conexao.php");
include_once(__DIR__ . "/../MODEL/config.php");
$config = Config::getInstance();

// Verificar se o ID do pedido foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<div class='alert alert-danger'>ID do pedido não fornecido</div>";
    exit;
}

$idpedido = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Buscar dados do pedido
    $query = "SELECT p.*, m.id_mesa as num_mesa, pr.id as produto_id, pr.nome as produto_nome, 
                     pr.descricao, pr.categoria_id, c.nome as categoria_nome, pr.preco_normal
              FROM pedido p 
              LEFT JOIN mesas m ON p.idmesa = m.id_mesa 
              LEFT JOIN produtos pr ON p.produto = pr.nome
              LEFT JOIN categorias c ON pr.categoria_id = c.id
              WHERE p.idpedido = ?";
              
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idpedido);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $pedido = mysqli_fetch_assoc($result);
    
    if (!$pedido) {
        throw new Exception("Pedido não encontrado");
    }

    // Buscar categorias
    $stmt = mysqli_prepare($conn, "
        SELECT id, nome, imagem 
        FROM categorias 
        ORDER BY 
            CASE nome 
                WHEN 'XIS' THEN 1
                WHEN 'Cachorro-Quente' THEN 2
                WHEN 'Bauru' THEN 3
                WHEN 'PF e A La Minuta' THEN 4
                WHEN 'Torrada' THEN 5
                WHEN 'Rodízio' THEN 6
                WHEN 'Porções' THEN 7
                WHEN 'Bebidas' THEN 8
                WHEN 'Bebidas Alcoólicas' THEN 9
                ELSE 10
            END
    ");
    mysqli_stmt_execute($stmt);
    $categorias = mysqli_stmt_get_result($stmt);

    // Buscar produtos da categoria atual
    if ($pedido['categoria_id']) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM produtos WHERE categoria_id = ? ORDER BY nome");
        mysqli_stmt_bind_param($stmt, "i", $pedido['categoria_id']);
        mysqli_stmt_execute($stmt);
        $produtos = mysqli_stmt_get_result($stmt);
    }
    
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Erro ao carregar pedido: " . $e->getMessage() . "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Pedido #<?php echo $idpedido; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ea1d2c;
            --secondary-color: #f7f7f7;
        }

        body {
            background-color: var(--secondary-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 80px;
        }

        .category-menu {
            background: white;
            padding: 15px;
            margin-bottom: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 15px;
            justify-items: center;
        }

        .category-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            cursor: pointer;
            padding: 10px;
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .category-image {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e8e8e8;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            background-color: #fff;
        }

        .category-name {
            font-size: 13px;
            font-weight: 500;
            color: #666;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        .category-item:hover .category-image {
            transform: scale(1.1);
            border-color: var(--primary-color);
        }

        .category-item.active .category-image {
            border-color: var(--primary-color);
            border-width: 3px;
        }

        .category-item:hover .category-name,
        .category-item.active .category-name {
            color: var(--primary-color);
        }

        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.2s;
            cursor: pointer;
            margin-bottom: 20px;
            border: 1px solid #eee;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-card:hover .product-img {
            transform: scale(1.1);
        }

        .product-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-price {
            color: var(--primary-color);
            font-weight: bold;
        }

        .ingrediente-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .ingrediente-item:hover {
            background-color: #f8f9fa;
        }

        .ingrediente-item label {
            margin-bottom: 0;
            margin-left: 10px;
            flex-grow: 1;
        }

        .modal-header {
            background-color: var(--primary-color);
            color: white;
        }

        .modal-header .btn-close {
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: #c41824;
            border-color: #c41824;
        }

        .loading {
            position: relative;
            pointer-events: none;
            opacity: 0.6;
        }
        
        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 30px;
            height: 30px;
            margin: -15px 0 0 -15px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .product-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .product-card.active {
            border-color: #3498db;
            background-color: #f8f9fa;
        }
        
        .ingrediente-item {
            transition: all 0.3s ease;
        }
        
        .ingrediente-item:hover {
            background-color: #f8f9fa;
        }
        
        .quantidade-ingrediente {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .btn-outline-danger, .btn-outline-success {
            padding: 0.25rem 0.5rem;
            line-height: 1;
        }
        
        .btn-outline-danger:hover, .btn-outline-success:hover {
            transform: scale(1.1);
        }
        
        #lista-ingredientes {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 5px;
        }
        
        #lista-ingredientes::-webkit-scrollbar {
            width: 6px;
        }
        
        #lista-ingredientes::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        #lista-ingredientes::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }
        
        #lista-ingredientes::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Categorias e Produtos (Lado Esquerdo) -->
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Editar Pedido #<?php echo $idpedido; ?></h4>
                <a href="?view=pedidos" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>

            <!-- Campo de Busca -->
            <div class="mb-4">
                <div class="input-group">
                    <input type="text" id="busca-produto" class="form-control" placeholder="Buscar produtos...">
                    <button class="btn btn-outline-secondary" type="button" onclick="buscarProdutos()">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </div>

            <!-- Menu de Categorias -->
            <div class="category-menu">
                <?php while ($categoria = mysqli_fetch_assoc($categorias)): 
                    $imagem = $categoria['imagem'] ? $categoria['imagem'] : 'mvc/common/img/no-image.jpg';
                ?>
                    <div class="category-item <?php echo ($categoria['id'] == $pedido['categoria_id']) ? 'active' : ''; ?>" 
                         data-categoria="<?php echo $categoria['id']; ?>">
                        <img src="<?php echo $imagem; ?>" 
                             alt="<?php echo htmlspecialchars($categoria['nome']); ?>" 
                             class="category-image"
                             onerror="this.src='mvc/common/img/no-image.jpg'">
                        <span class="category-name"><?php echo htmlspecialchars($categoria['nome']); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Lista de Produtos -->
            <div class="row" id="listaProdutos">
                <?php if (isset($produtos)): ?>
                    <?php while ($produto = mysqli_fetch_assoc($produtos)): ?>
                        <div class="col-md-4 mb-4">
                            <div class="product-card <?php echo ($produto['id'] == $pedido['produto_id']) ? 'active' : ''; ?>"
                                 onclick="selecionarProduto(<?php echo $produto['id']; ?>)">
                                <img src="<?php echo $produto['imagem'] ?? 'mvc/common/img/no-image.jpg'; ?>" 
                                     class="product-img" 
                                     alt="<?php echo htmlspecialchars($produto['nome']); ?>"
                                     onerror="this.src='mvc/common/img/no-image.jpg'">
                                <div class="product-info">
                                    <h5><?php echo htmlspecialchars($produto['nome']); ?></h5>
                                    <p class="text-muted small mb-2"><?php echo htmlspecialchars($produto['descricao']); ?></p>
                                    <div class="product-price">
                                        R$ <?php echo number_format($produto['preco_normal'], 2, ',', '.'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Detalhes do Pedido (Lado Direito) -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Detalhes do Pedido</h5>
                </div>
                <div class="card-body">
                    <form id="formEditarPedido" onsubmit="return salvarEdicao(event)">
                        <input type="hidden" name="idpedido" value="<?php echo $pedido['idpedido']; ?>">
                        <input type="hidden" name="produto_id" id="produto_id" value="<?php echo $pedido['produto_id'] ?? ''; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Produto Selecionado</label>
                            <input type="text" class="form-control" id="produto_nome" 
                                   value="<?php echo htmlspecialchars($pedido['produto_nome'] ?? ''); ?>" readonly>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Quantidade</label>
                                <input type="number" class="form-control" name="quantidade" id="quantidade" 
                                       value="<?php echo $pedido['quantidade'] ?? 1; ?>" required min="1" 
                                       onchange="atualizarValorTotal()">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Valor Unitário</label>
                                <input type="number" class="form-control" name="valor" id="valor" 
                                       value="<?php echo $pedido['valor'] ?? 0; ?>" required step="0.01" readonly>
                            </div>
                        </div>

                        <div id="ingredientes-container">
                            <h6>Ingredientes</h6>
                            <div id="lista-ingredientes" class="mb-3">
                                <!-- Ingredientes serão carregados via JavaScript -->
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea class="form-control" name="observacao" rows="3"
                                      placeholder="Ex: Sem cebola, bem passado, etc"><?php echo htmlspecialchars($pedido['observacao'] ?? ''); ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Status</label>
                                <select class="form-control" name="status" required>
                                    <?php
                                    $status_options = ['Pendente', 'Em Preparo', 'Pronto', 'Entregue', 'Cancelado'];
                                    $current_status = $pedido['status'] ?? 'Pendente';
                                    foreach ($status_options as $status) {
                                        echo '<option value="' . $status . '"' . 
                                             ($status == $current_status ? ' selected' : '') . '>' . 
                                             $status . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Mesa</label>
                                <input type="number" class="form-control" name="idmesa" 
                                       value="<?php echo $pedido['idmesa'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="valor_total">Valor Total</label>
                            <input type="text" class="form-control" id="valor_total" name="valor_total" 
                                   value="<?php echo number_format($pedido['valor'] * $pedido['quantidade'], 2, '.', ''); ?>" readonly>
                        </div>

                        <!-- Botões de impressão -->
                        <div class="form-group mt-3">
                            <button type="button" class="btn btn-info me-2" onclick="imprimirPedido()">
                                <i class="fas fa-print"></i> Imprimir
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="imprimirCozinha()">
                                <i class="fas fa-utensils"></i> Imprimir Cozinha
                            </button>
                        </div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let timeoutId;
    
    // Carregar ingredientes do produto inicial
    if ($('#produto_id').val()) {
        carregarIngredientesProduto($('#produto_id').val());
    }

    // Busca em tempo real com debounce
    $('#busca-produto').on('input', function() {
        clearTimeout(timeoutId);
        const termo = $(this).val().trim();
        
        if (termo.length >= 2) {
            timeoutId = setTimeout(() => {
                buscarProdutos(termo);
            }, 300);
        }
    });

    // Quando clicar em uma categoria
    $('.category-item').click(function() {
        const categoriaId = $(this).data('categoria');
        $('.category-item').removeClass('active');
        $(this).addClass('active');
        $('#busca-produto').val(''); // Limpar campo de busca
        
        // Carregar produtos da categoria
        $.ajax({
            url: 'MVC/MODEL/buscar_produtos_categoria.php',
            type: 'GET',
            data: { categoria_id: categoriaId },
            beforeSend: function() {
                $('#listaProdutos').addClass('loading');
            },
            success: function(response) {
                $('#listaProdutos').removeClass('loading').html(response);
            },
            error: function() {
                $('#listaProdutos').removeClass('loading');
                Swal.fire('Erro', 'Erro ao carregar produtos', 'error');
            }
        });
    });
});

function buscarProdutos(termo) {
    if (!termo) return;
    
    $.ajax({
        url: 'MVC/MODEL/buscar_produtos_nome.php',
        type: 'GET',
        data: { termo: termo },
        beforeSend: function() {
            $('#listaProdutos').addClass('loading');
        },
        success: function(response) {
            $('#listaProdutos').removeClass('loading').html(response);
            $('.category-item').removeClass('active');
        },
        error: function(xhr, status, error) {
            $('#listaProdutos').removeClass('loading');
            console.error('Erro na busca:', error);
            Swal.fire('Erro', 'Erro ao buscar produtos', 'error');
        }
    });
}

// Função unificada para carregar ingredientes
async function carregarIngredientes(produtoId) {
    if (!produtoId) {
        console.warn('ID do produto não fornecido');
        return;
    }

    try {
        console.log('Carregando ingredientes para produto:', produtoId); // Debug
        const response = await fetch(`MVC/MODEL/buscar_ingredientes.php?produto_id=${produtoId}`);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Resposta do servidor:', errorText); // Debug
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Dados recebidos:', data); // Debug
        
        if (!data.success) {
            throw new Error(data.message || 'Erro ao carregar ingredientes');
        }
        
        const container = document.getElementById('lista-ingredientes');
        if (!container) {
            console.error('Container de ingredientes não encontrado');
            return;
        }
        
        if (!data.ingredientes || data.ingredientes.length === 0) {
            container.innerHTML = '<p class="text-muted">Nenhum ingrediente cadastrado para este produto.</p>';
            return;
        }
        
        container.innerHTML = data.ingredientes.map(ingrediente => `
            <div class="ingrediente-item mb-2">
                <div class="d-flex align-items-center justify-content-between border rounded p-2">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" id="ing_${ingrediente.id}" 
                               name="ingredientes[]" 
                               value="${ingrediente.id}" 
                               class="form-check-input me-2"
                               ${ingrediente.selecionado ? 'checked' : ''}>
                        <label for="ing_${ingrediente.id}" class="mb-0">
                            ${ingrediente.nome}
                        </label>
                    </div>
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-danger me-2"
                                onclick="alterarQuantidadeIngrediente(${ingrediente.id}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="quantidade-ingrediente" data-id="${ingrediente.id}" 
                              style="min-width: 30px; text-align: center;">
                            ${ingrediente.quantidade_padrao}
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-success ms-2"
                                onclick="alterarQuantidadeIngrediente(${ingrediente.id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
        
    } catch (error) {
        console.error('Erro ao carregar ingredientes:', error);
        const container = document.getElementById('lista-ingredientes');
        if (container) {
            container.innerHTML = `
                <div class="alert alert-danger">
                    Erro ao carregar ingredientes: ${error.message}
                </div>`;
        }
        Swal.fire({
            icon: 'error',
            title: 'Erro',
            text: error.message || 'Erro ao carregar ingredientes'
        });
    }
}

// Atualizar a função selecionarProduto para usar a nova função unificada
async function selecionarProduto(produtoId) {
    if (!produtoId) {
        console.error('ID do produto não fornecido');
        return;
    }

    try {
        // Buscar dados do produto
        const response = await fetch(`MVC/MODEL/buscar_produto.php?id=${produtoId}`);
        if (!response.ok) throw new Error('Erro ao buscar produto');
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Erro ao buscar produto');
        }

        const produto = data.produto;

        // Atualizar campos do produto
        document.getElementById('produto_id').value = produto.id;
        document.getElementById('produto_nome').value = produto.nome;
        document.getElementById('valor').value = produto.preco_normal;
        document.getElementById('quantidade').value = '1';

        // Carregar ingredientes usando a função unificada
        await carregarIngredientes(produtoId);

        atualizarValorTotal();
    } catch (error) {
        console.error('Erro:', error);
        Swal.fire('Erro', error.message || 'Erro ao carregar dados do produto', 'error');
    }
}

function alterarQuantidadeIngrediente(ingredienteId, delta) {
    const spanQuantidade = $(`.quantidade-ingrediente[data-id="${ingredienteId}"]`);
    let quantidade = parseInt(spanQuantidade.text()) || 0;
    quantidade = Math.max(0, quantidade + delta);
    spanQuantidade.text(quantidade);
    
    // Se a quantidade for 0, desmarcar o checkbox
    if (quantidade === 0) {
        $(`#ing_${ingredienteId}`).prop('checked', false);
    } else {
        $(`#ing_${ingredienteId}`).prop('checked', true);
    }
    
    // Atualizar valor total se necessário
    atualizarValorTotal();
}

function atualizarValorTotal() {
    const valorCampo = document.getElementById('valor');
    const quantidadeCampo = document.getElementById('quantidade');
    const valorTotalCampo = document.getElementById('valor_total');

    if (!valorCampo || !quantidadeCampo || !valorTotalCampo) {
        console.warn('Campos necessários para cálculo não encontrados');
        return;
    }

    const valor = parseFloat(valorCampo.value) || 0;
    const quantidade = parseInt(quantidadeCampo.value) || 1;
    
    // Calcular valor total
    const valorTotal = valor * quantidade;
    valorTotalCampo.value = valorTotal.toFixed(2);
}

// Adicionar listeners apenas se os elementos existirem
document.addEventListener('DOMContentLoaded', function() {
    const quantidadeCampo = document.getElementById('quantidade');
    const valorCampo = document.getElementById('valor');

    if (quantidadeCampo) {
        quantidadeCampo.addEventListener('change', atualizarValorTotal);
    }
    
    if (valorCampo) {
        valorCampo.addEventListener('change', atualizarValorTotal);
    }
});

function salvarEdicao(event) {
    event.preventDefault();
    
    // Validar campos obrigatórios
    const form = event.target;
    const quantidade = form.querySelector('[name="quantidade"]').value;
    const valor = form.querySelector('[name="valor"]').value;
    const status = form.querySelector('[name="status"]').value;
    const mesa = form.querySelector('[name="idmesa"]').value;

    if (!quantidade || quantidade <= 0) {
        Swal.fire('Erro', 'A quantidade deve ser maior que zero', 'error');
        return false;
    }

    if (!valor || valor <= 0) {
        Swal.fire('Erro', 'O valor deve ser maior que zero', 'error');
        return false;
    }

    if (!mesa) {
        Swal.fire('Erro', 'Selecione uma mesa', 'error');
        return false;
    }

    // Confirmar alterações
    Swal.fire({
        title: 'Confirmar alterações?',
        text: 'Deseja salvar as alterações feitas no pedido?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, salvar',
        cancelButtonText: 'Não, cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData(form);
            
            // Adicionar ingredientes e suas quantidades
            const ingredientes = {};
            $('#lista-ingredientes .ingrediente-item').each(function() {
                const id = $(this).find('input[type="checkbox"]').val();
                const quantidade = parseInt($(this).find('.quantidade-ingrediente').text()) || 0;
                ingredientes[id] = {
                    selecionado: $(this).find('input[type="checkbox"]').is(':checked'),
                    quantidade: quantidade
                };
            });
            formData.append('ingredientes_info', JSON.stringify(ingredientes));
            
            // Adicionar valor total
            formData.append('valor_total', $('#valor_total').val());

            // Mostrar loading
            Swal.fire({
                title: 'Salvando...',
                text: 'Por favor, aguarde',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('MVC/MODEL/salvar_edicao_pedido.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: data.message || 'Pedido atualizado com sucesso!',
                        icon: 'success',
                        showCancelButton: false,
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        // Redirecionar mantendo os filtros anteriores
                        const urlParams = new URLSearchParams(window.location.search);
                        const filtros = {
                            status: urlParams.get('status'),
                            data_inicio: urlParams.get('data_inicio'),
                            data_fim: urlParams.get('data_fim'),
                            pagina: urlParams.get('pagina')
                        };

                        let redirectUrl = '?view=pedidos';
                        Object.keys(filtros).forEach(key => {
                            if (filtros[key]) {
                                redirectUrl += `&${key}=${filtros[key]}`;
                            }
                        });

                        window.location.href = redirectUrl;
                    });
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao atualizar pedido', 'error');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                Swal.fire('Erro', 'Erro ao atualizar pedido. Por favor, tente novamente.', 'error');
            });
        }
    });
    
    return false;
}

// Função para imprimir pedido
function imprimirPedido() {
    const idpedido = document.querySelector('[name="idpedido"]').value;
    window.open(`MVC/VIEWS/imprimir_pedido.php?pedido_id=${idpedido}`, '_blank');
}

// Função para imprimir pedido para cozinha
function imprimirCozinha() {
    const idpedido = document.querySelector('[name="idpedido"]').value;
    window.open(`MVC/VIEWS/imprimir_cozinha.php?pedido_id=${idpedido}`, '_blank');
}

// Carregar ingredientes ao iniciar a página
document.addEventListener('DOMContentLoaded', function() {
    const produtoId = document.getElementById('produto_id').value;
    if (produtoId) {
        carregarIngredientes(produtoId);
    }
});
</script>
</body>
</html> 