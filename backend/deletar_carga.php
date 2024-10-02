<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexao.php");

$database = new Database();
$conn = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $username = $data['username'];
    
    if (!isset($data['carga_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID da carga não fornecido.']);
        exit;
    }

    $cargaId = $data['carga_id'];

    $sql = "DELETE FROM informacoes_circuito WHERE carga_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao preparar a consulta: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('s', $cargaId);

    try {
        $stmt->execute();
        // Verifica se alguma linha foi afetada pela exclusão
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Nenhuma carga encontrada com esse ID.']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao deletar carga: ' . $e->getMessage()]);
    } finally {
        $stmt->close();
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method Not Allowed']);
}