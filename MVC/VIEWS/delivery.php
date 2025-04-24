<?php
// O arquivo init.php já foi incluído pelo System.class.php
// Então não precisamos incluir config.php ou conexao.php novamente

// Adicionar botão de novo pedido no topo
?>
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 text-end">
            <a href="<?php echo $config->url('?view=gerar_pedido_delivery'); ?>" class="btn btn-success">
                <i class="fas fa-plus-circle"></i> Novo Pedido Delivery
            </a>
        </div>
    </div>
</div>

<?php
// Verificar se há pesquisa
if(isset($_POST['pesquisa']) && $_POST['pesquisa'] != '') {
	try {
		$pesquisa = mysqli_real_escape_string($conn, $_POST['pesquisa']);
		$stmt = mysqli_prepare($conn, "SELECT * FROM clientes WHERE nome LIKE ? OR cpf_cnpj LIKE ?");
		$search_term = "%{$pesquisa}%";
		mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
		
		if (!mysqli_stmt_execute($stmt)) {
			throw new Exception("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
		}
		
		$clientes = mysqli_stmt_get_result($stmt);
		
		if (!$clientes) {
			throw new Exception("Erro ao obter resultados: " . mysqli_error($conn));
		}
?>
		<div class="container-fluid">
			<table class="table table-striped table-sm">
				<thead>
					<tr>
						<th>Nome</th>
						<th>Endereço</th>
						<th>Bairro</th>
						<th>Cidade</th>
						<th>Estado</th>
						<th>Telefone#1</th>
						<th>Telefone#2</th>
						<th>E-Mail</th>
						<th>Seleção</th>
					</tr>
				</thead>

<?php

				while($rows_clientes = mysqli_fetch_assoc($clientes)){ ?>



				<tbody>
					<td><?php echo htmlspecialchars($rows_clientes['nome'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['endereco'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['bairro'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['cidade'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['estado'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['tel1'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['tel2'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['email'], ENT_QUOTES, 'UTF-8');?></td>
					<td>
						<form method="POST" action="<?php echo $config->url('?view=delivery'); ?>">
							<input type="hidden" name="id" value="<?php echo $rows_clientes['id'];?>">
							<input type="hidden" name="nome" value="<?php echo htmlspecialchars($rows_clientes['nome'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="endereco" value="<?php echo htmlspecialchars($rows_clientes['endereco'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="bairro" value="<?php echo htmlspecialchars($rows_clientes['bairro'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="tel1" value="<?php echo htmlspecialchars($rows_clientes['tel1'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="tel2" value="<?php echo htmlspecialchars($rows_clientes['tel2'], ENT_QUOTES, 'UTF-8');?>">
							<button type="submit" class="btn btn-outline-danger btn-sm">Selecionar</button>
						</form>
					</td>
				</tbody>

<?php }?>
			</table>
		</div>
<?php
		mysqli_stmt_close($stmt);
	} catch (Exception $e) {
		error_log("Erro na página de delivery: " . $e->getMessage());
		$_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao carregar clientes: " . htmlspecialchars($e->getMessage()) . "</div>";
	}
} elseif(isset($_POST['id'])) {
	$id = $_POST['id'];
	$nome = $_POST['nome'];
	$endereco = $_POST['endereco'];
	$bairro = $_POST['bairro'];
	$tel1 = $_POST['tel1'];
	$tel2 = $_POST['tel2'];
?>
<div class="row">

	<div class="col-lg-12 text-center" style="color:black; padding: 3%;">
		<hr>
		
		<h2><?php echo htmlspecialchars($nome); ?></h2>
	</div>
	
	<div class="col-lg-12 text-center" style=" color: red;">
		<h5>Endereço:</h5>
		<h2><?php echo htmlspecialchars($endereco); ?>, Bairro: <?php echo htmlspecialchars($bairro); ?></h2>
	</div>

</div>
<hr>

<h4 style="padding: 3%;" class="mb-12 text-center" ><button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#Modal_categorias" >Novo Pedido Delivery</button></h4>

<div class="col-lg-12 text-center">


<a href="<?php echo $config->url('?view=delivery'); ?>" class="btn btn-outline-info col-lg-2 text-center" style="padding: 5px;">Retornar ao Início</a>

</div>


<?php
} else {
	try {
		$stmt = mysqli_prepare($conn, "SELECT * FROM clientes");
		
		if (!mysqli_stmt_execute($stmt)) {
			throw new Exception("Erro ao executar consulta: " . mysqli_stmt_error($stmt));
		}
		
		$clientes = mysqli_stmt_get_result($stmt);
		
		if (!$clientes) {
			throw new Exception("Erro ao obter resultados: " . mysqli_error($conn));
		}
?>

		<div class="container-fluid">
			<table class="table table-striped table-sm">
				<thead>
					<tr>
						<th>Nome</th>
						<th>Endereço</th>
						<th>Bairro</th>
						<th>Cidade</th>
						<th>Estado</th>
						<th>Telefone#1</th>
						<th>Telefone#2</th>
						<th>E-Mail</th>
						<th>Seleção</th>
					</tr>
				</thead>

<?php

				while($rows_clientes = mysqli_fetch_assoc($clientes)){ ?>




				<tbody>
					<td><?php echo htmlspecialchars($rows_clientes['nome'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['endereco'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['bairro'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['cidade'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['estado'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['tel1'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['tel2'], ENT_QUOTES, 'UTF-8');?></td>
					<td><?php echo htmlspecialchars($rows_clientes['email'], ENT_QUOTES, 'UTF-8');?></td>
					<td>
						<form method="POST" action="<?php echo $config->url('?view=delivery'); ?>">
							<input type="hidden" name="id" value="<?php echo $rows_clientes['id'];?>">
							<input type="hidden" name="nome" value="<?php echo htmlspecialchars($rows_clientes['nome'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="endereco" value="<?php echo htmlspecialchars($rows_clientes['endereco'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="bairro" value="<?php echo htmlspecialchars($rows_clientes['bairro'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="tel1" value="<?php echo htmlspecialchars($rows_clientes['tel1'], ENT_QUOTES, 'UTF-8');?>">
							<input type="hidden" name="tel2" value="<?php echo htmlspecialchars($rows_clientes['tel2'], ENT_QUOTES, 'UTF-8');?>">
							<button type="submit" class="btn btn-outline-danger btn-sm">Selecionar</button>
						</form>
					</td>
				</tbody>

<?php }

?>


			</table>
		</div>

<?php
		mysqli_stmt_close($stmt);
	} catch (Exception $e) {
		error_log("Erro na página de delivery: " . $e->getMessage());
		$_SESSION['msg'] = "<div class='alert alert-danger'>Erro ao carregar clientes: " . htmlspecialchars($e->getMessage()) . "</div>";
	}
}
?>


<!-- Modal CATEGORIAS-->
<div class="modal fade bd-example-modal-lg" id="Modal_categorias" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle" style="color: black;">Categorias</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="background: black;">

        
		<div class="container-fluid " >
			
			<div class="row" >
				
				
				<?php

				$tab_produtos = "SELECT * FROM produtos";

				$produtos = mysqli_query($conn, $tab_produtos);

				$comparativo = array();
				while ($cat = mysqli_fetch_assoc($produtos)) {
					
					$categoria = $cat['categoria'];

					if (in_array("$categoria", $comparativo) != true) {
					 	array_push($comparativo, $categoria);
						?>
							
							<form method="POST" action="<?php echo $config->url('?view=novo_pedido'); ?>">
							<div class="form-group" >
								<input type="hidden" name="pesquisa" id="pesquisa" value=" ">
								<input type="hidden" name="mesa" id="mesa" value="delivery">
								<input type="hidden" name="cliente" id="cliente" value="<?php echo htmlspecialchars($nome); ?>">
								<input type="submit" class="btn btn-outline-warning" name="categoria" value="<?php echo htmlspecialchars($categoria); ?>" ></input><label type="hidden" style="width: 10px;"></label>
								
							</div>
							</form>
							

					<?php 
					} 
				}; 

				?>
				</div>
			</div>
		</div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
        
      </div>
    </div>
  </div>
</div>
