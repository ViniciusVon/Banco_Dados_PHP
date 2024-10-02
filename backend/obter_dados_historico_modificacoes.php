<?php
// Configura a exibição de erros para ajudar na depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

class HistoricoModificacoes{
    private $conn;
    private $page_size;
    private $page;

    public function __construct($conn, $data){
        $this->conn = $conn;
        $this->page_size = isset($data['tamanho_pagina']) ? $data['tamanho_pagina'] : 10;
        $this->page = isset($data['page']) ? $data['page'] : 1;
    }

    public function obterDados($data) {
        // Define a consulta SQL inicial para selecionar todos os dados da tabela log_usuarios
        $query_filters = "SELECT user_id, categoria, data_hora, circuito FROM log_usuarios";

        // Adiciona a ordenação pela data_hora em ordem decrescente para pegar os registros mais recentes primeiro
        $query_filters .= " ORDER BY data_hora DESC";
    
        // Define o tamanho da página e a página atual para a paginação
        $page_size = isset($data['tamanho_pagina']) ? $data['tamanho_pagina'] : 10;
        $page = isset($data['page']) ? $data['page'] : 1;
    
        
        
        // Adiciona limites de paginação à consulta
        $query_filters .= " LIMIT " . $page_size . " OFFSET " . ($page - 1) * $page_size;
        // Prepara a consulta SQL
        $stmt = $this->conn->prepare($query_filters);
        
        // Executa a consulta
        $stmt->execute();
        $result_filters = $stmt->get_result();
    
        // Verifica se a execução da consulta foi bem-sucedida
        if (!$result_filters) {
            die("Query failed: " . $this->conn->error);
        }
    
        // Armazena os resultados da consulta em um array
        $rows_filters = [];
        while ($row_filter = $result_filters->fetch_assoc()) {
            $rows_filters[] = $row_filter;
        }
    
        // Define a consulta SQL para contar o número total de resultados
        $count_query = "SELECT COUNT(*) as count FROM log_usuarios";
    
        // Prepara a consulta de contagem
        $count_stmt = $this->conn->prepare($count_query);
    
        // Executa a consulta de contagem
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
    
        // Verifica se a execução da consulta de contagem foi bem-sucedida
        if (!$count_result) {
            die("Count query failed: " . $this->conn->error);
        }
    
        // Obtém o número total de linhas
        $count_row = $count_result->fetch_assoc();
        $total_rows = $count_row['count'];
    
        // Verifica se há resultados
        if (count($rows_filters) > 0) {
            // Retorna os dados e metadados em formato JSON
            $response = array(
                'resposta' => array(
                    'dados' => $rows_filters
                ),
                'metadados' => array(
                    'contagem' => $total_rows
                )
            );
    
            // Retorna os dados em formato JSON
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo json_encode(array('total_rows' => 0, 'rows' => array()));
        }
    }
}

// Cria uma instância da classe Database e obtém a conexão
$database = new Database();
$conn = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);

// Cria uma instância da classe HistoricoModificacoes e obtém os dados
$historico = new HistoricoModificacoes($conn, $data);
$historico->obterDados($data);