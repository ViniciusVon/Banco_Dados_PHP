<?php

// Configura a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

class DataUploader {
    private $pdo;

    public function __construct($database) {
        $this->pdo = $database->getPdoConnection();
    }

    public function uploadData($data) {
        if (!isset($data['data']) || !is_array($data['data'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Formato de dados inválido.']);
            return;
        }

        if (empty($data['data'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Nenhum dado recebido.']);
            return;
        }

        $cargaId = uniqid();
        $checkSql = "SELECT COUNT(*) FROM informacoes_circuito WHERE circuito = :circuito";
        $insertSql = "INSERT INTO informacoes_circuito (circuito, circuito_antigo, id_gesac_wfb, id_gesac_antigo, tipo, solicitante, fornecedor, remessa, carga_id) VALUES (:circuito, :coluna2, :coluna3, :coluna4, :coluna5, :coluna6, :coluna7, :coluna8, :carga_id)";
        
        $checkStmt = $this->pdo->prepare($checkSql);
        $insertStmt = $this->pdo->prepare($insertSql);

        $this->pdo->beginTransaction();

        $array_success = [];

        try {
            foreach ($data['data'] as $row) {
                if (count($row) === 8) {
                    $checkStmt->execute([':circuito' => $row[0]]);
                    $exists = $checkStmt->fetchColumn();

                    if ($exists == 0) {
                        $insertStmt->execute([
                            ':circuito' => $row[0], //Circuito
                            ':coluna2' => $row[1], //Circuito_antigo
                            ':coluna3' => $row[2], //Id gesac
                            ':coluna4' => $row[3], //ID gesac antigo
                            ':coluna5' => $row[4], //tipo
                            ':coluna6' => $row[5], //solicitante
                            ':coluna7' => $row[6], //fornecedor
                            ':coluna8' => $row[7], //remessa
                            ':carga_id' => $cargaId
                        ]);
                        $array_success[] = $row[0];
                    }
                } else {
                    throw new Exception('Dados incompletos em uma das linhas.');
                }
            }

            $this->pdo->commit();
            if($array_success != null) {
                echo json_encode(['success' => true, 'message' => 'Dados inseridos com sucesso!', 'carga_id' => $cargaId, 'array_success' => $array_success]);
            } else {
                echo json_encode(['todos_duplicados' => true]);
            }
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao inserir dados: ' . $e->getMessage()]);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

// Inicializa a conexão com o banco de dados
$database = new Database();

// Inicializa a classe de upload de dados e processa os dados recebidos
$dataUploader = new DataUploader($database);
$json = file_get_contents('php://input');
$post_json = json_decode($json, true);
$dataUploader->uploadData($post_json);