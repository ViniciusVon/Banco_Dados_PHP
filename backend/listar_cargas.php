<?php

// Configura a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

// Classe para listar cargas
class CargaLister {
    private $mysqli;

    public function __construct($database) {
        $this->mysqli = $database->getConnection();
    }

    public function listCargas() {
        try {
            // Busca as cargas existentes
            $sql = "SELECT DISTINCT carga_id FROM informacoes_circuito";
            $result = $this->mysqli->query($sql);

            if (!$result) {
                // Se houver um erro na execução da consulta
                throw new Exception("Erro na consulta: " . $this->mysqli->error);
            }

            $cargas = $result->fetch_all(MYSQLI_ASSOC);

            // Prepara e envia a resposta como JSON
            header('Content-Type: application/json');
            echo json_encode(['cargas' => $cargas]);

        } catch (Exception $e) {
            // Se ocorrer uma exceção ao tentar consultar, retorna um erro HTTP 500 e uma mensagem JSON com o erro
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao consultar cargas: ' . $e->getMessage()]);
        }
    }
}

// Inicializa a conexão com o banco de dados
$database = new Database();

// Inicializa a classe de listagem de cargas e realiza a listagem
$cargaLister = new CargaLister($database);
$cargaLister->listCargas();