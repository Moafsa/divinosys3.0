<?php
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
?>

<div class="container-fluid">
    <!-- Menu de Navegação Interna -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link<?php echo ($action == 'dashboard') ? ' active' : ''; ?>" href="?view=financeiro&action=dashboard">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php echo ($action == 'categorias') ? ' active' : ''; ?>" href="?view=financeiro&action=categorias">Categorias</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php echo ($action == 'contas') ? ' active' : ''; ?>" href="?view=financeiro&action=contas">Contas</a>
        </li>
        <li class="nav-item">
            <a class="nav-link<?php echo ($action == 'relatorios') ? ' active' : ''; ?>" href="?view=financeiro&action=relatorios">Relatórios</a>
        </li>
    </ul>

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Módulo Financeiro</h1>
    </div>

    <!-- Menu de Navegação -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="?view=financeiro&action=dashboard" class="text-decoration-none">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Dashboard</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Visão Geral</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="?view=financeiro&action=categorias" class="text-decoration-none">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Categorias</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Classificação</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tags fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <a href="?view=financeiro&action=contas" class="text-decoration-none">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Contas</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">Bancárias</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-university fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Conteúdo Dinâmico -->
    <div class="row">
        <div class="col-12">
            <?php
            switch ($action) {
                case 'dashboard':
                    include 'financeiro/dashboard.php';
                    break;
                case 'categorias':
                    include 'financeiro/categorias.php';
                    break;
                case 'contas':
                    include 'financeiro/contas.php';
                    break;
                case 'relatorios':
                    include 'financeiro/relatorios.php';
                    break;
                default:
                    include 'financeiro/dashboard.php';
            }
            ?>
        </div>
    </div>
</div> 