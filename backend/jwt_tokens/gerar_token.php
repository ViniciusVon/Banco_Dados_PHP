<?php
require_once './jwt.php'; // Inclui a classe JWT
require_once './funcoes_auxiliares.php'; // Inclui as funções auxiliares
include('../conexao.php'); // Inclui o arquivo de conexão com o Banco de Dados

function gerarTokenJWT() {
  // Chave secreta
  $chave_secreta = 'Am0G4t1nh0s';

  // Dados do token
  $dados_token = array(
      "user" => "User_name",
      "id_sequencial" => gerarSequencial(),
      "data_hora" => dataHora()
  );

  // Gera o token
  $token = JWT::encode($dados_token, $chave_secreta);

  // Retorna o token gerado
  return $token;
}

function validarTokenJWT($token, $chave_secreta) {
  $payload = JWT::decode($token, $chave_secreta);
  if ($payload) {
      return $payload;
  } else {
      return false;
  }
}

// // Exemplo de uso
// $token = gerarTokenJWT();
// echo "Token gerado: $token\n\n";

// $payload = validarTokenJWT($token, 'Am0G4t1nh0s');
// if ($payload) {
//   echo "Token válido: ";
//     print_r($payload);
// } else {
//   echo "Token inválido ou na blacklist.\n";
// }

