<?php
// Configurações para exibir todos os erros e avisos durante a execução
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//Definindo pra requisição demorar no máximo 1min e 30seg (90 seg)
set_time_limit(90);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

class UserLogger {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function logUserActionInsert($username, $circuito){
        $query_log = "INSERT INTO log_usuarios (user_id, categoria, circuito) VALUES (?, 1, ?)";
        $stmt_log = $this->conn->prepare($query_log);

        if($stmt_log === false){
            die("Erro ao preparar a inserção do log: " . $this->conn->error);
        }

        $stmt_log->bind_param('ss', $username, $circuito);

        if(!$stmt_log->execute()){
            die("Erro ao inserir no log: " . $stmt_log->error);
        }

        $stmt_log->close();

        return "Log registrado com sucesso.";
    }

    public function logUserActionUpdate($username, $circuito) {
        $query_log = "INSERT INTO log_usuarios (user_id, categoria, circuito) VALUES (?, 2, ?)";
        $stmt_log = $this->conn->prepare($query_log);

        if ($stmt_log === false) {
            die("Erro ao preparar a consulta do log: " . $this->conn->error);
        }

        $stmt_log->bind_param('ss', $username, $circuito);

        if (!$stmt_log->execute()) {
            die("Erro ao inserir no log: " . $stmt_log->error);
        }

        $stmt_log->close();

        return "Log registrado com sucesso.";
    }

    public function logUserActionDelete($username) {
        $query_log = "INSERT INTO log_usuarios (user_id, categoria) VALUES (?, 3)";
        $stmt_log = $this->conn->prepare($query_log);

        if($stmt_log === false) {
            die("Erro ao preparar a inserção do log: " . $this->conn->error);
        }

        $stmt_log->bind_param('s', $username);

        if(!$stmt_log->execute()){
            die("Erro ao inserir no log: " . $stmt_log->error);
        }

        $stmt_log->close();

        return "Log registrado com sucesso.";
    }
}

// Inicializa a conexão com o banco de dados
$database = new Database();
$db = $database->getConnection();

// Inicializa a classe de log
$userLogger = new UserLogger($db);

// Lê o JSON enviado via POST e decodifica para um array associativo
$json = file_get_contents('php://input');
$post_json = json_decode($json, true);

// Verifica a ação e executa o registro de log correspondente


if (!isset($post_json['action'])) {
    $resultados = [];
    foreach ($post_json as $logEntry) {
        // Extrai os dados de cada log
        $username = $logEntry['user_id'];
        $circuito = $logEntry['circuito'];
        
        // Chama a função para registrar o log de inserção
        $resultado = $userLogger->logUserActionInsert($username, $circuito);
        $resultados[] = $resultado;
    }
    // Retorna uma resposta com todos os resultados
    echo json_encode($resultados);
} else{
    $action = $post_json['action'];
    $username = $post_json['user_id'];
    if(isset($post_json['circuito'])){
        $circuito = $post_json['circuito'];
    }

    if ($action === 'update') {
        $resultado = $userLogger->logUserActionUpdate($username, $circuito);
    } elseif ($action === 'delete') {
        $resultado = $userLogger->logUserActionDelete($username);
    } else {
        $resultado = "Ação inválida.";
    }
    
    // Envia a resposta
    echo $resultado;
}

// Fecha a conexão com o banco de dados
//$db->close();
