<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Inclui o arquivo que cria a conexão com o banco de dados
include 'conexao.php';

class ObterDadosTecnica {
    private $conn;
    private $table_name = "informacoes_circuito";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getFiltros($data) {
        $query_filters = "SELECT informacoes_circuito.solicitante, informacoes_circuito.circuito, informacoes_circuito.status_circuito,
            comercial.modalidade_migracao, comercial.banda, informacoes_circuito.tipo, endereco.uf, endereco.municipio, informacoes_circuito.status_detalhamento
        FROM " . $this->table_name . "
        LEFT JOIN comercial ON informacoes_circuito.circuito = comercial.circuito
        LEFT JOIN ordem_servico ON informacoes_circuito.os = ordem_servico.os
        LEFT JOIN contato ON comercial.endereco = contato.endereco
        LEFT JOIN endereco ON contato.endereco = endereco.endereco_id";

        $filters = [];
        $page_size = isset($data['tamanho_pagina']) ? $data['tamanho_pagina'] : 10;
        $page = isset($data['page']) ? $data['page'] : 1;
        $busca = isset($data['busca']) ? $data['busca'] : "";

        if (!empty($data['circuito'])) {
            $filters[] = "informacoes_circuito.circuito = '" . $this->conn->real_escape_string($data['circuito']) . "'";
        }
        if (!empty($data['contrato'])) {
            $filters[] = "comercial.contrato = '" . $this->conn->real_escape_string($data['contrato']) . "'";
        }
        if (!empty($data['status_mcom'])) {
            $filters[] = "informacoes_circuito.status_circuito = '" . $this->conn->real_escape_string($data['status_mcom']) . "'";
        }
        if (!empty($data['status_tlb'])) {
            $filters[] = "informacoes_circuito.status_tlb = '" . $this->conn->real_escape_string($data['status_tlb']) . "'";
        }
        if (!empty($data['remessa'])) {
            $filters[] = "informacoes_circuito.remessa = '" . $this->conn->real_escape_string($data['remessa']) . "'";
        }
        if (!empty($data['modalidade'])) {
            $filters[] = "comercial.modalidade_migracao = '" . $this->conn->real_escape_string($data['modalidade']) . "'";
        }
        if (!empty($data['uf'])) {
            $filters[] = "endereco.uf = '" . $this->conn->real_escape_string($data['uf']) . "'";
        }
        if (!empty($data['municipio'])) {
            $filters[] = "endereco.municipio = '" . $this->conn->real_escape_string($data['municipio']) . "'";
        }
        if (!empty($data['solicitante'])) {
            $filters[] = "informacoes_circuito.solicitante = '" . $this->conn->real_escape_string($data['solicitante']) . "'";
        }
        if (!empty($data['tipologia'])) {
            $filters[] = "informacoes_circuito.tipo = '" . $this->conn->real_escape_string($data['tipologia']) . "'";
        }

        if (!empty($filters)) {
            $query_filters .= " WHERE " . implode(" AND ", $filters);
        } else {
            $query_filters .= " WHERE informacoes_circuito.solicitante LIKE CONCAT('%', ?, '%')
                            OR informacoes_circuito.circuito LIKE CONCAT('%', ?, '%')
                            OR informacoes_circuito.fornecedor LIKE CONCAT('%', ?, '%')";
        }

        $query_filters .= " LIMIT " . $page_size . " OFFSET " . ($page - 1) * $page_size;

        $stmt = $this->conn->prepare($query_filters);

        if (empty($filters)) {
            $stmt->bind_param("sss", $busca, $busca, $busca);
        }

        $stmt->execute();
        $result_filters = $stmt->get_result();

        if (!$result_filters) {
            die("Query failed: " . $this->conn->error);
        }

        $rows_filters = [];
        while ($row_filter = $result_filters->fetch_assoc()) {
            $rows_filters[] = $row_filter;
        }

        $count_query = "SELECT COUNT(*) as count FROM " . $this->table_name . "
        LEFT JOIN comercial ON informacoes_circuito.circuito = comercial.circuito
        LEFT JOIN ordem_servico ON informacoes_circuito.os = ordem_servico.os
        LEFT JOIN contato ON comercial.endereco = contato.endereco
        LEFT JOIN endereco ON contato.endereco = endereco.endereco_id
        " . ($filters ? " WHERE " . implode(" AND ", $filters) : 
        " WHERE informacoes_circuito.solicitante LIKE CONCAT('%', ?, '%')
        OR informacoes_circuito.circuito LIKE CONCAT('%', ?, '%')
        OR informacoes_circuito.fornecedor LIKE CONCAT('%', ?, '%')");

        $count_stmt = $this->conn->prepare($count_query);

        if (empty($filters)) {
            $count_stmt->bind_param("sss", $busca, $busca, $busca);
        }

        $count_stmt->execute();
        $count_result = $count_stmt->get_result();

        if (!$count_result) {
            die("Count query failed: " . $this->conn->error);
        }

        $count_row = $count_result->fetch_assoc();
        $total_rows = $count_row['count'];

        return [
            'rows' => $rows_filters,
            'total_rows' => $total_rows
        ];
    }
    
    public function getDistinctValues($table, $column) {
        $query = "SELECT DISTINCT $column FROM $table";
        $result = $this->conn->query($query);
        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }

        $values = [];
        while ($row = $result->fetch_assoc()) {
            $values[] = $row[$column];
        }
        return $values;
    }
}

// Cria uma instância da classe Database
$database = new Database();
$conn = $database->getConnection();

// Cria uma instância da classe ObterDadosTecnica
$script = new ObterDadosTecnica($conn);

// Obtém e decodifica o JSON enviado via POST
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Obtém os filtros e dados
$filtros_dados = $script->getFiltros($data);
$rows_filters = $filtros_dados['rows'];
$total_rows = $filtros_dados['total_rows'];

if (count($rows_filters) > 0) {
    $dados = $rows_filters;
    $array_solicitantes = array();

    foreach ($dados as $item) {
        $solicitante = $item['solicitante'];
        unset($item['solicitante']); // Remove o solicitante dos dados

        if (!isset($array_solicitantes[$solicitante])) {
            $array_solicitantes[$solicitante] = array();
        }
        $array_solicitantes[$solicitante][] = $item;
    }

    $filtros = array(
        'circuito' => $script->getDistinctValues('informacoes_circuito', 'circuito'),
        'contrato' => $script->getDistinctValues('comercial', 'contrato'),
        'status_mcom' => $script->getDistinctValues('informacoes_circuito', 'status_circuito'),
        'status_tlb' => $script->getDistinctValues('informacoes_circuito', 'status_tlb'),
        'remessa' => $script->getDistinctValues('informacoes_circuito', 'remessa'),
        'modalidade' => $script->getDistinctValues('comercial', 'modalidade_migracao'),
        'uf' => $script->getDistinctValues('endereco', 'uf'),
        'municipio' => $script->getDistinctValues('endereco', 'municipio'),
        'solicitante' => $script->getDistinctValues('informacoes_circuito', 'solicitante'),
        'tipologia' => $script->getDistinctValues('informacoes_circuito', 'tipo')
    );

    $response = array(
        'resposta' => array(
            'dados' => $array_solicitantes
        ),
        'metadados' => array(
            'filtros' => $filtros,
            'contagem' => $total_rows
        )
    );
} else {
    $response = array('message' => 'Nenhum resultado encontrado');
}

header('Content-Type: application/json');
echo json_encode($response);