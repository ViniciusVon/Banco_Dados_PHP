<?php
// Configura a exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

// Cria uma instância da classe Database e obtém a conexão
$database = new Database();
$conn = $database->getConnection();

class ObterDadosAtualizarCircuito {
    private $conn;
    private $page_size;
    private $page;
    private $busca;
    private $filters = [];

    public function __construct($conn, $data){
        $this->conn = $conn;
        $this->page_size = isset($data['tamanho_pagina']) ? $data['tamanho_pagina'] : 10;
        $this->page = isset($data['page']) ? $data['page'] : 1;
        $this->busca = isset($data['busca']) ? $data['busca'] : "";
        $this->setFilters($data);
    }

    private function setFilters($data){
        $fields = [
            'circuito' => 'informacoes_circuito.circuito',
            'contrato' => 'comercial.contrato',
            'status_mcom' => 'informacoes_circuito.status_circuito',
            'status_tlb' => 'informacoes_circuito.status_tlb',
            'remessa' => 'informacoes_circuito.remessa',
            'modalidade' => 'comercial.modalidade_migracao',
            'uf' => 'endereco.uf',
            'municipio' => 'endereco.municipio',
            'solicitante' => 'informacoes_circuito.solicitante',
            'tipologia' => 'informacoes_circuito.tipo'
        ];

        foreach ($fields as $key => $field) {
            if (!empty($data[$key])) {
                $this->filters[] = "$field = '" . $this->conn->real_escape_string($data[$key]) . "'";
            }
        }
    }

    private function buildQuery(){
        $query = "SELECT informacoes_circuito.circuito, comercial.contrato, informacoes_circuito.status_circuito, informacoes_circuito.status_tlb,
                  informacoes_circuito.remessa, comercial.modalidade_migracao, endereco.uf, informacoes_circuito.solicitante,
                  informacoes_circuito.tipo
                  FROM informacoes_circuito
                  LEFT JOIN comercial ON informacoes_circuito.circuito = comercial.circuito
                  LEFT JOIN ordem_servico ON informacoes_circuito.os = ordem_servico.os
                  LEFT JOIN contato ON comercial.endereco = contato.endereco
                  LEFT JOIN endereco ON contato.endereco = endereco.endereco_id";

        if (!empty($this->filters)) {
            $query .= " WHERE " . implode(" AND ", $this->filters);
        } else {
            $query .= " WHERE informacoes_circuito.solicitante LIKE CONCAT('%', ?, '%')
                        OR informacoes_circuito.circuito LIKE CONCAT('%', ?, '%')
                        OR informacoes_circuito.fornecedor LIKE CONCAT('%', ?, '%')";
        }

        $query .= " LIMIT " . $this->page_size . " OFFSET " . ($this->page - 1) * $this->page_size;

        return $query;
    }

    public function getDados(){
        $query = $this->buildQuery();
        $stmt = $this->conn->prepare($query);

        if (empty($this->filters)) {
            $stmt->bind_param("sss", $this->busca, $this->busca, $this->busca);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            die("Query failed: " . $this->conn->error);
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    public function getCount(){
        $query = "SELECT COUNT(*) as count FROM informacoes_circuito
                  LEFT JOIN comercial ON informacoes_circuito.circuito = comercial.circuito
                  LEFT JOIN ordem_servico ON informacoes_circuito.os = ordem_servico.os
                  LEFT JOIN contato ON comercial.endereco = contato.endereco
                  LEFT JOIN endereco ON contato.endereco = endereco.endereco_id";

        if (!empty($this->filters)) {
            $query .= " WHERE " . implode(" AND ", $this->filters);
        } else {
            $query .= " WHERE informacoes_circuito.solicitante LIKE CONCAT('%', ?, '%')
                        OR informacoes_circuito.circuito LIKE CONCAT('%', ?, '%')
                        OR informacoes_circuito.fornecedor LIKE CONCAT('%', ?, '%')";
        }

        $stmt = $this->conn->prepare($query);

        if (empty($this->filters)) {
            $stmt->bind_param("sss", $this->busca, $this->busca, $this->busca);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if (!$result) {
            die("Count query failed: " . $this->conn->error);
        }

        $row = $result->fetch_assoc();
        return $row['count'];
    }

    public function getDistinctValues($table, $column){
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

// Cria uma instância da classe ObterDadosAtualizarCircuito
$data = json_decode(file_get_contents('php://input'), true);
$dadosComercial = new ObterDadosAtualizarCircuito($conn, $data);

// Obtém os dados e a contagem total
$dados = $dadosComercial->getDados();
$total_rows = $dadosComercial->getCount();

// Obtém os valores distintos para os filtros
$filters_array = [
    'circuito' => $dadosComercial->getDistinctValues('informacoes_circuito', 'circuito'),
    'contrato' => $dadosComercial->getDistinctValues('comercial', 'contrato'),
    'status_mcom' => $dadosComercial->getDistinctValues('informacoes_circuito', 'status_circuito'),
    'status_tlb' => $dadosComercial->getDistinctValues('informacoes_circuito', 'status_tlb'),
    'remessa' => $dadosComercial->getDistinctValues('informacoes_circuito', 'remessa'),
    'modalidade' => $dadosComercial->getDistinctValues('comercial', 'modalidade_migracao'),
    'uf' => $dadosComercial->getDistinctValues('endereco', 'uf'),
    'municipio' => $dadosComercial->getDistinctValues('endereco', 'municipio'),
    'solicitante' => $dadosComercial->getDistinctValues('informacoes_circuito', 'solicitante'),
    'tipologia' => $dadosComercial->getDistinctValues('informacoes_circuito', 'tipo')
];

// Prepara a resposta
$response = [
    'resposta' => ['dados' => $dados],
    'metadados' => [
        'contagem' => $total_rows,
        'filtros' => $filters_array
    ]
];

// Define o tipo de conteúdo como JSON e envia a resposta
header('Content-Type: application/json');
echo json_encode($response);

// Não há necessidade de fechar a conexão manualmente
// $conn->close();