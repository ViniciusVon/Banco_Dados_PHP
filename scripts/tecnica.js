// Importa funções do arquivo functions.js
import { openCard, filtrar, changeLimit, carregarDados } from './functions.js';

const apiEndpoint = {
    method: 'POST',
    url: '../backend/obter_dados_tecnica.php' // Endpoint para obter dados técnicos
};

document.addEventListener("DOMContentLoaded", function() {
    const urlName = window.location.pathname.split('/').pop().replace('.html', '');

    document.querySelector(".search-button").addEventListener("click", function(event) {
        event.preventDefault(); // Evita o comportamento padrão do link
        carregarDados(apiEndpoint, urlName); // Chama a função para carregar dados com o endpoint e o nome da página
    });

    document.querySelector(".search-txt").addEventListener("keypress", function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Evita o envio do formulário ao pressionar Enter
            carregarDados(apiEndpoint, urlName); // Chama a função para carregar dados
        }
    });

    document.querySelector("#selectLimit").addEventListener("change", function() {
        changeLimit(this, apiEndpoint); // Chama a função para mudar o limite de página
    });

    const elementosFiltro = document.querySelectorAll(".field-filtro");

    elementosFiltro.forEach(function(elemento) {
        // Adiciona um evento de mudança a cada elemento
        elemento.addEventListener("change", function() {
            filtrar(apiEndpoint); // Chama a função para aplicar filtros
        });
    });
});

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