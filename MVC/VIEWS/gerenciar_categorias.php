<?php
// Verificação de login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    $_SESSION['msg'] = "<div class='alert alert-danger'>Acesso não autorizado! Faça login primeiro.</div>";
    header("Location: index.php");
    exit;
}

// Buscar categorias existentes
$query = "SELECT id, nome, imagem FROM categorias ORDER BY nome";
$result = mysqli_query($conn, $query);

// Mensagem de feedback
if (isset($_SESSION['msg'])) {
    echo $_SESSION['msg'];
    unset($_SESSION['msg']);
}
?>

<div class="container-fluid">
    <!-- Cabeçalho da Página -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gerenciamento de Categorias</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addCategoriaModal">
            <i class="fas fa-plus"></i> Nova Categoria
        </button>
    </div>

    <!-- Grid de Categorias -->
    <div class="row">
        <?php while ($categoria = mysqli_fetch_assoc($result)): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <img src="<?php echo $categoria['imagem'] ? $categoria['imagem'] : 'mvc/common/img/no-image.jpg'; ?>" 
                                 class="img-fluid rounded-circle" 
                                 style="width: 100px; height: 100px; object-fit: cover;"
                                 alt="<?php echo htmlspecialchars($categoria['nome']); ?>">
                        </div>
                        <div class="text-center">
                            <h5 class="font-weight-bold text-primary"><?php echo htmlspecialchars($categoria['nome']); ?></h5>
                        </div>
                        <div class="text-center mt-3">
                            <button class="btn btn-sm btn-info mr-2 editar-categoria" 
                                    data-id="<?php echo $categoria['id']; ?>"
                                    data-nome="<?php echo htmlspecialchars($categoria['nome']); ?>"
                                    data-imagem="<?php echo htmlspecialchars($categoria['imagem']); ?>"
                                    data-toggle="modal" 
                                    data-target="#editCategoriaModal">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-danger excluir-categoria"
                                    data-id="<?php echo $categoria['id']; ?>"
                                    data-nome="<?php echo htmlspecialchars($categoria['nome']); ?>">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal Adicionar Categoria -->
<div class="modal fade" id="addCategoriaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nova Categoria</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <form action="MVC/MODEL/salvar_categoria.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome da Categoria</label>
                        <input type="text" class="form-control" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label>Imagem</label>
                        <input type="file" class="form-control" name="imagem" accept="image/*" onchange="previewImage(this, 'preview')">
                        <img id="preview" class="img-fluid mt-2 rounded" style="max-height: 200px; display: none;">
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

<!-- Modal Editar Categoria -->
<div class="modal fade" id="editCategoriaModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Categoria</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
<form action="MVC/MODEL/salvar_categoria.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
<div class="form-group">
                        <label>Nome da Categoria</label>
                        <input type="text" class="form-control" name="nome" id="edit_nome" required>
                    </div>
                    <div class="form-group">
                        <label>Imagem</label>
                        <input type="file" class="form-control" name="imagem" accept="image/*" onchange="previewImage(this, 'edit_preview')">
                        <img id="edit_preview" class="img-fluid mt-2 rounded" style="max-height: 200px;">
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

<!-- Adicionar SweetAlert2 primeiro -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Esperar o DOM carregar completamente
document.addEventListener('DOMContentLoaded', function() {
    // Preview de imagem
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#' + previewId).attr('src', e.target.result).show();
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Editar categoria
    $('.editar-categoria').click(function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        var imagem = $(this).data('imagem');

        $('#edit_id').val(id);
        $('#edit_nome').val(nome);
        if (imagem) {
            $('#edit_preview').attr('src', imagem).show();
        }
    });

    // Excluir categoria
    $('.excluir-categoria').click(function() {
        var id = $(this).data('id');
        var nome = $(this).data('nome');
        
        Swal.fire({
            title: 'Confirmar exclusão?',
            text: 'Deseja realmente excluir a categoria "' + nome + '"?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sim, excluir!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
            window.location.href = 'MVC/MODEL/excluir_categoria.php?id=' + id;
            }
        });
    });

    // Tornar a função previewImage global
    window.previewImage = previewImage;

    // Feedback com SweetAlert2
    <?php if (isset($_SESSION['swal_msg'])): ?>
        Swal.fire(<?php echo $_SESSION['swal_msg']; unset($_SESSION['swal_msg']); ?>);
    <?php endif; ?>
});
</script>

<style>
.card {
    transition: transform .2s;
}
.card:hover {
    transform: translateY(-5px);
}
.modal-content {
    border-radius: 15px;
}
.btn {
    border-radius: 5px;
}
.form-control {
    border-radius: 5px;
}
</style> 