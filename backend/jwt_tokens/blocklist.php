<?php
include("conexao.php");

// Verificar a existência do token no Banco de Dados
function verificarExistencia($token) {
    global $conn;

}

// Função para adicionar o status invalido ao arquivo
function bloquearToken($token) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO tokens_invalidos (token) VALUES (?)");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();
    } else {
        die("Erro na preparação da consulta: " . $conn->error);
    }
}

// Função para verificar se um token está na blocklist
function isTokenBlocklisted($token) {
    global $conn;
    $stmt = $conn->prepare("SELECT 1 FROM tokens_invalidos WHERE token = ?");
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        $is_blocked = $stmt->num_rows > 0;
        $stmt->close();
        return $is_blocked;
    } else {
        die("Erro na preparação da consulta: " . $conn->error);
    }
}