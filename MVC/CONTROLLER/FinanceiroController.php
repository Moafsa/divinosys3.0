<?php
require_once 'MVC/MODEL/FinanceiroModel.php';

class FinanceiroController {
    private $model;
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
        $this->model = new FinanceiroModel($conn);
    }

    public function index() {
        // Verifica se há uma ação específica
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'categorias':
                    $this->categorias();
                    return;
                case 'contas':
                    $this->contas();
                    return;
                case 'relatorios':
                    $this->relatorios();
                    return;
                default:
                    $this->dashboard();
                    return;
            }
        }
        $this->dashboard();
    }

    private function dashboard() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'tipo' => $_POST['tipo'],
                'categoria_id' => $_POST['categoria_id'],
                'conta_id' => $_POST['conta_id'],
                'valor' => $_POST['valor'],
                'data_movimentacao' => $_POST['data_movimentacao'],
                'descricao' => $_POST['descricao'],
                'status' => $_POST['status'],
                'forma_pagamento' => $_POST['forma_pagamento']
            ];
            try {
                $movimentacaoId = null;
                if (isset($_POST['id']) && !empty($_POST['id'])) {
                    if ($this->model->updateMovimentacao($_POST['id'], $dados)) {
                        $movimentacaoId = $_POST['id'];
                        $_SESSION['msg'] = "Movimentação atualizada com sucesso!";
                    } else {
                        $_SESSION['msg'] = "Erro ao atualizar movimentação.";
                    }
                } else {
                    $movimentacaoId = $this->model->addMovimentacao($dados);
                    if ($movimentacaoId) {
                        $_SESSION['msg'] = "Movimentação registrada com sucesso!";
                    } else {
                        $_SESSION['msg'] = "Erro ao registrar movimentação.";
                    }
                }
                // Processa upload de imagens se houver
                if ($movimentacaoId && !empty($_FILES['imagens']['name'][0])) {
                    $uploadDir = 'uploads/financeiro/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    foreach ($_FILES['imagens']['tmp_name'] as $key => $tmpName) {
                        if (!$_FILES['imagens']['name'][$key]) continue;
                        $fileName = uniqid() . '_' . basename($_FILES['imagens']['name'][$key]);
                        $filePath = $uploadDir . $fileName;
                        if (move_uploaded_file($tmpName, $filePath)) {
                            $sql = "INSERT INTO imagens_movimentacoes (movimentacao_id, caminho) VALUES (?, ?)";
                            $stmt = $this->conn->prepare($sql);
                            $stmt->bind_param("is", $movimentacaoId, $filePath);
                            $stmt->execute();
                        }
                    }
                }
            } catch (Exception $e) {
                $_SESSION['msg'] = "Erro ao registrar movimentação: " . $e->getMessage();
            }
            header('Location: ?view=financeiro&action=dashboard');
            exit;
        }
        $data_fim = date('Y-m-d');
        $data_inicio = date('Y-m-d', strtotime('-30 days'));
        if (isset($_GET['data_inicio']) && isset($_GET['data_fim'])) {
            $data_inicio = $_GET['data_inicio'];
            $data_fim = $_GET['data_fim'];
        }
        $resumo = $this->model->getResumoFinanceiro($data_inicio, $data_fim);
        if (!$resumo) {
            $resumo = [
                'total_receitas' => 0,
                'total_despesas' => 0,
                'receitas_pendentes' => 0,
                'despesas_pendentes' => 0,
                'valor_receitas_pendentes' => 0,
                'valor_despesas_pendentes' => 0
            ];
        }
        $movimentacoes = $this->model->getMovimentacoes([
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim
        ]);
        // Buscar imagens associadas a cada movimentação
        foreach ($movimentacoes as &$mov) {
            $sql = "SELECT caminho FROM imagens_movimentacoes WHERE movimentacao_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("i", $mov['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $mov['imagens'] = [];
            while ($img = $result->fetch_assoc()) {
                $mov['imagens'][] = $img['caminho'];
            }
        }
        unset($mov);
        include(ROOT_PATH . '/MVC/VIEWS/financeiro/dashboard.php');
    }

    private function categorias() {
        $tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
        $categorias = $this->model->getCategorias($tipo);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $_POST['nome'];
            $tipo = $_POST['tipo'];
            $descricao = $_POST['descricao'] ?? null;
            if ($this->model->addCategoria($nome, $tipo, $descricao)) {
                $_SESSION['msg'] = "Categoria adicionada com sucesso!";
            } else {
                $_SESSION['msg'] = "Erro ao adicionar categoria.";
            }
            header('Location: ?view=financeiro&action=categorias');
            exit;
        }
        include(ROOT_PATH . '/MVC/VIEWS/financeiro/categorias.php');
    }

    private function contas() {
        $contas = $this->model->getContas();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'nome' => $_POST['nome'],
                'tipo' => $_POST['tipo'],
                'saldo_inicial' => $_POST['saldo_inicial'],
                'banco' => $_POST['banco'] ?? null,
                'agencia' => $_POST['agencia'] ?? null,
                'conta' => $_POST['conta'] ?? null
            ];
            if ($this->model->addConta($dados)) {
                $_SESSION['msg'] = "Conta adicionada com sucesso!";
            } else {
                $_SESSION['msg'] = "Erro ao adicionar conta.";
            }
            header('Location: ?view=financeiro&action=contas');
            exit;
        }
        include(ROOT_PATH . '/MVC/VIEWS/financeiro/contas.php');
    }

    private function relatorios() {
        $data_fim = date('Y-m-d');
        $data_inicio = date('Y-m-d', strtotime('-30 days'));
        if (isset($_GET['data_inicio']) && isset($_GET['data_fim'])) {
            $data_inicio = $_GET['data_inicio'];
            $data_fim = $_GET['data_fim'];
        }
        $resumo = $this->model->getResumoFinanceiro($data_inicio, $data_fim);
        $movimentacoes_por_categoria = $this->model->getMovimentacoesPorCategoria($data_inicio, $data_fim);
        $fluxo_caixa = $this->model->getFluxoCaixa($data_inicio, $data_fim);
        include(ROOT_PATH . '/MVC/VIEWS/financeiro/relatorios.php');
    }
} 