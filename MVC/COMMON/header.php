<?php
include_once(__DIR__ . "/../MODEL/config.php");

// Usar a variável de sessão para a cor
$cor = isset($_SESSION['cor']) ? $_SESSION['cor'] : 'danger';

// Obter o nome do usuário da sessão
$usuario = isset($_SESSION['nome']) ? $_SESSION['nome'] : (isset($_SESSION['user']) ? $_SESSION['user'] : 'Usuário');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="Sistema de PDV para restaurantes">
  <meta name="author" content="">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

  <title>Divinosys 1.0</title>

<link href="<?php echo url('MVC/COMMON/css/animate.min.css'); ?>" rel="stylesheet"/><!--ESTE COMANDO CRIA A NOTIFICAÇÃO ANIMADA  -->
<link href="<?php echo url('MVC/COMMON/css/bootstrap-datepicker.css'); ?>" rel="stylesheet"/>

<link rel="shortcut icon"  href="<?php echo url('MVC/COMMON/img/beer.png'); ?>"><!--este comando muda o icone da janela-->

  <!-- Custom fonts for this template-->
<link href="<?php echo url('MVC/COMMON/VENDOR/fontawesome-free/css/all.min.css'); ?>" rel="stylesheet" type="text/css">
  
  <!-- Bootstrap 5 CSS -->
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->

  <!-- Custom styles for this template-->
<link href="<?php echo url('MVC/COMMON/css/sb-admin-2.min.css'); ?>" rel="stylesheet">
<link href="<?php echo url('MVC/COMMON/css/custom.css'); ?>" rel="stylesheet">
<link href="<?php echo url('MVC/COMMON/css/sidebar-fix.css'); ?>" rel="stylesheet">
  
</head>


<!--INICIO DO BODY E INICIO DA CHAMADA DOS SCRIPTS JS!-->
<body id="page-top">

  <!-- INICIO DA CHAMADA DA CLASSE JQUERY-->
  <!-- Bootstrap core JavaScript-->
<script src="<?php echo url('MVC/COMMON/VENDOR/jquery/jquery.min.js'); ?>"></script>
<script src="<?php echo url('MVC/COMMON/VENDOR/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
  
  <!-- Bootstrap 5 JavaScript -->
  <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> -->

  <!-- Core plugin JavaScript-->
<script src="<?php echo url('MVC/COMMON/VENDOR/jquery-easing/jquery.easing.min.js'); ?>"></script>

  <!-- Custom scripts for all pages-->
<script src="<?php echo url('MVC/COMMON/js/sb-admin-2.min.js'); ?>"></script>
  <!-- FIM DA CHAMADA DA CLASSE JQUERY-->

<script src="<?php echo url('MVC/COMMON/VENDOR/chart.js/Chart.min.js'); ?>"></script>

<script src="<?php echo url('MVC/COMMON/VENDOR/bootstrap/js/bootstrap.min.js'); ?>"></script>
<script src="<?php echo url('MVC/COMMON/js/bootstrap-datepicker.min.js'); ?>"></script>
<script src="<?php echo url('MVC/COMMON/js/bootstrap-datepicker.pt-BR.min.js'); ?>"></script>
<!-- FIM DA CHAMADA DOS SCRIPTS JS!-->


  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-<?php echo $cor;?> sidebar sidebar-dark accordion" id="accordionSidebar">

      <!-- Sidebar - Brand -->
      <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo url('?view=Dashboard1'); ?>">
        <div class="sidebar-brand-icon rotate-n-15">
          <i class="fas fa-print"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Divinosys 1.0</div>
      </a>

      <!-- Divider -->
      <hr class="sidebar-divider my-0">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

      <!-- Nav Item - Dashboard -->


      <li class="nav-item active">
        <a class="nav-link"  href="<?php echo url('?view=Dashboard1'); ?>">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Mesas</span></a>
      </li>

      <li class="nav-item active">
        <a class="nav-link" href="<?php echo url('?view=delivery'); ?>">
          <i class="fa fa-motorcycle"></i>
          <span>Delivery</span></a>
      </li>

      <li class="nav-item active">
        <a class="nav-link" href="index.php?view=pedidos">
          <i class="fas fa-clipboard-list"></i>
          <span>Pedidos</span></a>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        Grupo 1
      </div>


      <!-- Nav Item - Utilities Collapse Menu -->
      <li class="nav-item active">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities" aria-expanded="true" aria-controls="collapseUtilities">
          <i class="fas fa-fw fa-wrench"></i>
          <span>FERRAMENTAS</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Grupo de Ferramentas</h6>
            <a class="collapse-item" href="#" data-toggle="modal" data-target="#calculadora" style=" border-radius: 8px; font-size:18px;">Calculadora</a>
          </div>
        </div>
      </li>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        Grupo 2
      </div>

      
      <li class="nav-item active">
        <a class="nav-link collapsed"  href="#" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
          <i class="fas fa-fw fa-folder"></i>
          <span>GERENCIAMENTO</span>
        </a>
        <!-- Para o item iniciara berta (class="collapse show")-->
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Gestão</h6>
            <a class="collapse-item" href="<?php echo url('?view=estoque'); ?>" style=" border-radius: 8px; font-size:18px;">Estoque</a>
            <a class="collapse-item" href="index.php?view=gerenciar_categorias" style=" border-radius: 8px; font-size:18px;">Categorias</a>
            <a class="collapse-item" href="index.php?view=relatorios" style=" border-radius: 8px; font-size:18px;">Relatórios</a>
          </div>
        </div>
      </li>

      <!-- Nav Item - Tables -->
      <li class="nav-item active">
        <a class="nav-link" href="index.php?view=gerenciar_produtos">
          <i class="fas fa-fw fa-table"></i>
          <span>Produtos</span></a>
      </li>

      <!-- Nav Item - Tables -->
      <li class="nav-item active">
        <a class="nav-link" href="<?php echo url('?view=clientes'); ?>">
          <i class="fas fa-fw fa-user"></i>
          <span>Clientes</span></a>
      </li>



      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

          <!-- Topbar Search -->


          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">


              <!-- Dropdown - Messages -->
              <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in" aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                  <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                      <button class="btn btn-primary" type="button">
                        <i class="fas fa-search fa-sm"></i>
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </li>

            <!-- Nav Item - Alerts -->
 

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
<img class="img-profile rounded-circle" src="MVC/COMMON/img/User.png">
                <label style="width: 15px;"></label>
                <span class="text-center" style=" font-size: 25px; color: #888888;"><b><?php echo $usuario; ?></b></span>
                
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">

                <a style=" font-size: 20px; color: #888888;" class="dropdown-item" href="MVC/VIEWS/perfil.php">
                  <i class="fas fa-user-circle fa-sm fa-fw mr-2 text-gray-400"></i>
                  Perfil
                </a>

                <a style=" font-size: 20px; color: #888888;" class="dropdown-item" href="#" data-toggle="modal" data-target="#CadastroModal">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Cadastrar Usuario
                </a>

                <a style=" font-size: 20px; color: #888888;" class="dropdown-item" href="#" data-toggle="modal" data-target="#ConfiguraçaoModal">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                  Configuração
                </a>

                <div class="dropdown-divider"></div>
                <a style=" font-size: 20px; color: #888888;" class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>

          </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">



<!-- Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel" style="color: red;"><b>Sair do Sistema</b></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h5 class="text-center"><b>Tem Certeza que deseja Sair do Sistema?</b></h5>
      </div>
      <div class="modal-footer">
        <form method="POST" action="MVC/MODEL/logout.php">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-danger">Sim!</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Modal -->
<div class="modal fade" id="ConfiguraçaoModal" tabindex="-1" role="dialog" aria-labelledby="ConfiguraçaoModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel" style="color: red;"><b>Configurações</b></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
       
        <h5 style="padding: 5%;" class="text-center"><b> Defina a cor das abas:</b></h5>
        <div class="text-center">
          <form method="POST" action="<?php echo url('MVC/MODEL/cor_e_quantidade_de_mesas.php'); ?>">
            <input type="radio" name="cor" value="1">&nbsp;&nbsp;VERDE &nbsp;&nbsp;
            <input type="radio" name="cor" value="2">&nbsp;&nbsp;VERMELHO &nbsp;&nbsp;
            <input type="radio" name="cor" value="3">&nbsp;&nbsp;AMARELO &nbsp;&nbsp;
            <input type="radio" name="cor" value="4">&nbsp;&nbsp;CIANO &nbsp;&nbsp;
            <input type="radio" name="cor" value="5">&nbsp;&nbsp;AZUL &nbsp;&nbsp;
            <hr>

          <h5 style="padding: 5%;" class="text-center"><b> Defina a quantidade de Mesas do seu estabelecimento:</b></h5>
          <input class="text-center" type="number" name="mesas">


        </div>
      </div>

      <div class="modal-footer">
        
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-danger">Definir</button>
        </form>
      </div>
    </div>
  </div>
</div>




<!-- Modal -->
<div class="modal fade" id="CadastroModal" tabindex="-1" role="dialog" aria-labelledby="CadastroModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel" style="color: red;"><b>Configurações</b></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
<h5>Preencha os campos abaixo com um Login e uma senha, depois escreva uma pergunta que só você saiba responder. Ela será usada como segurança para que voçê possa recuperar sua senha </h5>

        <form method="POST" action="cadastrar_administrador.php">
          <label></label>
          <input name="login" type="text" class="form-control text-center" placeholder="LOGIN">
          <label></label>
          <input name="senha" type="text" class="form-control text-center" placeholder="SENHA">
          <label></label>
          <textarea name="pergunta" type="text" class="form-control text-center" placeholder="Escreva uma pergunta" ></textarea>
           <label></label>
          <input name="resposta" type="text" class="form-control text-center" placeholder="RESPOSTA">
          <input name="nivel" type="hidden" value="2">
                    

      </div>

      <div class="modal-footer">
        
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-danger">Cadastrar</button>
        </form>
      </div>
    </div>
  </div>
</div>


          <!-- NESSA PARTE FICA O CONTEÚDO DA PÁGINA -->
                        <!-- FOOTER -->

    <!-- Modal de Configurações -->
    <div class="modal fade" id="configModal" tabindex="-1" role="dialog" aria-labelledby="configModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="configModalLabel">Configurações do Sistema</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="<?php echo url('MVC/MODEL/cor_e_quantidade_de_mesas.php'); ?>">
                        <h5 class="text-center mb-4"><b>Defina a cor das abas:</b></h5>
                        <div class="text-center mb-4">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-success">
                                    <input type="radio" name="cor" value="1"> Verde
                                </label>
                                <label class="btn btn-outline-danger">
                                    <input type="radio" name="cor" value="2"> Vermelho
                                </label>
                                <label class="btn btn-outline-warning">
                                    <input type="radio" name="cor" value="3"> Amarelo
                                </label>
                                <label class="btn btn-outline-info">
                                    <input type="radio" name="cor" value="4"> Ciano
                                </label>
                                <label class="btn btn-outline-primary">
                                    <input type="radio" name="cor" value="5"> Azul
                                </label>
                            </div>
                        </div>

                        <hr>

                        <h5 class="text-center mb-4"><b>Quantidade de Mesas:</b></h5>
                        <div class="form-group">
                            <input type="number" name="mesas" class="form-control text-center" min="1" placeholder="Digite o número de mesas">
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Perfil do Estabelecimento -->
    <div class="modal fade" id="perfilModal" tabindex="-1" role="dialog" aria-labelledby="perfilModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="perfilModalLabel">Perfil do Estabelecimento</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <form id="perfilForm" enctype="multipart/form-data">
            <div class="modal-body">
              <div id="perfilMsg"></div>
              <div class="form-group">
                <label for="perfil_nome_estabelecimento">Nome do Estabelecimento</label>
                <input type="text" class="form-control" id="perfil_nome_estabelecimento" name="nome_estabelecimento" required>
              </div>
              <div class="form-group">
                <label for="perfil_cnpj">CNPJ</label>
                <input type="text" class="form-control" id="perfil_cnpj" name="cnpj">
              </div>
              <div class="form-group">
                <label for="perfil_endereco">Endereço</label>
                <input type="text" class="form-control" id="perfil_endereco" name="endereco">
              </div>
              <div class="form-group">
                <label for="perfil_telefone">Telefone</label>
                <input type="text" class="form-control" id="perfil_telefone" name="telefone">
              </div>
              <div class="form-group">
                <label for="perfil_site">Site</label>
                <input type="text" class="form-control" id="perfil_site" name="site">
              </div>
              <div class="form-group">
                <label for="perfil_mensagem_header">Mensagem de Cabeçalho (opcional)</label>
                <input type="text" class="form-control" id="perfil_mensagem_header" name="mensagem_header">
              </div>
              <div class="form-group">
                <label for="perfil_logo">Logo</label><br>
                <img id="perfil_logo_preview" src="MVC/COMMON/img/logo-default.png" style="width: 120px; height: 120px; object-fit: contain; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 1rem; background: #f7f7f7;">
                <input type="file" class="form-control" id="perfil_logo" name="logo" accept="image/*">
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

</div>
</div>
</body>
</html>

<script>
// Abrir modal ao clicar em Perfil
$(document).on('click', 'a[href="MVC/VIEWS/perfil.php"]', function(e) {
  e.preventDefault();
  $('#perfilMsg').html('');
  $('#perfilForm')[0].reset();
  $('#perfil_logo_preview').attr('src', 'MVC/COMMON/img/logo-default.png');
  // Buscar dados do perfil via AJAX
  $.get('MVC/MODEL/get_perfil.php', function(data) {
    if (data && data.success) {
      $('#perfil_nome_estabelecimento').val(data.perfil.nome_estabelecimento || '');
      $('#perfil_cnpj').val(data.perfil.cnpj || '');
      $('#perfil_endereco').val(data.perfil.endereco || '');
      $('#perfil_telefone').val(data.perfil.telefone || '');
      $('#perfil_site').val(data.perfil.site || '');
      $('#perfil_mensagem_header').val(data.perfil.mensagem_header || '');
      if (data.perfil.logo) {
        $('#perfil_logo_preview').attr('src', 'uploads/' + data.perfil.logo);
      }
    }
  }, 'json');
  $('#perfilModal').modal('show');
});
// Preview da logo
$('#perfil_logo').on('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(evt) {
      $('#perfil_logo_preview').attr('src', evt.target.result);
    };
    reader.readAsDataURL(file);
  }
});
// Submissão AJAX do formulário
$('#perfilForm').on('submit', function(e) {
  e.preventDefault();
  var formData = new FormData(this);
  $.ajax({
    url: 'MVC/MODEL/salvar_perfil.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(resp) {
      if (resp.success) {
        $('#perfilMsg').html('<div class="alert alert-success">' + resp.message + '</div>');
        setTimeout(function() { $('#perfilModal').modal('hide'); location.reload(); }, 1200);
      } else {
        $('#perfilMsg').html('<div class="alert alert-danger">' + (resp.message || 'Erro ao salvar perfil') + '</div>');
      }
    },
    error: function() {
      $('#perfilMsg').html('<div class="alert alert-danger">Erro ao salvar perfil</div>');
    }
  });
});
</script>