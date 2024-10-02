import { openCard, changeLimit, filtrar, carregarDados } from './functions.js';

const apiEndpoint = {
    method: 'POST',
    url: '../backend/obter_dados_home.php'
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

    // Event listener para a mudança de limite de página
    document.querySelector("#selectLimit").addEventListener("change", function() {
        changeLimit(this, apiEndpoint);
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

    // Event listener para o download da tabela
    document.querySelector(".btn__download").addEventListener("click", function(event){
        event.preventDefault();
        exportTableToExcel('tabela_home', 'Pagina Site Banco de Dados');
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

// Função para deixar funcional o botão de download da table
function exportTableToExcel(tableId, filename = '') {
    var tableSelect = document.getElementById(tableId);
    var dataType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    // Criar uma nova tabela clonada para exportar os dados
    var tableClone = tableSelect.cloneNode(true);
    tableClone.id = "tableClone"; // Definir um novo ID para evitar conflitos

    // Remove o que a gente nao quer da tabela clonada, como tags e essas coisas
    var elementsToRemove = tableClone.querySelectorAll('.btn__download, .marcador');
    elementsToRemove.forEach(function(element) {
        element.parentNode.removeChild(element);
    });

    // Pegar os dados da tabela e passar como JSON
    var tableData = XLSX.utils.table_to_book(tableClone, {sheet:"Sheet1"});

    // Nome do arquivo
    filename = filename ? filename + '.xlsx' : 'excel_data.xlsx';

    // Crair um blob do JSON com o MIME
    var wbout = XLSX.write(tableData, {bookType:'xlsx', bookSST:true, type: 'binary'});

    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
        return buf;
    }

    var blob = new Blob([s2ab(wbout)], {type: dataType});

    // criar um url para o blob
    var url = window.URL.createObjectURL(blob);

    // Criar um link para o download
    var downloadLink = document.createElement("a");

    document.body.appendChild(downloadLink);

    // Referenciar o href com a url
    downloadLink.href = url;

    // colocando o nome do arquivo para o nome que eu desejo
    downloadLink.download = filename;
    downloadLink.click();
}
