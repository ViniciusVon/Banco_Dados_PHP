<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Adiciona cabeçalhos CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "conexao.php";
require_once "./jwt_tokens/funcoes_auxiliares.php";
require_once "./jwt_tokens/jwt.php";

class LDAPAuthenticator {
    private $db;
    private $ldapHost = "HOST"; //Host Especificado
    private $baseDN = "Base Especificada"; // Base Especificada
    private $perfilUsuario = 1;
    private $chaveSecreta = 'ChaveSecreta'; // Chave Secreta

    public function __construct() {
        $this->db = new Database();
    }

    public function authenticate($username, $password) {
        // Conecta ao LDAP
        $ldapUser = $username . "FinaldoEmail";
        $ldapPsw = $password;

        $link = ldap_connect($this->ldapHost);
        if (!$link) {
            throw new Exception("Could not connect to LDAP server.");
        }

        ldap_set_option($link, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($link, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($link, $ldapUser, $ldapPsw);
        if ($bind) {
            $this->handleUser($username);
            $token = $this->generateTokenJWT($username);
            return $token;
        } else {
            http_response_code(406);
            throw new Exception("Invalid credentials.");
        }
    }

    private function handleUser($username) {
        $conn = $this->db->getConnection();
        $sqlSelect = "SELECT * FROM usuarios WHERE user_id = ?";
        $stmt = $conn->prepare($sqlSelect);
        if ($stmt === false) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $sqlInsert = "INSERT INTO usuarios (user_id, perfil) VALUES (?, ?)";
            $stmt = $conn->prepare($sqlInsert);
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("si", $username, $this->perfilUsuario);
            if (!$stmt->execute()) {
                throw new Exception("Error inserting record: " . $conn->error);
            }
        }
    }

    private function generateTokenJWT($username) {
        $dadosToken = array(
            "user" => $username,
            "id_sequencial" => $this->generateSequencial(),
            "data_hora" => $this->dataHora()
        );

        $token = JWT::encode($dadosToken, $this->chaveSecreta);
        return $token;
    }

    private function generateSequencial() {
        return rand(1000, 9999); // Exemplo simples
    }

    private function dataHora() {
        return date('Y-m-d H:i:s');
    }
}

// Recebe o JSON e faz a autenticação
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $postJson = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE) {
        if (isset($postJson['username']) && isset($postJson['password'])) {
            $authenticator = new LDAPAuthenticator();
            try {
                $token = $authenticator->authenticate($postJson['username'], $postJson['password']);
                echo json_encode(["token" => $token]);
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(["error" => "Error: " . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Invalid input."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Invalid JSON format."]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Invalid request method."]);
}