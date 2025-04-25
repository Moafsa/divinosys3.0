<?php
require_once 'MVC/COMMON/header.php';
require_once 'MVC/MODEL/conexao.php';

// Get all products with their categories and stock info
$sql = "SELECT p.*, c.nome as categoria_nome, e.estoque_atual, e.estoque_minimo, e.preco_custo, e.marca 
        FROM produtos p 
        LEFT JOIN categorias c ON p.categoria_id = c.id 
        LEFT JOIN estoque e ON p.id = e.produto_id 
        ORDER BY p.nome";
$result = mysqli_query($conn, $sql);
$produtos = array();
while ($row = mysqli_fetch_assoc($result)) {
    $produtos[] = $row;
}

// Get all categories for the dropdown
$sql_categorias = "SELECT id, nome FROM categorias ORDER BY nome";
$result_cat = mysqli_query($conn, $sql_categorias);
$categorias = array();
while ($row = mysqli_fetch_assoc($result_cat)) {
    $categorias[] = $row;
}

// Helper function to safely escape HTML
function escape($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gerenciar Produtos</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">
            <i class="fas fa-plus"></i> Adicionar Produto
        </button>
    </div>

    <?php
    if (isset($_SESSION['msg'])) {
        echo $_SESSION['msg'];
        unset($_SESSION['msg']);
    }
    ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Imagem</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Estoque Mín.</th>
                            <th>Custo</th>
                            <th>Marca</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produtos as $produto): ?>
                            <tr>
                                <td class="text-center">
                                    <?php if ($produto['imagem']): ?>
                                        <img src="uploads/produtos/<?php echo escape($produto['imagem']); ?>" 
                                             alt="<?php echo escape($produto['nome']); ?>" 
                                             class="img-thumbnail" 
                                             style="max-width: 50px;">
                                    <?php else: ?>
                                        <img src="assets/img/no-image.php" 
                                             alt="Sem imagem" 
                                             class="img-thumbnail" 
                                             style="max-width: 50px;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo escape($produto['nome']); ?></td>
                                <td><?php echo escape($produto['categoria_nome']); ?></td>
                                <td>R$ <?php echo number_format((float)$produto['preco_normal'], 2, ',', '.'); ?></td>
                                <td><?php echo (int)$produto['estoque_atual']; ?></td>
                                <td><?php echo (int)$produto['estoque_minimo']; ?></td>
                                <td>R$ <?php echo number_format((float)$produto['preco_custo'], 2, ',', '.'); ?></td>
                                <td><?php echo escape($produto['marca']); ?></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info edit-product" 
                                            data-id="<?php echo (int)$produto['id']; ?>"
                                            data-nome="<?php echo escape($produto['nome']); ?>"
                                            data-categoria="<?php echo (int)$produto['categoria_id']; ?>"
                                            data-preco="<?php echo (float)$produto['preco_normal']; ?>"
                                            data-descricao="<?php echo escape($produto['descricao']); ?>"
                                            data-estoque="<?php echo (int)$produto['estoque_atual']; ?>"
                                            data-estoque-minimo="<?php echo (int)$produto['estoque_minimo']; ?>"
                                            data-custo="<?php echo (float)$produto['preco_custo']; ?>"
                                            data-marca="<?php echo escape($produto['marca']); ?>"
                                            data-toggle="modal" 
                                            data-target="#editProductModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-product" 
                                            data-id="<?php echo (int)$produto['id']; ?>"
                                            data-nome="<?php echo escape($produto['nome']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar Produto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addProductForm" action="MVC/MODEL/salvar_produto.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control" name="nome" required>
                            </div>
                            <div class="form-group">
                                <label>Categoria</label>
                                <select class="form-control" name="categoria_id" required>
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo (int)$categoria['id']; ?>">
                                            <?php echo escape($categoria['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Preço de Venda</label>
                                <input type="number" step="0.01" class="form-control" name="preco_normal" required>
                            </div>
                            <div class="form-group">
                                <label>Descrição</label>
                                <textarea class="form-control" name="descricao" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Imagem do Produto</label>
                                <input type="file" class="form-control" name="imagem" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Estoque Atual</label>
                                <input type="number" class="form-control" name="estoque_atual" value="0">
                            </div>
                            <div class="form-group">
                                <label>Estoque Mínimo</label>
                                <input type="number" class="form-control" name="estoque_minimo" value="0">
                            </div>
                            <div class="form-group">
                                <label>Preço de Custo</label>
                                <input type="number" step="0.01" class="form-control" name="preco_custo">
                            </div>
                            <div class="form-group">
                                <label>Marca</label>
                                <input type="text" class="form-control" name="marca">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Produto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editProductForm" action="MVC/MODEL/atualizar_produto.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Nome</label>
                                <input type="text" class="form-control" name="nome" id="edit_nome" required>
                            </div>
                            <div class="form-group">
                                <label>Categoria</label>
                                <select class="form-control" name="categoria_id" id="edit_categoria" required>
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo (int)$categoria['id']; ?>">
                                            <?php echo escape($categoria['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Preço de Venda</label>
                                <input type="number" step="0.01" class="form-control" name="preco_normal" id="edit_preco" required>
                            </div>
                            <div class="form-group">
                                <label>Descrição</label>
                                <textarea class="form-control" name="descricao" id="edit_descricao" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Imagem do Produto</label>
                                <input type="file" class="form-control" name="imagem" accept="image/*">
                                <div id="current_image" class="mt-2"></div>
                            </div>
                            <div class="form-group">
                                <label>Estoque Atual</label>
                                <input type="number" class="form-control" name="estoque_atual" id="edit_estoque">
                            </div>
                            <div class="form-group">
                                <label>Estoque Mínimo</label>
                                <input type="number" class="form-control" name="estoque_minimo" id="edit_estoque_minimo">
                            </div>
                            <div class="form-group">
                                <label>Preço de Custo</label>
                                <input type="number" step="0.01" class="form-control" name="preco_custo" id="edit_custo">
                            </div>
                            <div class="form-group">
                                <label>Marca</label>
                                <input type="text" class="form-control" name="marca" id="edit_marca">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- DataTables JavaScript -->
<script src="MVC/COMMON/vendor/datatables/jquery.dataTables.min.js"></script>
<script src="MVC/COMMON/vendor/datatables/dataTables.bootstrap4.min.js"></script>

<script>
var dataTable;

$(document).ready(function() {
    // Destroy existing DataTable if it exists
    if (dataTable) {
        dataTable.destroy();
    }
    
    // Initialize DataTable
    dataTable = $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
        },
        "pageLength": 10,
        "ordering": true,
        "searching": true,
        "destroy": true
    });

    // Edit Product
    $('.edit-product').click(function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        var categoria = $(this).data('categoria');
        var preco = $(this).data('preco');
        var descricao = $(this).data('descricao');
        var estoque = $(this).data('estoque');
        var estoqueMinimo = $(this).data('estoque-minimo');
        var custo = $(this).data('custo');
        var marca = $(this).data('marca');

        $('#edit_id').val(id);
        $('#edit_nome').val(nome);
        $('#edit_categoria').val(categoria);
        $('#edit_preco').val(preco);
        $('#edit_descricao').val(descricao);
        $('#edit_estoque').val(estoque);
        $('#edit_estoque_minimo').val(estoqueMinimo);
        $('#edit_custo').val(custo);
        $('#edit_marca').val(marca);
    });

    // Delete Product
    $('.delete-product').click(function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        
        if (confirm('Tem certeza que deseja excluir o produto "' + nome + '"?')) {
            window.location.href = 'MVC/MODEL/excluir_produto.php?id=' + id;
        }
    });
});
</script>

<?php require_once 'MVC/COMMON/footer.php'; ?> 