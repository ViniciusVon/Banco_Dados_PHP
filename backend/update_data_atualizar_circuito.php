<?php
    // Configurações para exibir todos os erros e avisos durante a execução
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Inclui o arquivo de conexão com o banco de dados
    include("conexao.php");

    // Verifica se houve erro na conexão com o banco de dados
    if ($conn->connect_error) {
        die("Conexão falhou: " . $conn->connect_error); // Encerra o script e exibe uma mensagem de erro se a conexão falhar
    }

    // Lê os dados JSON enviados via POST e decodifica para um array associativo
    $json = file_get_contents('php://input'); // Obtém o conteúdo do corpo da requisição POST
    $post_json = json_decode($json, true); // Decodifica o JSON para um array associativo

    // Construção da query SQL com limite para paginação
    $sql_query_select .= " LIMIT $offset, $page_size"; // Adiciona o limite de registros à query SQL (offset e page_size precisam estar definidos)

    // Executa a consulta no banco de dados
    $result = $conn->query($sql_query_select); // Executa a query SQL e armazena o resultado

    // Recupera os dados da consulta
    $rows = []; // Inicializa um array para armazenar os resultados
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row; // Adiciona cada linha de resultado ao array $rows
    }

    // Prepara a resposta em formato JSON
    $response = [
        'resposta' => [
            'dados' => $rows, // Inclui os dados obtidos na resposta
            'metadados' => [
                //'contagem' => $total_rows // Comentado: pode ser usado para incluir a contagem total de registros
            ]
        ]
    ];

    // Define o tipo de conteúdo como JSON e retorna a resposta codificada em JSON
    header('Content-Type: application/json'); // Define o cabeçalho da resposta como JSON
    echo json_encode($response); // Codifica o array $response em JSON e o envia para o cliente

    // Fecha a conexão com o banco de dados
    $conn->close(); // Encerra a conexão com o banco de dados