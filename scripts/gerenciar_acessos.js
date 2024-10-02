// Dicionario para fazer a troca quando clicada a caneta
const dicionarioAcessos = {
  'Usuário': 'Administrador',
  'Administrador': 'Editor',
  'Editor': 'Usuário'
};

// Dicionario de perfil dos usuários
const perfilParaNumero = {
  'Usuário': 1,
  'Administrador': 2,
  'Editor': 3
};

window.onload = function() {
  // Verifica se o usuário é elegível para acessar a página
  let token = sessionStorage.getItem("token");
  let token_local = localStorage.getItem("token");

  if (token || token_local) {
    const urlName = window.location.pathname.split('/').pop().replace('.html', '');
  } else {
    window.location.href = '../index.html';
  }

  if(sessionStorage.getItem('can_edit')){
    loadData();
  }
};

// Função para carregar os dados
function loadData() {
  fetch('../backend/gerenciar_acessos.php')
  .then(response => response.json())
  .then(data => {
    const tbody = document.querySelector('#data-output');
    const canEdit = sessionStorage.getItem("can_edit") === 'true'; // Verifica a permissão armazenada

    showLoader();

    data.forEach(item => {
      const tr = document.createElement('tr');

      tr.innerHTML = `
        <td>${item.user_id}</td>
        <td>
          <span class="perfil">${item.perfil}</span>
          ${canEdit ? '<img src="../images/btn_caneta.png" class="btn_caneta" alt="Símbolo de uma caneta">' : ''}
        </td>
      `; // Adiciona uma imagem se o usuário tiver perfil de edição
      tbody.appendChild(tr);
    });

    hideLoader();
  })
  .catch(error => {
    console.error('Erro:', error);
    hideLoader();
  });
}

// Função para mostrar o ícone de carregamento
function showLoader() {
  document.getElementById("loader").style.display = "block";
}

// Função para esconder o ícone de carregamento
function hideLoader() {
  document.getElementById("loader").style.display = "none";
}

document.addEventListener('DOMContentLoaded', (event) => {
  const tbody = document.querySelector('#data-output'); // Body da Tabela
  const saveButton = document.getElementById('salvar'); // Botão de salvar
  let updatedRows = []; // Variável para armazenar as linhas atualizadas

  tbody.addEventListener('click', (event) => {
      if (event.target.classList.contains('btn_caneta')) {
          saveButton.style.display = 'inline-block'; // Mostra o botão de salvar

          const tr = event.target.closest('tr');
          const perfilElement = tr.querySelector('.perfil'); // Elemento que contém o perfil
          const currentPerfil = perfilElement.textContent;

          // Alterna o tipo de perfil usando o dicionário
          perfilElement.textContent = dicionarioAcessos[currentPerfil];

          const userId = tr.querySelector('td').textContent;
          const updatedPerfil = perfilElement.textContent;
          const perfilNumero = perfilParaNumero[updatedPerfil]; // Obtém o número relativo ao perfil

          
          updatedRows.push({ user_id: userId, perfil: perfilNumero }); // Adiciona a linha atualizada à lista de atualizações
      }
  });

  // Adiciona evento de clique ao botão de salvar
  saveButton.addEventListener('click', () => {
      showLoader();

      fetch('../backend/update_acessos.php', {
          method: 'POST',
          headers: {
              'Content-Type': 'application/json',
          },
          body: JSON.stringify({ rows: updatedRows }),
      })
      .then(response => response.json())
      .then(data => {
          saveButton.style.display = 'none';
          hideLoader();
          if (data.success) {
              alert('Perfis atualizados com sucesso!');
              updatedRows = []; // Limpa a lista de atualizações após salvar
          } else {
              alert('Erro ao atualizar os perfis.');
          }
      })
      .catch(error => {
          console.error('Erro:', error);
          hideLoader();
      });
  });
});

