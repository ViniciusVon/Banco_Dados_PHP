<?php
// Configura a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
require_once 'conexao.php'; // Inclui a classe Database

class UserProfile {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function verifyUser($usuario) {
        $sql = "SELECT perfil FROM usuarios WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $usuario); // Vincula o parâmetro
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        return $user;
    }
}

// Código para uso da classe
$database = new Database();
$db = $database->getConnection();
$userProfile = new UserProfile($db);

// Obtém e decodifica o JSON enviado via POST
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['usuario'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Usuário não especificado.']);
    exit;
}

$usuario = $data['usuario'];
$user = $userProfile->verifyUser($usuario);

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'Usuário não encontrado.']);
    exit;
}

$canEdit = $user['perfil'] == 2 || $user['perfil'] == 3;
header('Content-Type: application/json');
echo json_encode(['canEdit' => $canEdit]);