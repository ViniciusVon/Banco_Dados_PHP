<?php
// Configurações para exibir todos os erros e avisos durante a execução
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui o arquivo de conexão com o banco de dados
include("conexao.php");

class CardUpdater {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function updateCard($dados_inputs) {
        if (!isset($dados_inputs['circuito'])) {
            return "Erro: Circuito não definido.";
        }

        $circuito = $dados_inputs['circuito'];
        $updates_ic = [];
        $updates_contato = [];
        $updates_endereco = [];

        foreach ($dados_inputs as $campo => $valor) {
            if ($campo !== 'circuito' && $valor !== null) {
                if (in_array($campo, ["nome", "telefone", "email"])) {
                    $updates_contato[] = "$campo = '{$this->conn->real_escape_string($valor)}'";
                } else if (in_array($campo, ["nome_estabelecimento", "tipo_estabelecimento", "solicitante", "uf", "municipio", "bairro", "cep", "logradouro", "numero", "complemento", "latitude", "longitude"])) {
                    $updates_endereco[] = "$campo = '{$this->conn->real_escape_string($valor)}'";
                } else {
                    $updates_ic[] = "$campo = '{$this->conn->real_escape_string($valor)}'";
                }
            }
        }

        $this->executeUpdate($updates_ic, 'informacoes_circuito', $circuito);
        $this->executeUpdate($updates_contato, 'contato', $circuito);
        $this->executeUpdate($updates_endereco, 'endereco', $circuito);

        return "Atualização concluída com sucesso.";
    }

    private function executeUpdate($updates, $table, $circuito) {
        if (count($updates) > 0) {
            $query = $this->buildUpdateQuery($table, $updates, $circuito);
            if ($query && !$this->conn->query($query)) {
                die("Erro ao executar a query $table: " . $this->conn->error);
            }
        }
    }

    private function buildUpdateQuery($table, $updates, $circuito) {
        // Construa a consulta de atualização dependendo da tabela
        switch ($table) {
            case 'informacoes_circuito':
                return "
                    UPDATE informacoes_circuito
                    LEFT JOIN comercial ON informacoes_circuito.circuito = comercial.circuito
                    LEFT JOIN ordem_servico ON informacoes_circuito.os = ordem_servico.os
                    SET " . implode(', ', $updates) . "
                    WHERE informacoes_circuito.circuito = '{$this->conn->real_escape_string($circuito)}'
                ";
            case 'contato':
                return "
                    UPDATE contato
                    LEFT JOIN endereco ON contato.endereco = endereco.endereco_id
                    LEFT JOIN comercial ON endereco.endereco_id = comercial.endereco
                    LEFT JOIN informacoes_circuito ON comercial.circuito = informacoes_circuito.circuito
                    SET " . implode(', ', $updates) . "
                    WHERE informacoes_circuito.circuito = '{$this->conn->real_escape_string($circuito)}'
                ";
            case 'endereco':
                return "
                    UPDATE endereco
                    LEFT JOIN contato ON endereco.endereco_id = contato.endereco
                    LEFT JOIN comercial ON endereco.endereco_id = comercial.endereco
                    LEFT JOIN informacoes_circuito ON comercial.circuito = informacoes_circuito.circuito
                    SET " . implode(', ', $updates) . "
                    WHERE informacoes_circuito.circuito = '{$this->conn->real_escape_string($circuito)}'
                ";
            default:
                return null;
        }
    }
}

// Inicializa a conexão com o banco de dados
$database = new Database();
$db = $database->getConnection();

// Inicializa a classe de atualização e executa a atualização dos dados
$cardUpdater = new CardUpdater($db);

// Lê o JSON enviado via POST e decodifica para um array associativo
$json = file_get_contents('php://input');
$post_json = json_decode($json, true);

// Executa a atualização e obtém o resultado
$resultado = $cardUpdater->updateCard($post_json['dadosInputs']);

// Envia a resposta
echo $resultado;

// Fecha a conexão com o banco de dados
//$db->close();