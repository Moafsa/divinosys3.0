<?php
$categorias = $data['categorias'];
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Categorias Financeiras</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#categoriaModal">
            <i class="fas fa-plus"></i> Nova Categoria
        </button>
    </div>

    <!-- Listagem -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Categorias</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Descrição</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $cat): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['nome']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $cat['tipo'] == 'receita' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($cat['tipo']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($cat['descricao']); ?></td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" onclick="editarCategoria(<?php echo $cat['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="excluirCategoria(<?php echo $cat['id']; ?>)">
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

<!-- Modal Nova Categoria -->
<div class="modal fade" id="categoriaModal" tabindex="-1" role="dialog" aria-labelledby="categoriaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoriaModalLabel">Nova Categoria</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="?view=financeiro&action=categorias">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo" class="form-control" required>
                            <option value="receita">Receita</option>
                            <option value="despesa">Despesa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Descrição</label>
                        <textarea name="descricao" class="form-control" rows="3"></textarea>
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

<script>
function editarCategoria(id) {
    // Implementar edição
    alert('Editar categoria ' + id);
}

function excluirCategoria(id) {
    if (confirm('Tem certeza que deseja excluir esta categoria?')) {
        // Implementar exclusão
        alert('Excluir categoria ' + id);
    }
}
</script> 