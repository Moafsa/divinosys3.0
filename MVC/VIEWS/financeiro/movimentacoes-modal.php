<?php /* Modal Nova Movimentação - Padrão Limpo */ ?>
<div class="modal fade" id="movimentacaoModal" tabindex="-1" role="dialog" aria-labelledby="movimentacaoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="movimentacaoModalLabel">Nova Movimentação</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formMovimentacao" method="POST" action="?view=financeiro&action=dashboard" enctype="multipart/form-data">
                <div class="modal-body">
                    <?php if (empty($categorias) || empty($contas)): ?>
                        <div class="alert alert-warning text-center">
                            Atenção: Cadastre pelo menos uma categoria e uma conta antes de lançar uma movimentação financeira.
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo <span class="text-danger">*</span></label>
                                <select name="tipo" class="form-control" required>
                                    <option value="receita">Receita</option>
                                    <option value="despesa">Despesa</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Categoria <span class="text-danger">*</span></label>
                                <select name="categoria_id" class="form-control" required <?php echo empty($categorias) ? 'disabled' : ''; ?>>
                                    <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Conta <span class="text-danger">*</span></label>
                                <select name="conta_id" class="form-control" required <?php echo empty($contas) ? 'disabled' : ''; ?>>
                                    <?php foreach ($contas as $conta): ?>
                                    <option value="<?php echo $conta['id']; ?>"><?php echo htmlspecialchars($conta['nome']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Valor <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="valor" class="form-control" required autofocus>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Data Movimentação <span class="text-danger">*</span></label>
                                <input type="date" name="data_movimentacao" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Description</label>
                                <input type="text" name="descricao" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control">
                                    <option value="pago">Pago</option>
                                    <option value="pendente">Pendente</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Forma de Pagamento</label>
                                <select name="forma_pagamento" class="form-control">
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="cartao">Cartão</option>
                                    <option value="pix">Pix</option>
                                    <option value="boleto">Boleto</option>
                                    <option value="transferencia">Transferência</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Imagens (opcional)</label>
                                <input type="file" name="imagens[]" class="form-control" accept="image/*" multiple>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" <?php echo (empty($categorias) || empty($contas)) ? 'disabled' : ''; ?>>Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Foco automático no campo Valor ao abrir modal
$('#movimentacaoModal').on('shown.bs.modal', function () {
    $(this).find('input[name="valor"]').trigger('focus');
});
// Limpa o campo hidden 'id' ao fechar o modal
$('#movimentacaoModal').on('hidden.bs.modal', function () {
    $(this).find('input[name="id"]').remove();
    $(this).find('form')[0].reset();
});
// Salvar e adicionar outra
$('#btnSalvarAdicionarOutra').on('click', function() {
    $('#formMovimentacao').append('<input type="hidden" name="addOutra" value="1">');
    $('#formMovimentacao').submit();
});

// Filtrar categorias conforme o tipo selecionado
$(document).ready(function() {
    var allCategories = <?php echo json_encode($categorias); ?>;
    var $tipo = $('select[name="tipo"]');
    var $categoria = $('select[name="categoria_id"]');

    window.filterCategorias = function(selectedCategoriaId) {
        var tipoSelecionado = $tipo.val();
        $categoria.empty();
        var found = false;
        allCategories.forEach(function(cat) {
            if (cat.tipo === tipoSelecionado) {
                var selected = '';
                if (selectedCategoriaId && cat.id == selectedCategoriaId) {
                    selected = ' selected';
                    found = true;
                }
                $categoria.append('<option value="' + cat.id + '"' + selected + '>' + cat.nome + '</option>');
            }
        });
        // Se não encontrou a categoria, seleciona a primeira
        if (!found && $categoria.find('option').length > 0) {
            $categoria.val($categoria.find('option').first().val());
        }
    }

    // Função global para ser chamada ao editar
    window.atualizarCategoriasModal = function(tipo, categoriaId) {
        $tipo.val(tipo);
        window.filterCategorias(categoriaId);
    }

    $tipo.on('change', function() {
        var selectedCategoriaId = $categoria.val();
        window.filterCategorias(selectedCategoriaId);
    });

    // Inicializa ao carregar
    window.filterCategorias($categoria.val());
});
</script>

<?php
// Garante conexão e listas em qualquer contexto
global $conn;
if (!isset($conn) || !$conn) {
    require_once __DIR__ . '/../../MODEL/config.php';
    require_once __DIR__ . '/../../MODEL/conexao.php';
}
require_once __DIR__ . '/../../MODEL/FinanceiroModel.php';
$model = new FinanceiroModel($conn);
$categorias = $model->getCategorias();
$contas = $model->getContas(); 