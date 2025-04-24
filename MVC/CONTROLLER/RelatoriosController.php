<?php
require_once 'MVC/MODEL/RelatoriosModel.php';

class RelatoriosController {
    private $model;
    private $conn;

    public function __construct($conn) {
        error_log("RelatoriosController::__construct - Iniciando");
        $this->conn = $conn;
        $this->model = new RelatoriosModel($conn);
        error_log("RelatoriosController::__construct - Model inicializado");
    }

    public function index() {
        error_log("RelatoriosController::index - Iniciando");
        
        // Check if there's an action to execute
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'exportToPDF':
                    return $this->exportToPDF();
                case 'exportToExcel':
                    return $this->exportToExcel();
            }
        }
        
        // Set default period (last 30 days)
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-30 days'));

        // Get filter dates if they exist
        if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $start_date = $_GET['start_date'];
            $end_date = $_GET['end_date'];
        }

        error_log("RelatoriosController::index - Datas: $start_date até $end_date");

        // Get status filter if it exists
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        error_log("RelatoriosController::index - Status: " . ($status ?? 'null'));

        // Get data
        $orders = $this->model->getOrdersByPeriod($start_date, $end_date, $status);
        error_log("RelatoriosController::index - Pedidos obtidos: " . count($orders));
        
        $summary = $this->model->getSalesSummary($start_date, $end_date);
        error_log("RelatoriosController::index - Resumo obtido");
        
        $bestSellingProducts = $this->model->getBestSellingProducts($start_date, $end_date);
        error_log("RelatoriosController::index - Produtos mais vendidos obtidos");
        
        $dailySales = $this->model->getDailySales($start_date, $end_date);
        error_log("RelatoriosController::index - Vendas diárias obtidas");

        // Prepare chart data
        $chartData = [
            'labels' => [],
            'amounts' => [],
            'orders' => []
        ];
        
        foreach ($dailySales as $sale) {
            $chartData['labels'][] = date('m/d', strtotime($sale['date']));
            $chartData['amounts'][] = floatval($sale['total_amount']);
            $chartData['orders'][] = intval($sale['total_orders']);
        }

        error_log("RelatoriosController::index - Dados do gráfico preparados");

        // Include the view
        error_log("RelatoriosController::index - Carregando view: MVC/VIEWS/relatorios/index.php");
        require_once 'MVC/VIEWS/relatorios/index.php';
        error_log("RelatoriosController::index - View carregada");
    }

    public function exportToPDF() {
        // Get filter parameters
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        // Get data
        $orders = $this->model->getOrdersByPeriod($start_date, $end_date, $status);
        $summary = $this->model->getSalesSummary($start_date, $end_date);

        // Set headers for download
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_pedidos.html"');

        // Generate HTML content
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; }
                .header { text-align: center; margin-bottom: 30px; }
                .summary { margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Relatório de Pedidos</h1>
                <p>Período: ' . date('d/m/Y', strtotime($start_date)) . ' até ' . date('d/m/Y', strtotime($end_date)) . '</p>
            </div>
            
            <div class="summary">
                <h2>Resumo</h2>
                <p>Total de Pedidos: ' . $summary['total_orders'] . '</p>
                <p>Valor Total: R$ ' . number_format($summary['total_amount'], 2, ',', '.') . '</p>
                <p>Delivery: ' . $summary['total_delivery'] . '</p>
                <p>Mesa: ' . $summary['total_table'] . '</p>
            </div>

            <h2>Lista de Pedidos</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Status</th>
                        <th>Tipo</th>
                        <th>Valor</th>
                        <th>Atendente</th>
                    </tr>
                </thead>
                <tbody>';
        
        foreach ($orders as $order) {
            $html .= '
                    <tr>
                        <td>' . $order['order_id'] . '</td>
                        <td>' . $order['customer'] . '</td>
                        <td>' . date('d/m/Y', strtotime($order['date'])) . '</td>
                        <td>' . $order['order_time'] . '</td>
                        <td>' . $order['status'] . '</td>
                        <td>' . ($order['delivery'] ? 'Delivery' : 'Mesa') . '</td>
                        <td>R$ ' . number_format($order['total_amount'], 2, ',', '.') . '</td>
                        <td>' . $order['attendant'] . '</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </body>
        </html>';

        echo $html;
        exit();
    }

    public function exportToExcel() {
        // Get filter parameters
        $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
        $status = isset($_GET['status']) ? $_GET['status'] : null;

        // Get data
        $orders = $this->model->getOrdersByPeriod($start_date, $end_date, $status);
        $summary = $this->model->getSalesSummary($start_date, $end_date);

        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_pedidos.csv"');

        // Create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // Add UTF-8 BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Add headers
        fputcsv($output, ['ID', 'Cliente', 'Data', 'Hora', 'Status', 'Tipo', 'Valor', 'Atendente']);

        // Add data rows
        foreach ($orders as $order) {
            fputcsv($output, [
                $order['order_id'],
                $order['customer'],
                date('d/m/Y', strtotime($order['date'])),
                $order['order_time'],
                $order['status'],
                $order['delivery'] ? 'Delivery' : 'Mesa',
                'R$ ' . number_format($order['total_amount'], 2, ',', '.'),
                $order['attendant']
            ]);
        }

        fclose($output);
        exit();
    }
} 