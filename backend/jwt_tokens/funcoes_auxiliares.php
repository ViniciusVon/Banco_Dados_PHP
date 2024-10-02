<?php

// Jwt Token
    // Componentes do Token
        function gerarSequencial() { // Fução para gerar um código para o token
            $numeroAleatorio = mt_rand(0, 100000000000); // Gera um número sequencial
            $hexadecimal = dechex($numeroAleatorio);  // Converte o número aleatório para hexadecimal
            $minuscula = strtolower($hexadecimal); // Converte o hexadecimal para letras minúsculas
            return $minuscula; // Retorna o código do token como um sequencial hexadecimal minúsculo
        }

        function dataHora() { // Função para gerar a data e hora da emissão do token
            return date('Y-m-d H:i:s'); // Retorna o resultado da função
        }

    // 
    
    // Função para buscar o nome do usuário pelo ID
    include 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados

    function getUserNameById($userId) {
        global $conn;
        $sql = "SELECT nome FROM contato WHERE contato_id = $userId";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $userName = $row['nome'];
            return $userName;
        } else {
            return "Usuário não encontrado";
        }
    }

    define('TEMPO_VALIDADE', 180);
    // Função para verificar o status e a data de criação do token
    function verificarStatusToken($conn, $token, $status, $createdat){
        $sql = "SELECT status, created_at FROM jwt_tokens WHERE token = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die('Erro ao preparar a consulta: ' . htmlspecialchars($conn->error));
        }
        $stmt->bind_param("s", $token);
        if ($stmt->execute()) {
            $stmt->bind_result($statusEncontrado, $dtCriacaoEncontrada); // Corrigido
            $stmt->fetch();
            echo "Status do token: $statusEncontrado\n";
            echo "Data de criação do token: $dtCriacaoEncontrada\n";
            if ($statusEncontrado == "valido") {
                // Verificar se o token está expirado
                $diferencaEmDias = calcularDiferencaDias($dtCriacaoEncontrada);
                if ($diferencaEmDias >= TEMPO_VALIDADE) {
                    // Muda o status do Token no banco para invalido
                    $stmt_update = $conn->prepare("UPDATE jwt_tokens SET status = 'invalido' WHERE token = ?");
                    if ($stmt_update === false) {
                        die('Erro ao preparar a consulta de atualização: ' . htmlspecialchars($conn->error));
                    }
                    $stmt_update->bind_param("s", $token);
                    if ($stmt_update->execute()) {
                        echo "Token expirado. Status alterado para inválido.\n";
                    } else {
                        echo "Erro ao atualizar status do token: " . htmlspecialchars($stmt_update->error) . "\n";
                    }
                    $stmt_update->close();
                }
            }
        } else {
            echo "Erro ao executar a consulta: " . htmlspecialchars($stmt->error) . "\n";
        }
        $stmt->close();
    }

    // Função para calcular a diferença de dias entre a data de criação e a data atual
    function calcularDiferencaDias($dtCriacaoEncontrada) {
        $dataAtual = date("Y-m-d");
        $dtCriacaoTimestamp = strtotime($dtCriacaoEncontrada);
        $dataAtualTimestamp = strtotime($dataAtual);
        $diferencaEmSegundos = $dataAtualTimestamp - $dtCriacaoTimestamp;
        $diferencaEmDias = floor($diferencaEmSegundos / (60 * 60 * 24));
        return $diferencaEmDias;
    }
