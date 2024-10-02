<?php
// Inclui o arquivo de conexão com o banco de dados
include 'conexao.php';

// Cria uma instância da classe Database
$database = new Database();
$conn = $database->getConnection();

// Define um dicionário de acessos que mapeia IDs de perfil para descrições de perfil
$dicionarioAcessos = array(
    1 => 'Usuário',        // ID 1 mapeado para 'Usuário'
    2 => 'Administrador',  // ID 2 mapeado para 'Administrador'
    3 => 'Editor',         // ID 3 mapeado para 'Editor'
);

// Define a query SQL para selecionar IDs de usuário e perfil da tabela 'usuarios'
$sql = "SELECT user_id, perfil FROM `usuarios`";

// Executa a query no banco de dados
$result = $conn->query($sql);

// Verifica se a consulta retornou algum resultado
if ($result->num_rows > 0) {
    $dados = array(); // Array para armazenar os dados retornados

    // Itera sobre cada linha de resultado
    while ($row = $result->fetch_assoc()){
        // Verifica se o valor do perfil existe no dicionário de acessos
        if (array_key_exists($row['perfil'], $dicionarioAcessos)){
            // Substitui o ID do perfil pela descrição correspondente do dicionário
            $row['perfil'] = $dicionarioAcessos[$row['perfil']];
        }
        // Adiciona a linha processada ao array de dados
        $dados[] = $row;
    }
} else {
    // Se não houver resultados, inicializa o array de dados como vazio
    $dados = [];
}

// Converte o array de dados para formato JSON e exibe
echo json_encode($dados);

// Fecha a conexão com o banco de dados
$conn->close();