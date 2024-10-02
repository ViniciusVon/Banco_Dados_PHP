<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("conexao.php");

class DadosCard{
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    public function obterDados($circuito){
        // Consulta para obter informações principais relacionadas ao circuito
        $sql_informacoes_principais = "SELECT comercial.contrato, informacoes_circuito.circuito_antigo, informacoes_circuito.circuito, 
            informacoes_circuito.id_gesac_antigo, informacoes_circuito.id_gesac_wfb, ordem_servico.tipo_de_os, 
            informacoes_circuito.status_circuito, informacoes_circuito.status_tlb, informacoes_circuito.remessa,
            comercial.modalidade_migracao, comercial.banda
            FROM informacoes_circuito 
            LEFT JOIN comercial ON informacoes_circuito.circuito = comercial.circuito
            LEFT JOIN ordem_servico ON informacoes_circuito.os = ordem_servico.os
            WHERE informacoes_circuito.circuito = ?";

        // Prepara e executa a consulta principal
        $stmt_informacoes_principais = $this->conn->prepare($sql_informacoes_principais);
        $stmt_informacoes_principais->bind_param("s", $circuito);
        $stmt_informacoes_principais->execute();
        $result_informacoes_gerais = $stmt_informacoes_principais->get_result();

        // Armazena os dados da consulta principal
        $informacoes_gerais = [];
        while ($row = $result_informacoes_gerais->fetch_assoc()) {
            $informacoes_gerais[] = $row;
        }

        // Consulta para obter dados de contato relacionados ao circuito
        $sql_contato = "SELECT contato.nome, contato.telefone, contato.email
            FROM contato
            LEFT JOIN endereco ON contato.endereco = endereco.endereco_id
            LEFT JOIN comercial ON endereco.endereco_id = comercial.endereco
            LEFT JOIN informacoes_circuito ON comercial.circuito = informacoes_circuito.circuito
            WHERE informacoes_circuito.circuito = ?";

        // Prepara e executa a consulta de contato
        $stmt_contato = $this->conn->prepare($sql_contato);
        $stmt_contato->bind_param("s", $circuito);
        $stmt_contato->execute();
        $result_contato = $stmt_contato->get_result();

        // Armazena os dados de contato
        $contato = [];
        while ($row = $result_contato->fetch_assoc()) {
            $contato[] = $row;
        }

        // Consulta para obter dados de endereço relacionados ao circuito
        $sql_endereco = "SELECT endereco.nome_estabelecimento, endereco.tipo_estabelecimento,
            informacoes_circuito.solicitante, endereco.uf, endereco.municipio,
            endereco.bairro, endereco.cep, endereco.logradouro, endereco.numero, 
            endereco.complemento, endereco.latitude, endereco.longitude
            FROM endereco
            LEFT JOIN comercial ON endereco.endereco_id = comercial.endereco
            LEFT JOIN informacoes_circuito ON comercial.circuito = informacoes_circuito.circuito
            WHERE informacoes_circuito.circuito = ?";

        // Prepara e executa a consulta de endereço
        $stmt_endereco = $this->conn->prepare($sql_endereco);
        $stmt_endereco->bind_param("s", $circuito);
        $stmt_endereco->execute();
        $result_endereco = $stmt_endereco->get_result();

        // Armazena os dados de endereço
        $endereco = [];
        while ($row = $result_endereco->fetch_assoc()) {
            $endereco[] = $row;
        }

        // Prepara a resposta com os dados obtidos
        if ($result_informacoes_gerais->num_rows > 0 || $result_contato->num_rows > 0 || $result_endereco->num_rows > 0) {
            $array_titulos = [
                'info-gerais' => $informacoes_gerais, 
                'contato' => $contato,
                'endereco' => $endereco
            ];
            $array_resposta = [
                'resposta' => $array_titulos
            ];
            $json_data = json_encode($array_resposta);

            // Retorna os dados como resposta ao pedido AJAX
            header('Content-Type: application/json');
            echo $json_data;
        } else {
            // Se não houver dados, retorna uma resposta de erro
            http_response_code(501);
            echo json_encode(["message" => "Nenhum dado encontrado"]);
        }
    }
}

// Cria uma instância da classe Database e obtém a conexão
$database = new Database();
$conn = $database->getConnection();

// Instancia a classe DadosCard e obtém os dados
$dadosCard = new DadosCard($conn);

// Recebe o JSON enviado via POST e o decodifica
$json = file_get_contents('php://input');
$post_json = json_decode($json, true);

// Obtém o valor do parâmetro 'circuito' do JSON
$circuito = $post_json['circuito'];

// Chama o método para obter os dados
$dadosCard->obterDados($circuito);

// Fecha a conexão com o banco de dados
//$conn->close();