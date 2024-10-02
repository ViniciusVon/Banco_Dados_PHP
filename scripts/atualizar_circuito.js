// Importa funções do arquivo functions.js
import { openCard, changeLimit, filtrar, carregarDados } from './functions.js';

const apiEndpoint = {
    method: 'POST',
    url: '../backend/obter_dados_atualizar_circuito.php' // Endpoint para obter dados de atualização de circuito
};

// Adiciona um evento que é executado quando o DOM é completamente carregado
document.addEventListener("DOMContentLoaded", function() {
    // Obtém o nome do arquivo HTML atual, sem a extensão
    const urlName = window.location.pathname.split('/').pop().replace('.html', '');

    // Configura o evento de clique para o botão de busca
    document.querySelector(".search-button").addEventListener("click", function(event) {
        event.preventDefault(); // Evita o comportamento padrão do link
        carregarDados(apiEndpoint, urlName); // Chama a função para carregar dados com o endpoint e o nome da página
    });

    // Configura o evento de tecla pressionada para o campo de texto de busca
    document.querySelector(".search-txt").addEventListener("keypress", function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Evita o comportamento padrão do Enter
            carregarDados(apiEndpoint, urlName); // Chama a função para carregar dados
        }
    });

    // Configura o evento de mudança para o seletor de limite de página
    document.querySelector("#selectLimit").addEventListener("change", function() {
        changeLimit(this, apiEndpoint); // Chama a função para mudar o limite de página
    });

    // Seleciona todos os elementos com a classe .field-filtro
    const elementosFiltro = document.querySelectorAll(".field-filtro");

    // Itera sobre cada elemento com a classe .field-filtro
    elementosFiltro.forEach(function(elemento) {
        // Adiciona um evento de mudança a cada elemento
        elemento.addEventListener("change", function() {
            filtrar(apiEndpoint); // Chama a função para aplicar filtros
        });
    });
});

// Adiciona um evento que é executado quando a janela é carregada
window.onload = function() {
    // Obtém o token da sessão e do armazenamento local
    let token = sessionStorage.getItem("token");
    let token_local = localStorage.getItem("token");
  
    // Se um token for encontrado em qualquer um dos armazenamentos
    if (token || token_local) {
        // Obtém o nome do arquivo HTML atual, sem a extensão
        const urlName = window.location.pathname.split('/').pop().replace('.html', '');
        carregarDados(apiEndpoint, urlName); // Chama a função para carregar dados
    } else {
        // Se nenhum token for encontrado, redireciona para a página de login
        window.location.href = '../index.html'; 
    }
};