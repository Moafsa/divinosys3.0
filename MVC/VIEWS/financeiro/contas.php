<?php
$contas = $data['contas'];
?>

<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Contas Financeiras</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#contaModal">
            <i class="fas fa-plus"></i> Nova Conta
        </button>
    </div>

    <!-- Listagem -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Contas</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>Saldo Inicial</th>
                            <th>Saldo Atual</th>
                            <th>Banco</th>
                            <th>Agência</th>
                            <th>Conta</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contas as $conta): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($conta['nome']); ?></td>
                            <td>
                                <span class="badge badge-info">
                                    <?php echo ucfirst($conta['tipo']); ?>
                                </span>
                            </td>
                            <td class="text-right">
                                R$ <?php echo number_format($conta['saldo_inicial'], 2, ',', '.'); ?>
                            </td>
                            <td class="text-right">
                                R$ <?php echo number_format($conta['saldo_atual'], 2, ',', '.'); ?>
                            </td>
                            <td><?php echo htmlspecialchars($conta['banco']); ?></td>
                            <td><?php echo htmlspecialchars($conta['agencia']); ?></td>
                            <td><?php echo htmlspecialchars($conta['conta']); ?></td>
                            <td>
                                <button type="button" class="btn btn-info btn-sm" onclick="editarConta(<?php echo $conta['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="excluirConta(<?php echo $conta['id']; ?>)">
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

<!-- Modal Nova Conta -->
<div class="modal fade" id="contaModal" tabindex="-1" role="dialog" aria-labelledby="contaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contaModalLabel">Nova Conta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="?view=financeiro&action=contas">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nome</label>
                        <input type="text" name="nome" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo</label>
                        <select name="tipo" class="form-control" required>
                            <option value="conta_corrente">Conta Corrente</option>
                            <option value="conta_poupanca">Conta Poupança</option>
                            <option value="carteira">Carteira</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Saldo Inicial</label>
                        <input type="number" step="0.01" name="saldo_inicial" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Banco</label>
                        <input type="text" name="banco" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Agência</label>
                        <input type="text" name="agencia" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Conta</label>
                        <input type="text" name="conta" class="form-control">
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
function editarConta(id) {
    // Implementar edição
    alert('Editar conta ' + id);
}

function excluirConta(id) {
    if (confirm('Tem certeza que deseja excluir esta conta?')) {
        // Implementar exclusão
        alert('Excluir conta ' + id);
    }
}

// Atualiza campos baseado no tipo de conta
document.querySelector('select[name="tipo"]').addEventListener('change', function() {
    const tipo = this.value;
    const bancoGroup = document.querySelector('input[name="banco"]').closest('.form-group');
    const agenciaGroup = document.querySelector('input[name="agencia"]').closest('.form-group');
    const contaGroup = document.querySelector('input[name="conta"]').closest('.form-group');
    
    if (tipo === 'carteira') {
        bancoGroup.style.display = 'none';
        agenciaGroup.style.display = 'none';
        contaGroup.style.display = 'none';
    } else {
        bancoGroup.style.display = 'block';
        agenciaGroup.style.display = 'block';
        contaGroup.style.display = 'block';
    }
});
</script> 