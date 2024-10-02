<?php
require_once 'jwt.php'; // Inclui a classe JWT
require_once 'blocklist.php'; // Inclui as funções da blocklist
require_once 'funcoes_auxiliares.php'; // Inclui as funções auxiliares
require_once 'conexao.php'; // Inclui o arquivo de conexão com o Banco de Dados

// Função para armazenar o token no banco de dados
function armazenarBd($conn, $token) {
    // Verifica se o token já existe no banco de dados
    $stmt = $conn->prepare("SELECT COUNT(*) FROM jwt_tokens WHERE token = ?");
    if ($stmt === false) {
        die('Erro ao preparar a consulta: ' . htmlspecialchars($conn->error));
    }
    $stmt->bind_param("s", $token);
    if ($stmt->execute()) {
        $result = $stmt->get_result;
        $count = array_values($result->fetch_assoc())[0];

        if ($count > 0) {
            // Token já existe, verificar status e data de criação
            verificarStatusToken($conn, $token, $status, $createdat, $statusEncontrado, $dtCriacaoEncontrada);
            // Token não existe, inserir no banco de dados
            $stmt_insert = $conn->prepare("INSERT INTO jwt_tokens (token, status, created_at) VALUES (?, ?, ?)");
            if ($stmt_insert === false) {
                die('Erro ao preparar a consulta de inserção: ' . htmlspecialchars($conn->error));
            } else{
                $stmt_insert->bind_param("sss", $token, $status, $createdat);
                if ($stmt_insert->execute()) {
                    echo "Token armazenado com sucesso.\n";
                } else {
                    echo "Erro ao armazenar token: " . htmlspecialchars($stmt_insert->error) . "\n";
                }
                $stmt_insert->close();
            }
        }

        //Aqui precisamos fazer o insert do token no bd

        
    } else {
        echo "Erro ao executar a consulta: " . htmlspecialchars($stmt->error) . "\n";
    }
    $stmt->close();
}