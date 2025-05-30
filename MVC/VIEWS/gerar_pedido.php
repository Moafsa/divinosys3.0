<?php
// Prevenir qualquer saída antes dos headers
if (ob_get_level()) ob_end_clean();
ob_start();

// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir constante do caminho base
define('BASE_PATH', dirname(dirname(dirname(__FILE__))));

// Incluir arquivos necessários
require_once BASE_PATH . "/MVC/MODEL/conexao.php";
require_once BASE_PATH . "/MVC/MODEL/config.php";
$config = Config::getInstance();

// Verificar se usuário está logado
if (!isset($_SESSION['login']) || $_SESSION['login'] == 0) {
    header("Location: " . url('index.php'));
    exit;
}

// Definir título da página
$page_title = "Novo Pedido";

// Inicializar variáveis
$total = 0;
$todos_pedidos = [];
$error_message = '';
$categorias_data = [];

// Obter parâmetros da URL
$mesa_id = isset($_GET['mesa']) ? intval($_GET['mesa']) : null;
$cliente = isset($_GET['cliente']) ? htmlspecialchars($_GET['cliente']) : '';

try {
    // Verificar conexão
    if (!isset($conn) || !$conn) {
        throw new Exception("Conexão com o banco não está disponível");
    }

    // Buscar categorias
    $stmt = mysqli_prepare($conn, "
        SELECT id, nome 
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
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
    }
    
    $categorias = mysqli_stmt_get_result($stmt);
    if (!$categorias) {
        throw new Exception("Erro ao obter resultados: " . mysqli_error($conn));
    }

    while ($categoria = mysqli_fetch_assoc($categorias)) {
        $categorias_data[] = $categoria;
    }
    
    // Buscar pedidos da mesa se houver
    if ($mesa_id) {
        $stmt = mysqli_prepare($conn, "
            SELECT 
                p.*,
                pr.nome as produto_nome,
                pr.descricao,
                pr.preco_normal as preco_venda,
                p.valor as valor_total
            FROM pedido p 
            LEFT JOIN produtos pr ON pr.nome = p.produto
            WHERE p.idmesa = ? AND p.status != 'Finalizado'
            ORDER BY p.data DESC, p.hora_pedido DESC
        ");
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta de pedidos: " . mysqli_error($conn));
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $mesa_id);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Erro ao executar consulta de pedidos: " . mysqli_stmt_error($stmt));
        }
        
        $pedidos = mysqli_stmt_get_result($stmt);
        if (!$pedidos) {
            throw new Exception("Erro ao obter resultados dos pedidos: " . mysqli_error($conn));
        }
        
        while ($pedido = mysqli_fetch_assoc($pedidos)) {
            $total += floatval(str_replace(',', '.', $pedido['valor']));
            $todos_pedidos[] = $pedido;
        }
    }
} catch (Exception $e) {
    error_log("Erro em gerar_pedido.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $error_message = "Erro ao carregar dados: " . htmlspecialchars($e->getMessage());
}

// Função para formatar valor monetário
function formatMoney($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

// Função para limpar o carrinho da mesa
function limparCarrinho($mesa_id) {
    if (isset($_SESSION['carrinho'][$mesa_id])) {
        unset($_SESSION['carrinho'][$mesa_id]);
    }
}

// Inicializar o carrinho se não existir
if ($mesa_id && !isset($_SESSION['carrinho'][$mesa_id])) {
    $_SESSION['carrinho'][$mesa_id] = array();
}

// Calcular total do carrinho
$total_carrinho = 0;
if ($mesa_id && isset($_SESSION['carrinho'][$mesa_id])) {
    foreach ($_SESSION['carrinho'][$mesa_id] as $index => $item) {
        $total_carrinho += isset($item['valor']) ? $item['valor'] : 0;
    }
}

// Agora que todo o processamento PHP foi concluído, podemos começar a saída HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Divinosys 1.0</title>
    
    <!-- CSS Files -->
    <link href="<?php echo $config->url("MVC/COMMON/CSS/bootstrap.min.css"); ?>" rel="stylesheet">
    <link href="<?php echo $config->url("MVC/COMMON/VENDOR/fontawesome-free/css/all.min.css"); ?>" rel="stylesheet">
    <link href="<?php echo $config->url("MVC/COMMON/CSS/animate.min.css"); ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #ea1d2c;
            --secondary-color: #f7f7f7;
        }

        .search-container {
            position: sticky;
            top: 0;
            z-index: 100;
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .category-container {
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            padding: 1rem 0;
        }

        .category-item {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border-radius: 20px;
            background: var(--secondary-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .category-item.active {
            background: var(--primary-color);
            color: white;
        }

        .product-card {
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .product-image {
            height: 200px;
            object-fit: cover;
        }

        .cart-container {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            padding: 1rem;
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .cart-container.active {
            transform: translateY(0);
        }

        .cart-toggle {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1001;
        }

        .ingredient-toggle {
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
            user-select: none;
        }

        .ingredient-toggle:hover {
            background-color: #e9ecef;
        }

        .ingredient-toggle.included {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .ingredient-toggle.excluded {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            text-decoration: line-through;
        }

        .search-input {
            border-radius: 20px;
            padding: 0.5rem 1rem;
            border: 1px solid #ddd;
            width: 100%;
            font-size: 1rem;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(234, 29, 44, 0.2);
        }

        /* Loading animation */
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }

        .loading.active {
            display: block;
        }

        /* Cart item styles */
        .cart-item {
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
            position: relative;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item .text-danger {
            color: #dc3545 !important;
            font-size: 0.875rem;
            margin-bottom: 2px;
        }

        .cart-item .text-success {
            color: #198754 !important;
            font-size: 0.875rem;
            margin-bottom: 2px;
        }

        .cart-item .text-muted {
            color: #6c757d !important;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .cart-item .fa-trash {
            font-size: 0.875rem;
        }

        #cartTotal {
            font-weight: bold;
            color: #dc3545;
        }

        .cart-total {
            color: #dc3545;
            font-weight: bold;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
        }

        /* Modal customization */
        .modal-content {
            border-radius: 12px;
        }

        .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            background: white;
            transition: all 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        /* Quick add button styles */
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: scale(1.05);
        }

        .btn-success:active {
            transform: scale(0.95);
        }

        .d-flex.gap-2 {
            gap: 0.5rem;
        }

        .flex-grow-1 {
            flex-grow: 1;
        }

        /* Feedback animation */
        @keyframes addToCart {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .add-to-cart-animation {
            animation: addToCart 0.5s ease;
        }

        .cart-count-animation {
            animation: addToCart 0.5s ease;
            background-color: #28a745 !important;
        }
    </style>
</head>

<body>
    <!-- Search Container -->
    <div class="search-container">
        <div class="container">
    <div class="row">
                <div class="col-12">
                    <input type="text" id="searchInput" class="search-input" placeholder="Buscar produtos...">
                </div>
            </div>
                </div>
            </div>

    <!-- Categories -->
    <div class="container">
        <div class="category-container" id="categories">
            <!-- Categories will be loaded here -->
                    </div>
            </div>

    <!-- Products Grid -->
    <div class="container mb-5">
        <div class="row" id="productsGrid">
            <!-- Products will be loaded here -->
        </div>
        <div class="loading" id="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            </div>
        </div>

    <!-- Cart Toggle Button -->
    <div class="cart-toggle" id="cartToggle">
                        <i class="fas fa-shopping-cart"></i> 
        <span class="badge badge-light" id="cartCount">0</span>
                </div>

    <!-- Cart Container -->
    <div class="cart-container" id="cartContainer">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5>Seu Pedido</h5>
                <div id="cartItems">
                        <!-- Cart items will be loaded here -->
                                    </div>
                    <div class="cart-total mt-3">
                        Total: R$ <span id="cartTotal">0,00</span>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="printReceipt" checked>
                        <label class="form-check-label" for="printReceipt">
                            Imprimir cupom fiscal
                        </label>
                    </div>
                    <button class="btn btn-primary btn-block mt-3" id="finishOrder">
                        Finalizar Pedido
                    </button>
                                    </div>
                                </div>
                            </div>
                </div>

    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title" id="modalProductName"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <div class="modal-body">
                    <div class="product-details">
                        <p class="product-description" id="modalProductDescription"></p>
                        <div class="product-price mb-3">
                            <h6>Preço: R$ <span id="modalProductPrice"></span></h6>
            </div>
                        <div class="ingredients-section mb-3">
                            <h6>Ingredientes:</h6>
                            <div id="modalIngredients">
                                <!-- Ingredients will be loaded here -->
                </div>
                    </div>
                        <div class="quantity-section">
                            <h6>Quantidade:</h6>
                            <div class="quantity-control">
                                <button class="quantity-btn" id="decreaseQuantity">-</button>
                                <span id="quantity">1</span>
                                <button class="quantity-btn" id="increaseQuantity">+</button>
                </div>
                </div>
                </div>
            </div>
            <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="addToCart">
                        Adicionar ao Pedido
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Produto -->
    <div class="modal fade" id="produtoModal" tabindex="-1" aria-labelledby="produtoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                    <h5 class="modal-title" id="produtoModalLabel">Detalhes do Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <h4 id="produto-nome"></h4>
                            <p id="produto-descricao" class="text-muted"></p>
                            <h5 id="produto-preco" class="text-primary"></h5>
                        </div>
                </div>
                <!-- Seleção de tamanho para XIS -->
                <div class="row mb-3" id="tamanho-xis-row" style="display:none;">
                    <div class="col-md-6">
                        <label class="form-label">Size:</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tamanho-xis" id="xis-normal" value="normal" checked>
                                <label class="form-check-label" for="xis-normal">Normal</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="tamanho-xis" id="xis-mini" value="mini">
                                <label class="form-check-label" for="xis-mini">Mini</label>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="quantidade" class="form-label">Quantidade:</label>
                            <input type="number" class="form-control" id="quantidade" value="1" min="1">
                        </div>
                </div>
                
                <div class="mb-3">
                        <h5>Ingredientes</h5>
                        <div id="ingredientes-container">
                            <!-- Será preenchido via JavaScript -->
                    </div>
                        <input type="hidden" id="ingredientes-selecionados" value="">
                </div>

                <div class="mb-3">
                        <label for="observacoes" class="form-label">Observações:</label>
                        <textarea class="form-control" id="observacoes" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="addToCart()">
                        Adicionar ao Pedido
                </button>
            </div>
        </div>
    </div>
</div>

    <!-- JavaScript Files -->
    <script src="<?php echo $config->url("MVC/COMMON/VENDOR/jquery/jquery.min.js"); ?>"></script>
    <script src="<?php echo $config->url("MVC/COMMON/VENDOR/bootstrap/js/bootstrap.bundle.min.js"); ?>"></script>
    <script src="<?php echo $config->url("MVC/COMMON/VENDOR/jquery-easing/jquery.easing.min.js"); ?>"></script>
    <script src="<?php echo $config->url("MVC/COMMON/JS/sb-admin-2.min.js"); ?>"></script>
    <script src="<?php echo $config->url("MVC/COMMON/VENDOR/chart.js/Chart.min.js"); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
<script>
        // Initialize tooltips
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });

        // Utility function - Debounce
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    func.apply(this, args);
                }, wait);
            };
        }

        // Variáveis globais
        let mesaId = <?php echo json_encode($mesa_id); ?>;
        let cart = [];
        let currentProduct = null;
        let buscando = false;
        let selectedCategory = null;
        let isLoading = false;

        // Load products from server
        function loadProducts(search = '', category = null) {
            if (isLoading) {
                console.log('Já existe uma busca em andamento');
                return;
            }
            
            isLoading = true;
            $('#loading').show();
            $('#productsGrid').empty();

            // Garantir que search seja uma string
            search = String(search || '');
            console.log('Iniciando busca com termo:', search, 'categoria:', category);

            const requestData = {
                query: search,
                categoria: category
            };

            console.log('Enviando requisição:', requestData);
    
    $.ajax({
                url: '<?php echo $config->url("MVC/MODEL/buscar_produtos.php"); ?>',
                method: 'POST',
                data: JSON.stringify(requestData),
                contentType: 'application/json',
                dataType: 'json',
        success: function(response) {
                    console.log('Resposta da busca:', response);
                    
                    const container = $('#productsGrid');
                    
                    if (response && response.success) {
                        if (!response.produtos || response.produtos.length === 0) {
                            container.html(`
                                <div class="col-12 text-center">
                                    <p>Nenhum produto encontrado para: "${search}"</p>
                                </div>
                            `);
                            return;
                        }
                        
                        response.produtos.forEach(produto => {
                            if (!produto) {
                                console.error('Produto inválido na resposta');
                                return;
                            }
                            
                            try {
                                const produtoJson = JSON.stringify(produto).replace(/"/g, '&quot;');
                                const card = `
                                    <div class="col-md-4 mb-3">
                                        <div class="card product-card">
                                            <div class="card-body">
                                                <h5 class="card-title">${produto.nome || 'Sem nome'}</h5>
                                                <p class="card-text text-muted">${produto.codigo || 'Sem código'}</p>
                                                <p class="card-text">${produto.descricao || 'Sem descrição'}</p>
                                                <p class="card-text"><strong>R$ ${parseFloat(produto.preco_normal || 0).toFixed(2).replace('.', ',')}</strong></p>
                                                <div class="d-flex gap-2">
                                                    <button class="btn btn-primary flex-grow-1" onclick='showProductModal(${produtoJson})'>
                                                        Adicionar ao Pedido
                                                    </button>
                                                    <button class="btn btn-success" 
                                                            onclick='quickAddToCart(${produtoJson})' 
                                                            data-bs-toggle="tooltip" 
                                                            data-bs-placement="top" 
                                                            title="Adicionar rapidamente (sem personalizações)">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                container.append(card);
                            } catch (err) {
                                console.error('Erro ao processar produto:', err, produto);
                            }
                        });
                    } else {
                        container.html(`
                            <div class="col-12 text-center">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    ${response?.message || 'Erro ao carregar produtos'}
                                </div>
                            </div>
                        `);
            }
        },
        error: function(xhr, status, error) {
                    console.error('Erro na busca:', {xhr, status, error});
                    let errorMessage = 'Erro ao carregar produtos';
                    
                    try {
                        if (xhr.responseText) {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.message) {
                                errorMessage = response.message;
                            }
                        }
                    } catch (e) {
                        console.error('Erro ao parsear resposta:', e);
                    }
                    
                    $('#productsGrid').html(`
                        <div class="col-12 text-center">
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                ${errorMessage}
                            </div>
                        </div>
                    `);
                },
                complete: function() {
                    isLoading = false;
                    $('#loading').hide();
                    console.log('Busca finalizada');
                }
            });
        }

        // Load categories from server
        function loadCategories() {
            console.log('Carregando categorias...');
        $.ajax({
                url: '<?php echo $config->url("MVC/MODEL/buscar_categorias.php"); ?>',
            method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                    console.log('Resposta categorias:', response);
                    if (response.success) {
                        const categoriesHtml = `
                            <div class="category-item active" data-id="todos">Todos</div>
                            ${response.categories.map(category => `
                                <div class="category-item" data-id="${category.id}">
                                    ${category.nome}
                                </div>
                            `).join('')}
                        `;
                        
                        $('#categories').html(categoriesHtml);

                        // Adicionar event listeners para as categorias
                        $('.category-item').click(function() {
                            $('.category-item').removeClass('active');
                            $(this).addClass('active');
                            const categoryId = $(this).data('id');
                            selectedCategory = categoryId === 'todos' ? null : parseInt(categoryId);
                            loadProducts($('#searchInput').val().trim(), selectedCategory);
                        });
                } else {
                        console.error('Erro na resposta:', response);
                        showError('Erro ao carregar categorias');
                }
            },
            error: function(xhr, status, error) {
                    console.error('Erro ao carregar categorias:', error);
                    showError('Erro ao carregar categorias');
                }
            });
        }

        // Event listeners
        $(document).ready(function() {
            // Inicialização
            if (!mesaId) {
                showError('Selecione uma mesa primeiro');
                return;
            }

            // Carregar dados iniciais
            loadCategories();
            loadProducts();
            loadCart(mesaId);

            // Event listener para busca com debounce
            const debouncedSearch = debounce(function(searchTerm) {
                console.log('Executando busca com termo:', searchTerm);
                loadProducts(searchTerm, selectedCategory);
            }, 300);

            $('#searchInput').on('input', function() {
                const searchTerm = $(this).val() || '';
                console.log('Termo digitado:', searchTerm);
                debouncedSearch(searchTerm.trim());
            });

            // Event listeners para quantidade
            $('#decreaseQuantity').click(function() {
                updateQuantity(-1);
            });

            $('#increaseQuantity').click(function() {
                updateQuantity(1);
            });

            // Event listener para adicionar ao carrinho
            $('#addToCart').click(function() {
                addToCart();
            });

            // Event listener para finalizar pedido
            $('#finishOrder').click(function() {
                finalizarPedido();
            });

            // Event listener para toggle do carrinho
            $('#cartToggle').click(function() {
                $('#cartContainer').toggleClass('active');
            });
        });

        // Load cart from server
        function loadCart(mesaId) {
        $.ajax({
                url: '<?php echo $config->url("MVC/MODEL/carrinho.php"); ?>',
            method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
            },
                data: { mesa_id: mesaId },
            success: function(response) {
                    if (response.success) {
                        cart = response.carrinho || [];
                        updateCartUI();
                    }
                },
                error: function(error) {
                    console.error('Erro ao carregar carrinho:', error);
                }
            });
        }

        // Show product modal
        function showProductModal(product) {
            if (!product) {
                console.error('Produto inválido');
                return;
            }

            try {
                currentProduct = product;
                produtoSelecionado = product;
                
                // Preencher dados do produto no modal
                $('#produto-nome').text(product.nome || '');
                $('#produto-descricao').text(product.descricao || '');
                $('#produto-preco').text('R$ ' + formatMoney(product.preco_normal || 0));
                $('#quantidade').val(1);
                $('#observacoes').val('');
                // Exibir seleção de tamanho se for XIS e tiver preco_mini > 0
                if ((product.categoria && product.categoria.toUpperCase() === 'XIS') && parseFloat(product.preco_mini) > 0) {
                    $('#tamanho-xis-row').show();
                    $('#xis-normal').prop('checked', true);
                } else {
                    $('#tamanho-xis-row').hide();
                }
                
                // Limpar e mostrar loading nos ingredientes
                $('#ingredientes-container').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando ingredientes...</div>');
                
                // Carregar ingredientes
                if (product.id) {
    $.ajax({
                        url: '<?php echo $config->url("MVC/MODEL/buscar_ingredientes.php"); ?>',
        method: 'GET',
                        data: { produto_id: product.id },
                        dataType: 'json',
        success: function(response) {
                            console.log('Resposta ingredientes:', response);
                            
                            if (response.success && response.ingredients && response.ingredients.length > 0) {
                                // Separar ingredientes em padrão e adicionais
                                const ingredientesPadrao = response.ingredients.filter(i => i.padrao);
                                const ingredientesAdicionais = response.ingredients.filter(i => !i.padrao);
                                
                                let html = '<div class="row">';
                                
                                // Seção de ingredientes padrão
                                html += '<div class="col-12 mb-3">';
                                html += '<h6 class="text-danger mb-2">Ingredientes do produto (clique para remover):</h6>';
                                ingredientesPadrao.forEach(function(ingrediente) {
                                    html += `
                                        <div class="ingredient-toggle mb-2" 
                                            data-id="${ingrediente.id}"
                                            data-nome="${ingrediente.nome}"
                                            data-padrao="true"
                                            data-preco="${ingrediente.preco_adicional}"
                                            onclick="toggleIngredienteClick(this)">
                                            ${ingrediente.nome}
                                        </div>`;
                                });
                                html += '</div>';
                                
                                // Seção de ingredientes adicionais
                                if (ingredientesAdicionais.length > 0) {
                                    html += '<div class="col-12">';
                                    html += '<h6 class="text-success mb-2">Ingredientes adicionais (clique para adicionar):</h6>';
                                    ingredientesAdicionais.forEach(function(ingrediente) {
                                        html += `
                                            <div class="ingredient-toggle mb-2" 
                                                data-id="${ingrediente.id}"
                                                data-nome="${ingrediente.nome}"
                                                data-padrao="false"
                                                data-preco="${ingrediente.preco_adicional}"
                                                onclick="toggleIngredienteClick(this)">
                                                ${ingrediente.nome}
                                                ${ingrediente.preco_adicional > 0 ? ` (+R$ ${ingrediente.preco_adicional.toFixed(2)})` : ''}
                                            </div>`;
                                    });
                                    html += '</div>';
                                }
                                
                                html += '</div>';
                                $('#ingredientes-container').html(html);
                            } else {
                                $('#ingredientes-container').html('<p class="text-muted">Nenhum ingrediente disponível para este produto</p>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erro ao carregar ingredientes:', error);
                            console.error('Status:', status);
                            console.error('Response:', xhr.responseText);
                            $('#ingredientes-container').html('<p class="text-danger">Erro ao carregar ingredientes. Por favor, tente novamente.</p>');
                        }
                    });
                } else {
                    $('#ingredientes-container').html('<p class="text-muted">Nenhum ingrediente disponível para este produto</p>');
                }
                
                // Mostrar o modal
                $('#produtoModal').modal('show');
                
            } catch (error) {
                console.error('Erro ao mostrar modal:', error);
                Swal.fire('Erro', 'Erro ao exibir detalhes do produto', 'error');
            }
        }

        function toggleIngredienteClick(element) {
            const $element = $(element);
            const isPadrao = $element.data('padrao') === true;
            
            if ($element.hasClass('excluded')) {
                $element.removeClass('excluded included');
            } else if ($element.hasClass('included')) {
                $element.removeClass('included').addClass('excluded');
            } else {
                $element.addClass(isPadrao ? 'excluded' : 'included');
            }
        }

        function getSelectedIngredients() {
            const ingredientes = [];
            
            $('.ingredient-toggle').each(function() {
                const $this = $(this);
                if ($this.hasClass('excluded') || $this.hasClass('included')) {
                    ingredientes.push({
                        id: $this.data('id'),
                        nome: $this.data('nome'),
                        tipo: $this.hasClass('excluded') ? 'sem' : 'com',
                        preco_adicional: parseFloat($this.data('preco') || 0)
                    });
                }
            });
            
            return ingredientes;
        }

        function addToCart() {
            if (!mesaId) {
                showError('Selecione uma mesa primeiro');
        return;
    }

            if (!produtoSelecionado) {
                showError('Produto não selecionado');
        return;
    }

            const quantidade = parseInt($('#quantidade').val());
            if (isNaN(quantidade) || quantidade < 1) {
                showError('Quantidade inválida');
        return;
    }

            // Obter ingredientes selecionados
            const ingredientes = [];
            $('.ingredient-toggle').each(function() {
                const $this = $(this);
                if ($this.hasClass('excluded') || $this.hasClass('included')) {
                    ingredientes.push({
                        id: $this.data('id'),
                        nome: $this.data('nome'),
                        tipo: $this.hasClass('excluded') ? 'sem' : 'com',
                        preco_adicional: parseFloat($this.data('preco') || 0)
                    });
                }
            });

            // --- NOVO: lógica de tamanho ---
            let tamanho = 'normal';
            let nomeProduto = produtoSelecionado.nome;
            let precoTotal = parseFloat(produtoSelecionado.preco_normal);
            if ((produtoSelecionado.categoria && produtoSelecionado.categoria.toUpperCase() === 'XIS') && parseFloat(produtoSelecionado.preco_mini) > 0) {
                tamanho = $("input[name='tamanho-xis']:checked").val();
                if (tamanho === 'mini') {
                    nomeProduto = 'Mini ' + nomeProduto;
                    precoTotal = parseFloat(produtoSelecionado.preco_mini);
                }
            }
            // Adicionais
            ingredientes.forEach(ing => {
                if (ing.tipo === 'com' && ing.preco_adicional) {
                    precoTotal += parseFloat(ing.preco_adicional);
                }
            });

            const observacao = $('#observacoes').val().trim();

            const cartItem = {
                mesa_id: mesaId,
                produto: {
                    id: produtoSelecionado.id,
                    nome: nomeProduto,
                    preco: precoTotal
                },
        quantidade: quantidade,
                ingredientes: ingredientes,
                observacao: observacao,
                valor_total: precoTotal * quantidade,
                tamanho: tamanho
            };

            console.log('Enviando item para o carrinho:', cartItem);

    $.ajax({
                url: '<?php echo $config->url("MVC/MODEL/carrinho.php"); ?>',
        method: 'POST',
                data: JSON.stringify(cartItem),
        contentType: 'application/json',
        success: function(response) {
            console.log('Resposta do servidor:', response);
            if (response.success) {
                        cart = response.carrinho || [];
                        updateCartUI();
                        $('#produtoModal').modal('hide');
                        showSuccess('Item adicionado ao pedido');
                        
                        // Limpar seleções
                        $('#quantidade').val(1);
                        $('#observacoes').val('');
                        $('.ingredient-toggle').removeClass('excluded included');
            } else {
                        showError(response.message || 'Erro ao adicionar item ao carrinho');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao adicionar item:', {xhr, status, error});
                    showError('Erro ao adicionar item ao carrinho');
        }
    });
}

        function updateCartUI() {
            const cartContainer = $('#cartItems');
            cartContainer.empty();
            let total = 0;

            console.log('Atualizando carrinho:', cart);

            if (!cart || cart.length === 0) {
                cartContainer.html('<p class="text-center text-muted">Carrinho vazio</p>');
                $('#finishOrder').prop('disabled', true);
                $('#cartTotal').text('0,00');
                $('#cartCount').text('0');
        return;
    }

            cart.forEach((item, index) => {
                    console.log('Processando item:', item);
                const itemTotal = item.valor_total || (item.produto.preco * item.quantidade);
                total += itemTotal;

                // Formatar ingredientes
                let ingredientesHtml = '';
                if (item.ingredientes && Array.isArray(item.ingredientes)) {
                    console.log('Ingredientes do item:', item.ingredientes);
                    const removidos = item.ingredientes.filter(i => i.tipo === 'sem').map(i => i.nome);
                    const adicionados = item.ingredientes.filter(i => i.tipo === 'com').map(i => i.nome);
                        
                        if (removidos.length > 0) {
                        ingredientesHtml += `<div class="text-danger small mt-1">Sem: ${removidos.join(', ')}</div>`;
                        }
                        if (adicionados.length > 0) {
                        ingredientesHtml += `<div class="text-success small mt-1">Com: ${adicionados.join(', ')}</div>`;
                    }
                }

                const observacaoHtml = item.observacao ? `<div class="text-muted small mt-1"><i class="fas fa-comment-alt"></i> ${item.observacao}</div>` : '';

                let nomeProduto = item.produto.nome;
                if (item.tamanho === 'mini' && !nomeProduto.toLowerCase().startsWith('mini ')) {
                    nomeProduto = 'Mini ' + nomeProduto;
                }

                const itemHtml = `
                    <div class="cart-item">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="d-flex align-items-center">
                                <span class="me-2">${item.quantidade}x</span>
                                <span>${nomeProduto}</span>
                            </div>
                            <div>R$ ${formatMoney(itemTotal)}</div>
                        </div>
                        ${ingredientesHtml}
                        ${observacaoHtml}
                        <button class="btn btn-sm text-danger p-0 float-end" onclick="removeFromCart(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                `;

                cartContainer.append(itemHtml);
            });

            $('#cartTotal').text(formatMoney(total));
            $('#cartCount').text(cart.length);
            $('#finishOrder').prop('disabled', false);
        }

        // Remove item from cart
        function removeFromCart(index) {
            if (index >= 0 && index < cart.length) {
                // Atualizar carrinho no servidor
                $.ajax({
                    url: '<?php echo $config->url("MVC/MODEL/carrinho.php"); ?>',
                    method: 'DELETE',
                    data: JSON.stringify({
                        mesa_id: mesaId,
                        index: index
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            cart = response.carrinho || [];
                            updateCartUI();
                            showSuccess('Item removido do carrinho');
                        } else {
                            showError('Erro ao remover item: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
                        console.error('Erro ao remover item:', {xhr, status, error});
                        showError('Erro ao remover item do carrinho');
        }
    });
            }
}

        // Finish order
function finalizarPedido() {
            if (!mesaId) {
                showError('Mesa não selecionada');
        return;
    }

            if (!cart || cart.length === 0) {
                showError('Adicione itens ao pedido');
                return;
            }

            const shouldPrint = $('#printReceipt').is(':checked');
            const data = {
                mesa_id: mesaId,
                items: cart,
                print_receipt: shouldPrint
            };

            // Log para debug
            console.log('Dados do pedido:', data);

    $.ajax({
                url: '<?php echo $config->url("MVC/MODEL/finalizar_pedido.php"); ?>',
        method: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
                    console.log('Resposta do servidor:', response);
                    if (response && response.success) {
                        if (shouldPrint && response.pedido_id) {
                            // Abrir janela de impressão em uma nova aba
                            window.open(`<?php echo $config->url("MVC/VIEWS/imprimir_pedido.php"); ?>?pedido_id=${response.pedido_id}`, '_blank');
                        }
                        Swal.fire({
                            title: 'Sucesso!',
                            text: 'Pedido finalizado com sucesso',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?php echo $config->url("?view=Dashboard1"); ?>';
                            }
                        });
                    } else {
                        const errorMsg = response && response.message ? response.message : 'Erro ao finalizar pedido';
                        Swal.fire('Erro', errorMsg, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Erro ao finalizar pedido:', {xhr, status, error});
                    let errorMsg = 'Erro ao finalizar pedido';
                    
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response && response.message) {
                            errorMsg = response.message;
                        }
                    } catch (e) {
                        console.error('Erro ao parsear resposta:', e);
                    }
                    
                    Swal.fire('Erro', errorMsg, 'error');
                }
            });
        }

        // Utility functions
        function formatMoney(value) {
            return parseFloat(value).toFixed(2).replace('.', ',');
        }

        function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Sucesso',
        text: message,
        timer: 2000,
        showConfirmButton: false
    });
}

        function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Erro',
        text: message
    });
}

// Update the quickAddToCart function
function quickAddToCart(product) {
    if (!product) {
        showError('Produto não selecionado');
        return;
    }

    if (!mesaId) {
        showError('Selecione uma mesa primeiro');
        return;
    }

    // Check if product has required ingredients
    if (product.ingredientes_obrigatorios && product.ingredientes_obrigatorios.length > 0) {
        Swal.fire({
            title: 'Personalização Necessária',
            text: 'Este produto requer personalização. Por favor, use o botão "Adicionar ao Pedido" para selecionar os ingredientes.',
            icon: 'info',
            confirmButtonText: 'OK'
        });
        return;
    }

    const cartItem = {
        mesa_id: mesaId,
        produto: {
            id: product.id,
            nome: product.nome,
            preco: parseFloat(product.preco_normal)
        },
        quantidade: 1,
        ingredientes: [],
        observacao: '',
        valor_total: parseFloat(product.preco_normal)
    };

    // Add animation to the clicked button
    const button = event.currentTarget;
    button.classList.add('add-to-cart-animation');
    setTimeout(() => button.classList.remove('add-to-cart-animation'), 500);

    $.ajax({
        url: '<?php echo $config->url("MVC/MODEL/carrinho.php"); ?>',
        method: 'POST',
        data: JSON.stringify(cartItem),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                cart = response.carrinho || [];
                updateCartUI();
                
                // Add animation to cart count
                const cartCount = $('#cartCount');
                cartCount.addClass('cart-count-animation');
                setTimeout(() => cartCount.removeClass('cart-count-animation'), 500);
                
                // Show quick confirmation toast
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });

                Toast.fire({
                    icon: 'success',
                    title: `${product.nome} adicionado ao pedido`
                });
            } else {
                showError(response.message || 'Erro ao adicionar item ao carrinho');
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao adicionar item:', {xhr, status, error});
            showError('Erro ao adicionar item ao carrinho');
        }
    });
}
</script>
</body>
</html> 