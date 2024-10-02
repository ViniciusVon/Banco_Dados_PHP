import { openCard, filtrar, carregarDados } from './functions.js';

const apiEndpoint = {
    method: 'POST',
    url: '../backend/obter_dados_historico_modificacoes.php'
};

document.addEventListener("DOMContentLoaded", function() {
    const urlName = window.location.pathname.split('/').pop().replace('.html', '');
    // Configurar os event listeners para os botões e campos de busca
    document.querySelector(".search-button").addEventListener("click", function(event) {
        event.preventDefault(); // Evita que o link seja seguido
        carregarDados(apiEndpoint, urlName);
    });

    document.querySelector(".search-txt").addEventListener("keypress", function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Evita que o link seja seguido
            carregarDados(apiEndpoint, urlName);
        }
    });

    // Seleciona todos os elementos com a classe .field-filtro
    const elementosFiltro = document.querySelectorAll(".field-filtro");

    // Itera sobre cada elemento selecionado
    elementosFiltro.forEach(function(elemento) {
        // Adiciona o evento de clique a cada elemento
        elemento.addEventListener("change", function() {
            filtrar(apiEndpoint);
        });
    });
});

window.onload = function() {
    // Verifica se o usuário é elegível para acessar a página
    let token = sessionStorage.getItem("token");
    let token_local = localStorage.getItem("token");
  
    if (token || token_local) {
      // Criação dessa variável para a paginação apontar para a página correta
      const urlName = window.location.pathname.split('/').pop().replace('.html', '');
      carregarDados(apiEndpoint, urlName);
    } else {
      window.location.href = '../index.html'; 
    }
};