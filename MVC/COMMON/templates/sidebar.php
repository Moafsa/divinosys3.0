<!-- Mobile Menu Toggle -->
<button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
    <i class="fa fa-bars"></i>
</button>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay"></div>

<!-- Mobile Menu -->
<div class="mobile-menu">
    <div class="mobile-menu-header">
        <h5>Menu</h5>
        <button class="mobile-menu-close">
            <i class="fa fa-times"></i>
        </button>
    </div>
    
    <div class="mobile-menu-body">
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Orders -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-target="#ordersSubmenu">
                    <i class="fas fa-fw fa-shopping-cart"></i>
                    <span>Pedidos</span>
                    <i class="fas fa-chevron-right submenu-icon"></i>
                </a>
                <div class="collapse" id="ordersSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="collapse-item" href="orders.php">Listar Pedidos</a>
                        </li>
                        <li class="nav-item">
                            <a class="collapse-item" href="new-order.php">Novo Pedido</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Products -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-target="#productsSubmenu">
                    <i class="fas fa-fw fa-box"></i>
                    <span>Produtos</span>
                    <i class="fas fa-chevron-right submenu-icon"></i>
                </a>
                <div class="collapse" id="productsSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="collapse-item" href="products.php">Listar Produtos</a>
                        </li>
                        <li class="nav-item">
                            <a class="collapse-item" href="new-product.php">Novo Produto</a>
                        </li>
                        <li class="nav-item">
                            <a class="collapse-item" href="categories.php">Categorias</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Customers -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-target="#customersSubmenu">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Clientes</span>
                    <i class="fas fa-chevron-right submenu-icon"></i>
                </a>
                <div class="collapse" id="customersSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="collapse-item" href="customers.php">Listar Clientes</a>
                        </li>
                        <li class="nav-item">
                            <a class="collapse-item" href="new-customer.php">Novo Cliente</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Reports -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-target="#reportsSubmenu">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span>Relatórios</span>
                    <i class="fas fa-chevron-right submenu-icon"></i>
                </a>
                <div class="collapse" id="reportsSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="collapse-item" href="sales-report.php">Relatório de Vendas</a>
                        </li>
                        <li class="nav-item">
                            <a class="collapse-item" href="inventory-report.php">Relatório de Estoque</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Settings -->
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Configurações</span>
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Desktop Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
    // ... existing code ...
</ul> 