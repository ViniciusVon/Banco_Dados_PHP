<?php
// Configurações para exibir todos os erros e avisos durante a execução
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

class AccessUpdater {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function updateAccess($data) {
        $response = [];
        if (isset($data['rows']) && is_array($data['rows'])) {
            foreach ($data['rows'] as $row) {
                $user_id = $row['user_id'];
                $perfil = $row['perfil'];

                $sql = "UPDATE usuarios SET perfil = ? WHERE user_id = ?";
                if ($stmt = $this->conn->prepare($sql)) {
                    $stmt->bind_param("is", $perfil, $user_id);
                    if ($stmt->execute()) {
                        $response[] = ["user_id" => $user_id, "success" => true];
                    } else {
                        $response[] = ["user_id" => $user_id, "success" => false, "error" => "Erro ao executar a declaração: " . $stmt->error];
                    }
                    $stmt->close();
                } else {
                    $response[] = ["user_id" => $user_id, "success" => false, "error" => "Erro ao preparar a declaração: " . $this->conn->error];
                }
            }
        } else {
            $response[] = ["success" => false, "error" => "Nenhum dado recebido para atualização."];
        }
        return $response;
    }
}

// Inicializa a conexão com o banco de dados
$database = new Database();
$db = $database->getConnection();

// Inicializa a classe de atualização de acessos e processa os dados recebidos
$accessUpdater = new AccessUpdater($db);
$json = file_get_contents('php://input');
$post_json = json_decode($json, true);
$response = $accessUpdater->updateAccess($post_json);

// Fecha a conexão com o banco de dados
$db->close(); // Corrigido para fechar a conexão corretamente

// Verifica se todas as atualizações foram bem-sucedidas
$all_success = array_reduce($response, function($carry, $item) {
    return $carry && $item["success"];
}, true);

// Define o tipo de conteúdo como JSON e retorna a resposta codificada como JSON
header('Content-Type: application/json');
echo json_encode(["success" => $all_success, "details" => $response]);