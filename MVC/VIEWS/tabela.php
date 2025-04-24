<?php

// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir o arquivo de conexão
include_once(__DIR__ . "/include_conexao.php");

try {
    // Consultar produtos com prepared statement
    $stmt = mysqli_prepare($conn, "SELECT * FROM produtos");
    
    if (!$stmt) {
        throw new Exception("Erro ao preparar consulta: " . mysqli_error($conn));
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
    }
    
    $produtos = mysqli_stmt_get_result($stmt);
    
    if (!$produtos) {
        throw new Exception("Erro ao obter resultados: " . mysqli_error($conn));
    }
    
    mysqli_stmt_close($stmt);
    
} catch (Exception $e) {
    error_log("Erro na página de produtos: " . $e->getMessage());
    $_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao carregar produtos: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<style type="text/css"> 
    html { overflow-y: auto; } /* Permitir rolagem vertical */
    .table-container { 
        max-height: 650px; 
        overflow-y: auto; 
    }
</style>

<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Produtos</h1>
    <p class="mb-4">Gerenciamento de produtos do sistema</p>

    <div class="row mb-4">
        <div class="col-md-8">
			<?php
			if(isset($_SESSION['msg'])){
				echo $_SESSION['msg'];
				unset($_SESSION['msg']);
			}
			?>
	</div>
        <div class="col-md-4 text-right">
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModalcad">
                <i class="fas fa-plus"></i> Cadastrar Novo
            </button>
	</div>
	</div>

		<!-- CONSTRUÇÃO DO MODAL DE CADASTRO -->
	<div class="modal fade bd-example-modal-xl" id="myModalcad" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog modal-xl" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title text-center" id="myModalLabel">	Cadastrar Um Novo Produto </h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<!-- FIM DO CABEÇALHO DO MODAL DE CADASTRO -->
				</div>
				<div class="modal-body">
      				<!-- CRIA O FORMULÁRIO PARA CADASTRAR E ENVIAR PELO METODO POST PARA O SCRIPT "cadastrar_produtos.php" -->
        			<form method="POST" action="MVC/MODEL/cadastrar_produtos.php">
        			<div class="row">
	          			<div class="form-group col-md-4">
	            			<label for="recipient-name" class="col-form-label">Código(Barras):</label>
	            			<input name="codigo" type="text" class="form-control" >
	          			</div>
	          			<div class="form-group col-md-4">
	            			<label for="recipient-name" class="col-form-label">Nome:</label>
	            			<input name="nome" type="text" class="form-control" >
	          			</div>
	          			<div class="form-group col-md-4">
	            			<label for="message-text" class="col-form-label">Detalhes:</label>
	            			<input name="detalhes" type="text" class="form-control" >
	          			</div>
	          			<div class="form-group col-md-2">
	            			<label for="recipient-name" class="col-form-label">Categoria:</label>
	            			<input name="categoria" type="text" class="form-control" >
	          			</div>	          				          			
	          			<div class="form-group col-md-2">
	            			<label for="recipient-name" class="col-form-label">Preço de Custo:</label>
	            			<input name="preco_custo" type="text" class="form-control" >
	          			</div>
	          			<div class="form-group col-md-2">
	            			<label for="recipient-name" class="col-form-label">Preço de Venda:</label>
	            			<input name="preco_venda" type="text" class="form-control" >
	          			</div>
	          			<div class="form-group col-md-2">
	            			<label for="recipient-name" class="col-form-label">Estoque Atual:</label>
	            			<input name="estoque_atual" type="text" class="form-control" >
	          			</div>
	          			<div class="form-group col-md-2">
	            			<label for="recipient-name" class="col-form-label">Estoque Mínimo:</label>
	            			<input name="estoque_minimo" type="text" class="form-control" >
	          			</div>
	          			<div class="form-group col-md-2">
	            			<label for="recipient-name" class="col-form-label">Data da Compra:</label>
	            			<input name="data_compra" type="text" class="form-control" id="compra">
	          			</div>	          				          			
	          			<div class="form-group col-md-2">
	            			<label for="recipient-name" class="col-form-label">Data da Validade:</label>
	            			<input name="data_validade" type="text" class="form-control" id="validade">
	          			</div>
	          			<div class="form-group col-md-2">
	            			<label for="recipient-name" class="col-form-label">Unidade (Kg/L):</label>
	            			<input name="unidade" type="text" class="form-control" >
	          			</div>
	          			<div class="form-group col-md-4">
	            			<label for="recipient-name" class="col-form-label">Marca:</label>
	            			<input name="marca" type="text" class="form-control" >
	          			</div>
	          			<div class="form-group col-md-4">
	            			<label for="recipient-name" class="col-form-label">Fornecedor:</label>
	            			<input name="fornecedor" type="text" class="form-control" >
	          			</div>	          				          			
	         			 <div class="form-group col-md-12">
	            			<label for="message-text" class="col-form-label">Observação/Ingredientes:</label>
	            			<textarea name="observacoes" class="form-control" ></textarea>
	          			</div>	          			
	          		</div>
	      			<div class="modal-footer">
	        			<button type="submit" class="btn btn-success">Cadastrar</button>
	      			</div>
        			</form>
				</div>
			</div>
			<!-- FIM DO CORPO DA MENSAGEM DO MODAL DE CADASTRO -->
		</div>
	</div>

	<label></label>
	<h4>  Relação de Produtos :</h4>
      <div class="table-responsive" style="overflow: auto; height: 650px">
        <table class="table table-striped table-sm">
          <thead>
            <tr>
              <th>Lista</th>
                    <th>Código</th>
              <th>Nome</th>   
              <th>Categoria</th>
                    <th>Descrição</th>
                    <th>Preço Normal</th>
                    <th>Preço Mini</th>
              <th>Estoque</th>
                    <th>Marca</th>
              <th>Ação</th>
            </tr>
          </thead>
          <tbody>
          	<?php
          	$num = 0;

                // Consulta com JOIN para obter o nome da categoria e informações de estoque
                $query = "SELECT p.*, c.nome as categoria_nome, e.estoque_atual, e.estoque_minimo, e.marca, e.fornecedor
                         FROM produtos p 
                         LEFT JOIN categorias c ON p.categoria_id = c.id 
                         LEFT JOIN estoque e ON p.id = e.produto_id
                         ORDER BY p.nome";
                         
                $produtos = mysqli_query($conn, $query);

                while($rows_produtos = mysqli_fetch_assoc($produtos)) {
          		$num +=1;
          		?>
            <tr>
                        <td><?php echo $num; ?></td>
                        <td><?php echo !empty($rows_produtos['codigo']) ? htmlspecialchars($rows_produtos['codigo']) : '-'; ?></td>
                        <td><?php echo !empty($rows_produtos['nome']) ? htmlspecialchars($rows_produtos['nome']) : '-'; ?></td>
                        <td><?php echo !empty($rows_produtos['categoria_nome']) ? htmlspecialchars($rows_produtos['categoria_nome']) : '-'; ?></td>
                        <td><?php echo !empty($rows_produtos['descricao']) ? htmlspecialchars($rows_produtos['descricao']) : '-'; ?></td>
                        <td>R$ <?php echo number_format($rows_produtos['preco_normal'], 2, ',', '.'); ?></td>
                        <td><?php echo $rows_produtos['preco_mini'] ? 'R$ ' . number_format($rows_produtos['preco_mini'], 2, ',', '.') : '-'; ?></td>
                        <td>
                            <?php 
                            $estoque_atual = $rows_produtos['estoque_atual'] ?? 0;
                            $estoque_minimo = $rows_produtos['estoque_minimo'] ?? 0;
                            $classe_estoque = $estoque_atual <= $estoque_minimo ? 'text-danger' : 'text-success';
                            echo "<span class='{$classe_estoque}'>" . number_format($estoque_atual, 2, ',', '.') . "</span>";
                            ?>
                        </td>
                        <td><?php echo !empty($rows_produtos['marca']) ? htmlspecialchars($rows_produtos['marca']) : '-'; ?></td>
                        <td>
                            <button type="button" class="btn btn-warning btn-icon-split btn-sm" data-toggle="modal" data-target="#editar"
                                data-id="<?php echo $rows_produtos['id']; ?>"
                                data-codigo="<?php echo !empty($rows_produtos['codigo']) ? htmlspecialchars($rows_produtos['codigo']) : ''; ?>"
                                data-nome="<?php echo !empty($rows_produtos['nome']) ? htmlspecialchars($rows_produtos['nome']) : ''; ?>"
                                data-categoria="<?php echo !empty($rows_produtos['categoria_nome']) ? htmlspecialchars($rows_produtos['categoria_nome']) : ''; ?>"
                                data-descricao="<?php echo !empty($rows_produtos['descricao']) ? htmlspecialchars($rows_produtos['descricao']) : ''; ?>"
                                data-preco-normal="<?php echo $rows_produtos['preco_normal']; ?>"
                                data-preco-mini="<?php echo $rows_produtos['preco_mini']; ?>"
                                data-estoque-atual="<?php echo $rows_produtos['estoque_atual']; ?>"
                                data-estoque-minimo="<?php echo $rows_produtos['estoque_minimo']; ?>"
                                data-marca="<?php echo !empty($rows_produtos['marca']) ? htmlspecialchars($rows_produtos['marca']) : ''; ?>"
                                data-fornecedor="<?php echo !empty($rows_produtos['fornecedor']) ? htmlspecialchars($rows_produtos['fornecedor']) : ''; ?>"
                            >Editar</button>
                            <button type="button" class="btn btn-danger btn-icon-split btn-sm" data-toggle="modal" data-target="#excluir"
                                data-id="<?php echo $rows_produtos['id']; ?>"
                                data-nome="<?php echo !empty($rows_produtos['nome']) ? htmlspecialchars($rows_produtos['nome']) : ''; ?>"
                            >Excluir</button>
				</td>
            </tr>
				<?php } ?>        
          </tbody>
        </table>
  </div>
</div>

				<!-- CONSTRUÇÃO DO MODAL DE EDIÇÃO -->
<div class="modal fade bd-example-modal-xl" id="editar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
				  <div class="modal-dialog modal-xl" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title" id="exampleModalLabel">Produto</h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				      	<!-- CRIA O FORMULÁRIO PARA APRESENTRAÇÃO E ENVIA PELO METODO POST PARA O SCRIPT "edita_produtos.php" -->
				        <form method="POST" action="MVC/MODEL/edita_produtos.php">
				        	<div class="row">
					          	<div class="form-group col-md-4">
					            	<label for="recipient-name" class="col-form-label">Código(Barras):</label>
					            	<input name="codigo" type="text" class="form-control" id="codigo">
					          	</div>
					          	<div class="form-group col-md-4">
					            	<label for="recipient-name" class="col-form-label">Nome:</label>
					            	<input name="nome" type="text" class="form-control" id="recipient-name">
					          	</div>
					          	<div class="form-group col-md-4">
					            	<label for="message-text" class="col-form-label">Detalhes:</label>
					            	<input name="detalhes" type="text" class="form-control" id="detalhes-text">
					          	</div>
					          	<div class="form-group col-md-2">
					            	<label for="recipient-name" class="col-form-label">Categoria:</label>
					            	<input name="categoria" type="text" class="form-control" id="categoria">
					          	</div>	          				          			
					          	<div class="form-group col-md-2">
					            	<label for="recipient-name" class="col-form-label">Preço de Custo:</label>
					            	<input name="preco_custo" type="text" class="form-control" id="preco-custo">
					          	</div>
					          	<div class="form-group col-md-2">
					            	<label for="recipient-name" class="col-form-label">Preço de Venda:</label>
					            	<input name="preco_venda" type="text" class="form-control" id="preco-venda">
					          	</div>
					          	<div class="form-group col-md-2">
					            	<label for="recipient-name" class="col-form-label">Estoque Atual:</label>
					            	<input name="estoque_atual" type="text" class="form-control" id="estoque-atual">
					          	</div>
					          	<div class="form-group col-md-2">
					            	<label for="recipient-name" class="col-form-label">Estoque Mínimo:</label>
					            	<input name="estoque_minimo" type="text" class="form-control" id="estoque-minimo">
					          	</div>
					          	<div class="form-group col-md-2">
					            	<label for="recipient-name" class="col-form-label">Data da Compra:</label>
					            	<input name="data_compra" type="text" class="form-control"  id="data-compra">
					          	</div>	          				          			
					          	<div class="form-group col-md-2">
					            	<label for="recipient-name" class="col-form-label">Data da Validade:</label>
					            	<input name="data_validade" type="text" class="form-control"  id="data-validade">
					          	</div>
					          	<div class="form-group col-md-2">
					            	<label for="recipient-name" class="col-form-label">Unidade (Kg/L):</label>
					            	<input name="unidade" type="text" class="form-control" id="unidade">
					          	</div>
					          	<div class="form-group col-md-4">
					            	<label for="recipient-name" class="col-form-label">Marca:</label>
					            	<input name="marca" type="text" class="form-control" id="marca">
					          	</div>
					          	<div class="form-group col-md-4">
					            	<label for="recipient-name" class="col-form-label">Fornecedor:</label>
					            	<input name="fornecedor" type="text" class="form-control" id="fornecedor">
					          	</div>	          				          			
					         		<div class="form-group col-md-12">
					            	<label for="message-text" class="col-form-label">Observação/Ingredientes:</label>
					            	<textarea name="observacoes" class="form-control" id="observacoes" ></textarea>
					          	</div>	          			
					          </div>
				          <!--cria um campo invisivel "hidden" para pegar o id "id_Produto"-->
				          <input name="id" type="hidden" id="id_Produto">

					      <div class="modal-footer">
					        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancelar</button>
					        <button type="submit" class="btn btn-warning">Editar</button>
					      </div>
				        </form>
				      </div>
				    </div>
				  </div>
				</div>

<script>
// Inicializar datepickers
$(document).ready(function(){
			$('#compra').datepicker({	
        format: 'dd/mm/yyyy',
        language: 'pt-BR'
			});

			$('#validade').datepicker({	
        format: 'dd/mm/yyyy',
        language: 'pt-BR'
    });
});
</script>


