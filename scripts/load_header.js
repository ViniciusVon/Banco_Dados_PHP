// Obtém o nome de usuário armazenado no localStorage
const usernameRemembered = localStorage.getItem("username");
// Obtém o nome de usuário armazenado na sessão atual
let username = sessionStorage.getItem("username");

// Substitui o nome de usuário da sessão pelo lembrado se disponível
if (usernameRemembered) {
  username = usernameRemembered;
}

const text = username || "";
const meuArray = text.split(".");
const primeiroNome = meuArray[0]; // Obtém o primeiro nome

if (username) {
  document.getElementById("usernameDisplay").innerHTML = capitalizeFirstLetter(primeiroNome);
}


checkUserPermission().then(canEdit => {
  if (canEdit) {
    document.querySelector(".container_gerenciamento").style.display = 'block'; // Exibe o container de gerenciamento se o usuário tiver permissão
  }
});


document.querySelector(".cabecalho__logout").addEventListener("click", function(event) {
  event.preventDefault(); // Evita que o link de logout seja seguido

  sessionStorage.removeItem("token");
  sessionStorage.removeItem("username");
  localStorage.removeItem("token");
  localStorage.removeItem("username");

  window.location.href = '../index.html';
});

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase();
}

function checkUserPermission() {
  return new Promise((resolve, reject) => {
    const usuario = sessionStorage.getItem("username");

    $.ajax({
      url: '../backend/verificar_perfil_acesso.php', // URL do backend para verificar permissões
      method: 'POST', // Método da requisição
      data: JSON.stringify({ usuario }), // Dados enviados na requisição
      contentType: 'application/json', // Tipo de conteúdo da requisição
      dataType: 'json', // Tipo de dado esperado na resposta
      success: function(response) {
        //console.log('Resposta recebida:', response); // Adicione este log para depuração (opcional)
        if (response && typeof response.canEdit !== 'undefined') {
          sessionStorage.setItem("can_edit", response.canEdit); // Armazena a permissão de edição na sessão
          resolve(response.canEdit); // Resolve a promessa com o valor da permissão
        } else {
          console.error('Resposta inválida:', response); // Adiciona log de erro para resposta inválida
          reject('Resposta inválida'); // Rejeita a promessa se a resposta for inválida
        }
      },
      error: function(xhr, status, error) {
        console.error('Erro ao verificar permissões:', error); // Adiciona log de erro para falha na requisição
        reject(error); // Rejeita a promessa em caso de erro
      }
    });
  });
}