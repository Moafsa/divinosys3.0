<?php
// Incluir o arquivo de conexão
include_once(__DIR__ . "/include_conexao.php");

   $categoria = $_POST['categoria'];
   $pesquisa = $_POST['pesquisa'];
   $mesa = $_POST['mesa'];
   $cliente = $_POST['cliente'];
   ?>

<div class="row">
	<h3 class="col-lg-8" style="height: 80px; color: #4D4D4D;">Categoria "<?php echo htmlspecialchars($categoria); ?>"</h3>

	<form method="POST" id="" action="" class="mb-10 text-center">
		<input type="text" name="pesquisa" id="pesquisa" placeholder="Digite o nome do produto">
		<label type="hidden" style="width: 10px;"></label>
		<input class="btn btn-outline-warning" type="submit" name="enviar" value="Pesquisar">
		<input type="hidden" name="categoria" id="categoria" value="<?php echo htmlspecialchars($categoria); ?>">
		<input type="hidden" name="mesa" id="mesa" value="<?php echo htmlspecialchars($mesa); ?>">
		<input type="hidden" name="cliente" id="cliente" value="<?php echo htmlspecialchars($cliente); ?>">
	</form>
</div>

<?php
   include_once "MVC/MODEL/conexao.php";

try {
   if ($pesquisa == ' ') {
		// Buscar produtos da categoria
		$stmt = mysqli_prepare($conn, "
			SELECT p.*, c.nome as categoria_nome 
			FROM produtos p 
			JOIN categorias c ON p.categoria_id = c.id 
			WHERE c.nome = ?
		");
		mysqli_stmt_bind_param($stmt, 's', $categoria);
	} else {
		// Buscar produtos por pesquisa
		$stmt = mysqli_prepare($conn, "
			SELECT p.*, c.nome as categoria_nome 
			FROM produtos p 
			JOIN categorias c ON p.categoria_id = c.id 
			WHERE c.nome = ? AND (p.nome LIKE ? OR p.codigo = ?)
		");
		$pesquisa_like = "%$pesquisa%";
		mysqli_stmt_bind_param($stmt, 'sss', $categoria, $pesquisa_like, $pesquisa);
	}

	mysqli_stmt_execute($stmt);
	$produtos = mysqli_stmt_get_result($stmt);

	if (mysqli_num_rows($produtos) <= 0) {
		?>
	<div class="container-fluid">
		<table class="table table-striped table-sm">
			<thead>
				<tr>
						<th>Código</th>
						<th>Nome</th>
						<th>Categoria</th>
						<th>Preço Normal</th>
						<th>Preço Mini</th>
						<th>Ação</th>
				</tr>
			</thead>
			<tbody>
					<tr>
						<td>-</td>
						<td>"Nenhum produto encontrado..."</td>
						<td>-</td>
						<td>-</td>
						<td>-</td>
						<td>
							<form method="POST" action="" class="mb-10 text-center">
		<input class="btn btn-outline-danger" type="submit" name="enviar" value="Voltar">
								<input type="hidden" name="categoria" value="<?php echo htmlspecialchars($categoria); ?>">
								<input type="hidden" name="mesa" value="<?php echo htmlspecialchars($mesa); ?>">
								<input type="hidden" name="pesquisa" value=" ">
								<input type="hidden" name="cliente" value="<?php echo htmlspecialchars($cliente); ?>">
		</form>
						</td>
					</tr>
			</tbody>
		</table>
	</div>
	<?php
	} else {
?>
	<div class="container-fluid">
		<table class="table table-striped table-sm">
			<thead>
				<tr>
						<th>Código</th>
						<th>Nome</th>
						<th>Categoria</th>
						<th>Preço Normal</th>
						<th>Preço Mini</th>
						<th>Adicionar</th>
				</tr>
			</thead>
			<tbody>
					<?php while($produto = mysqli_fetch_assoc($produtos)) { ?>
						<tr>
							<td><?php echo htmlspecialchars($produto['codigo']); ?></td>
							<td style="color: #4D4D4D;"><b><?php echo htmlspecialchars($produto['nome']); ?></b></td>
							<td><?php echo htmlspecialchars($produto['categoria_nome']); ?></td>
							<td>R$ <?php echo number_format($produto['preco_normal'], 2, ',', '.'); ?></td>
							<td><?php echo $produto['preco_mini'] ? 'R$ ' . number_format($produto['preco_mini'], 2, ',', '.') : '-'; ?></td>
							<td>
								<button type="button" class="btn btn-info btn-icon-split btn-sm" 
									data-idcliente="<?php echo htmlspecialchars($cliente); ?>" 
									data-idnome="<?php echo htmlspecialchars($produto['nome']); ?>" 
									data-idmesa="<?php echo htmlspecialchars($mesa); ?>" 
									data-idpreco="<?php echo $produto['preco_normal']; ?>"
									data-idpreco-mini="<?php echo $produto['preco_mini']; ?>"
									data-toggle="modal" 
									data-target="#adiciona">
									Selecionar
								</button>
							</td>
				</tr>
					<?php } ?>
			</tbody>
		</table>
	</div>
<?php 
	}
} catch (Exception $e) {
	error_log("Erro ao buscar produtos: " . $e->getMessage());
	echo "<div class='alert alert-danger'>Erro ao buscar produtos. Por favor, tente novamente.</div>";
}
?>

<!-- Modal Novo Pedido -->
<div class="modal fade" id="modalNovoPedido" tabindex="-1" role="dialog" aria-labelledby="modalNovoPedidoLabel">
    <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
                <h4 class="modal-title" id="modalNovoPedidoLabel">Adicionar Pedido</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formNovoPedido" method="POST" action="MVC/MODEL/adicionar_pedido.php">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="searchProduto">Buscar Produto (nome ou código)</label>
                                <input type="text" class="form-control" id="searchProduto" placeholder="Digite para buscar...">
                            </div>
	    	</div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="filterCategoria">Filtrar por Categoria</label>
                                <select class="form-control" id="filterCategoria">
                                    <option value="">Todas as Categorias</option>
                                    <option value="XIS">XIS</option>
                                    <option value="Cachorro-Quente">Cachorro-Quente</option>
                                    <option value="Bauru">Bauru</option>
                                    <option value="PF e A La Minuta">PF e A La Minuta</option>
                                    <option value="Torrada">Torrada</option>
                                    <option value="Rodízio">Rodízio</option>
                                    <option value="Porções">Porções</option>
                                    <option value="Bebidas">Bebidas</option>
                                    <option value="Bebidas Alcoólicas">Bebidas Alcoólicas</option>
                                </select>
						</div>
						</div>
						</div>

                    <div id="searchResults" class="list-group mb-3" style="max-height: 200px; overflow-y: auto; display: none;">
                        <!-- Results will be populated here -->
						</div>
				          				          			
                    <div id="selectedProduct" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Produto Selecionado</label>
                                    <h5 id="selectedProductName"></h5>
                                    <p id="selectedProductDesc" class="text-muted"></p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="priceType">Tamanho</label>
                                    <select class="form-control" id="priceType" name="priceType">
                                        <option value="normal">Normal</option>
                                        <option value="mini">Mini</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="quantity">Quantidade</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" value="1" min="1">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="observacoes">Observações</label>
                                    <textarea class="form-control" id="observacoes" name="observacoes" rows="2"></textarea>
                                </div>
                            </div>
						</div>	
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <h4>Total: R$ <span id="totalPrice">0.00</span></h4>
						</div>	
					</div>
				</div>

                    <input type="hidden" id="productId" name="productId">
                    <input type="hidden" id="tableId" name="tableId">
                    <input type="hidden" id="clientId" name="clientId">
			</form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnAddToOrder" disabled>Adicionar ao Pedido</button>
            </div>
	    </div>
	</div>
</div>

<script>
// Utility function to format price
function formatPrice(price) {
    return parseFloat(price).toFixed(2);
}

// Function to update total price
function updateTotalPrice() {
    const product = $('#selectedProduct').data('product');
    if (!product) return;

    const quantity = parseInt($('#quantity').val()) || 1;
    const priceType = $('#priceType').val();
    const price = priceType === 'normal' ? product.preco_normal : product.preco_mini;
    const total = price * quantity;

    $('#totalPrice').text(formatPrice(total));
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Function to search products
const searchProducts = debounce(function() {
    const searchTerm = $('#searchProduto').val().trim();
    const categoria = $('#filterCategoria').val();

    if (searchTerm.length < 2 && !categoria) {
        $('#searchResults').hide();
        return;
    }

    $.ajax({
        url: '<?php echo api_url("buscar_produto.php"); ?>',
        method: 'GET',
        data: {
            termo: searchTerm,
            categoria: categoria
        },
        success: function(response) {
            if (response.success && response.data.length > 0) {
                const resultsHtml = response.data.map(product => `
                    <a href="#" class="list-group-item list-group-item-action product-item" 
                       data-product='${JSON.stringify(product)}'>
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">${product.nome}</h5>
                            <small>R$ ${formatPrice(product.preco_normal)}</small>
                        </div>
                        <p class="mb-1">${product.descricao || ''}</p>
                        <small>${product.categoria}</small>
                    </a>
                `).join('');

                $('#searchResults')
                    .html(resultsHtml)
                    .show();
            } else {
                $('#searchResults')
                    .html('<div class="list-group-item">Nenhum produto encontrado</div>')
                    .show();
            }
        },
        error: function() {
            $('#searchResults')
                .html('<div class="list-group-item text-danger">Erro ao buscar produtos</div>')
                .show();
        }
    });
}, 300);

// Event listeners
$('#searchProduto, #filterCategoria').on('input change', searchProducts);

$(document).on('click', '.product-item', function(e) {
    e.preventDefault();
    const product = $(this).data('product');
    
    // Store product data
    $('#selectedProduct').data('product', product);
    
    // Update UI
    $('#selectedProductName').text(product.nome);
    $('#selectedProductDesc').text(product.descricao || '');
    $('#productId').val(product.id);
    
    // Show/hide elements
    $('#searchResults').hide();
    $('#selectedProduct').show();
    $('#btnAddToOrder').prop('disabled', false);
    
    // Update price
    updateTotalPrice();
});

$('#quantity, #priceType').on('change', updateTotalPrice);

$('#btnAddToOrder').on('click', function() {
    const product = $('#selectedProduct').data('product');
    if (!product) return;

    const formData = {
        productId: product.id,
        tableId: $('#tableId').val(),
        clientId: $('#clientId').val(),
        quantity: $('#quantity').val(),
        priceType: $('#priceType').val(),
        observacoes: $('#observacoes').val()
    };

    $.ajax({
        url: '<?php echo api_url("adicionar_pedido.php"); ?>',
        method: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                // Close modal and refresh orders
                $('#modalNovoPedido').modal('hide');
                // You might want to trigger a refresh of the orders list here
                if (typeof refreshOrders === 'function') {
                    refreshOrders();
                }
            } else {
                alert('Erro ao adicionar pedido: ' + response.message);
            }
        },
        error: function() {
            alert('Erro ao processar pedido. Por favor, tente novamente.');
        }
    });
});

// Reset modal when closed
$('#modalNovoPedido').on('hidden.bs.modal', function() {
    $('#formNovoPedido')[0].reset();
    $('#searchResults').hide();
    $('#selectedProduct').hide().removeData('product');
    $('#btnAddToOrder').prop('disabled', true);
});

// Initialize modal with data
$('#modalNovoPedido').on('show.bs.modal', function(event) {
    const button = $(event.relatedTarget);
    const tableId = button.data('table-id');
    const clientId = button.data('client-id');
    
    $('#tableId').val(tableId);
    $('#clientId').val(clientId);
});
</script>