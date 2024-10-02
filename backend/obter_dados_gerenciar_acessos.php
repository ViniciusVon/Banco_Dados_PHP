<?php
// Configura a exibição de erros para ajudar na depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

// Verifica se a conexão com o banco de dados foi bem-sucedida
if ($conn->connect_error) die("Conexão falhou: " . $conn->connect_error);

// Obtém os dados JSON enviados via POST e decodifica
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Define o tamanho da página e a página atual para a paginação
$page_size = isset($data['tamanho_pagina']) ? $data['tamanho_pagina'] : 10;
$page = isset($data['page']) ? $data['page'] : 1;

// Calcula o deslocamento para a consulta de paginação
$offset = ($page - 1) * $page_size;

// Consulta para contar o total de usuários (no código atual, 'LIMIT' não faz sentido aqui e pode ser removido)
// A query COUNT normalmente não usa LIMIT
$query_count = "SELECT COUNT(*) FROM usuarios LIMIT ? OFFSET ?";

// Consulta para selecionar os usuários com base na paginação
$sql = "SELECT usuarios.user_id, usuarios.perfil FROM usuarios LIMIT ? OFFSET ?";

// Prepara e executa a consulta de contagem
$stmt = $conn->prepare($query_count);
$stmt->bind_param("ii", $page_size, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Obtém a contagem total de usuários
$count = array_values($result->fetch_assoc())[0];

// Prepara e executa a consulta para obter os dados dos usuários
$stmt2 = $conn->prepare($sql);
$stmt2->bind_param("ii", $page_size, $offset);
$stmt2->execute();
$result2 = $stmt2->get_result();

// Verifica se a consulta retornou resultados
if ($result2->num_rows > 0) {
    
    $usuarios = array();
    
    // Armazena os dados dos usuários em um array
    while ($row = $result2->fetch_assoc()) {
        $usuarios[] = $row;
    }
    
    // Cria um array com os metadados da resposta, incluindo a contagem total de usuários
    $array_meta = [
        'contagem' => $count
    ];

    // Cria um array com os dados dos usuários
    $array_dados = [
        'dados' => $usuarios
    ];

    // Cria um array de resposta que inclui dados e metadados
    $array_resposta = [
        'resposta' => $array_dados,
        'metadados' => $array_meta
    ];

    // Codifica o array de resposta em JSON
    $json_data = json_encode($array_resposta);

    // Define o cabeçalho da resposta como JSON e envia a resposta
    header('Content-Type: application/json');
    echo $json_data;
    http_response_code(201);
} else {
    // Se não houver dados, retorna uma resposta de erro (501 - Not Implemented)
    http_response_code(501);
}

// Fecha a conexão com o banco de dados
$conn->close();