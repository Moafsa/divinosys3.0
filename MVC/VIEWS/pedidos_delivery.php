<?php
// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir arquivo de configuração
include_once(__DIR__ . "/../MODEL/config.php");

// Incluir o arquivo de conexão
include_once(__DIR__ . "/include_conexao.php");

// Inicializar a configuração
$config = Config::getInstance();

try {
    // Consultar pedidos delivery com prepared statement
    $stmt = mysqli_prepare($conn, "
        SELECT p.*, c.nome as cliente_nome, c.telefone, c.endereco, c.bairro, c.cidade, c.estado, c.cep, c.complemento, c.numero
        FROM pedido p
        LEFT JOIN clientes c ON p.cliente = c.id
        WHERE p.delivery = 1 AND p.status NOT IN ('Finalizado', 'Entregue', 'Entregue (Mesa)', 'Entregue (Delivery)', 'Cancelado')
        ORDER BY p.id DESC
    ");
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
    }
    
    $pedidos = mysqli_stmt_get_result($stmt);
    
    if (!$pedidos) {
        throw new Exception("Erro ao obter resultados: " . mysqli_error($conn));
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    error_log("Erro na página de pedidos delivery: " . $e->getMessage());
    $_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao carregar pedidos: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h3 style="color: #4D4D4D;">Pedidos Delivery</h3>
        </div>
        <div class="col-md-6 text-right">
            <a href="<?php echo $config->url('?view=delivery'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Novo Pedido
            </a>
        </div>
    </div>

<?php
    if(isset($_SESSION['msg'])) {
        echo $_SESSION['msg'];
        unset($_SESSION['msg']);
    }
    ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Pedido #</th>
                    <th>Cliente</th>
                    <th>Endereço</th>
                    <th>Telefone</th>
                    <th>Data/Hora</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
<?php
                if (isset($pedidos) && mysqli_num_rows($pedidos) > 0) {
                    while($pedido = mysqli_fetch_assoc($pedidos)) {
                        $status_class = '';
                        switch($pedido['status']) {
                            case 'pendente':
                                $status_class = 'warning';
                                break;
                            case 'em_preparo':
                                $status_class = 'info';
                                break;
                            case 'saiu_entrega':
                                $status_class = 'primary';
                                break;
                            case 'entregue':
                                $status_class = 'success';
                                break;
                            case 'cancelado':
                                $status_class = 'danger';
                                break;
                        }
                ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($pedido['id']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['cliente_nome']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['endereco'] . ', ' . $pedido['bairro']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['telefone']); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $status_class; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $pedido['status'])); ?>
                        </span>
                    </td>
                    <td>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#detalhesModal<?php echo $pedido['id']; ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                        <?php if ($pedido['status'] != 'entregue' && $pedido['status'] != 'cancelado') { ?>
                            <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#statusModal<?php echo $pedido['id']; ?>">
                                <i class="fas fa-edit"></i>
                            </button>
                        <?php } ?>
                    </td>
                </tr>

                <!-- Modal de Detalhes -->
                <div class="modal fade" id="detalhesModal<?php echo $pedido['id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg">
				    <div class="modal-content">
				      <div class="modal-header">
                                <h5 class="modal-title">Detalhes do Pedido #<?php echo $pedido['id']; ?></h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
				      </div>
						<div class="modal-body">
							<div class="row">
                                    <div class="col-md-6">
                                        <h6>Informações do Cliente</h6>
                                        <p><strong>Nome:</strong> <?php echo htmlspecialchars($pedido['cliente_nome']); ?></p>
                                        <p><strong>Endereço:</strong> <?php echo htmlspecialchars($pedido['endereco']); ?></p>
                                        <p><strong>Bairro:</strong> <?php echo htmlspecialchars($pedido['bairro']); ?></p>
                                        <p><strong>Telefone:</strong> <?php echo htmlspecialchars($pedido['telefone']); ?></p>
			          			</div>
                                    <div class="col-md-6">
                                        <h6>Informações do Pedido</h6>
                                        <p><strong>Data/Hora:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['data_pedido'])); ?></p>
                                        <p><strong>Status:</strong> <span class="badge badge-<?php echo $status_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $pedido['status'])); ?></span></p>
                                        <p><strong>Total:</strong> R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></p>
			          			</div>
			          			</div>	          				          			
                                <hr>
                                <h6>Itens do Pedido</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Quantidade</th>
                                                <th>Valor Unit.</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            try {
                                                $stmt_itens = mysqli_prepare($conn, "
                                                    SELECT i.*, p.nome as produto_nome, p.preco_venda 
                                                    FROM pedido_itens i
                                                    JOIN produtos p ON i.produto = p.nome
                                                    WHERE i.pedido_id = ?
                                                ");
                                                mysqli_stmt_bind_param($stmt_itens, "i", $pedido['id']);
                                                mysqli_stmt_execute($stmt_itens);
                                                $itens = mysqli_stmt_get_result($stmt_itens);
                                                
                                                while($item = mysqli_fetch_assoc($itens)) {
                                                    echo "<tr>";
                                                    echo "<td>" . htmlspecialchars($item['produto_nome']) . "</td>";
                                                    echo "<td>" . $item['quantidade'] . "</td>";
                                                    echo "<td>R$ " . number_format($item['valor_unitario'], 2, ',', '.') . "</td>";
                                                    echo "<td>R$ " . number_format($item['valor_total'], 2, ',', '.') . "</td>";
                                                    echo "</tr>";
                                                }
                                                
                                                mysqli_stmt_close($stmt_itens);
                                            } catch (Exception $e) {
                                                echo "<tr><td colspan='4' class='text-center text-danger'>Erro ao carregar itens do pedido</td></tr>";
                                                error_log("Erro ao carregar itens do pedido: " . $e->getMessage());
                                            }
                                            ?>
                                        </tbody>
                                    </table>
			          			</div>
			          			</div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                <?php if ($pedido['status'] != 'entregue' && $pedido['status'] != 'cancelado') { ?>
                                    <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#statusModal<?php echo $pedido['id']; ?>">
                                        Atualizar Status
                                    </button>
                                <?php } ?>
			          			</div>
			          			</div>	
			          			</div>
			          			</div>

                <!-- Modal de Atualização de Status -->
                <div class="modal fade" id="statusModal<?php echo $pedido['id']; ?>" tabindex="-1" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Atualizar Status do Pedido #<?php echo $pedido['id']; ?></h5>
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
			          			</div>
                            <form method="POST" action="<?php echo $config->url('mvc/model/atualizar_status_pedido.php'); ?>">
                                <div class="modal-body">
                                    <input type="hidden" name="pedido_id" value="<?php echo $pedido['id']; ?>">
                                    <div class="form-group">
                                        <label>Novo Status:</label>
                                        <select name="status" class="form-control" required>
                                            <option value="pendente" <?php echo $pedido['status'] == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                            <option value="em_preparo" <?php echo $pedido['status'] == 'em_preparo' ? 'selected' : ''; ?>>Em Preparo</option>
                                            <option value="saiu_entrega" <?php echo $pedido['status'] == 'saiu_entrega' ? 'selected' : ''; ?>>Saiu para Entrega</option>
                                            <option value="entregue" <?php echo $pedido['status'] == 'entregue' ? 'selected' : ''; ?>>Entregue</option>
                                            <option value="cancelado" <?php echo $pedido['status'] == 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                        </select>
			          			</div>	          			
			          		</div>
						<div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Atualizar</button>
					    </div>
					    </form>
					  </div>
                    </div>
                </div>
                <?php 
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>Nenhum pedido delivery encontrado</td></tr>";
                }
                ?>
            </tbody>
        </table>
					 </div>
					</div>

<script>
$(document).ready(function(){
    // Atualizar a página a cada 30 segundos para manter os pedidos atualizados
    setInterval(function(){
        location.reload();
    }, 30000);
});
</script>