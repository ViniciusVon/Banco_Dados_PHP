<?php
// Configura a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

// Cria uma nova instância da classe Database
$db = new Database();
$conn = $db->getConnection();

// Verifica se o valor 'usuario' está presente na requisição POST
if (!isset($_POST['usuario'])) {
    // Se o campo 'usuario' não estiver presente, retorna um erro HTTP 400 e uma mensagem JSON
    http_response_code(400);
    echo json_encode(['error' => 'Usuário não especificado.']);
    exit;
}

$usuario = $_POST['usuario']; // Armazena o valor do usuário recebido na requisição POST

// Prepara e executa a consulta para verificar o perfil do usuário
$sql = "SELECT perfil FROM usuarios WHERE user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Se houver um erro na preparação da consulta
    http_response_code(500);
    echo json_encode(['error' => 'Erro na preparação da consulta: ' . $conn->error]);
    exit;
}

// Vincula o parâmetro e executa a consulta
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

// Obtém o resultado da consulta
$user = $result->fetch_assoc();

// Verifica se o usuário foi encontrado
if (!$user) {
    // Se o usuário não for encontrado, retorna um erro HTTP 404 e uma mensagem JSON
    http_response_code(404);
    echo json_encode(['error' => 'Usuário não encontrado.']);
    exit;
}

// Determina se o usuário pode excluir com base no perfil
$canDelete = $user['perfil'] == 2;

// Prepara e envia a resposta como JSON
header('Content-Type: application/json');
echo json_encode(['canDelete' => $canDelete]);