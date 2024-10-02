import { dicionario_status, dicionario_detalhamento, dicionario_modalidade_migracao, dicionario_status_smurfs, dicionario_log } from './dicionarios.js';
// functions.js

// Função para abrir o card de um circuito
export function openCard(circuito) {
    var target = "./card.html?c=circuito";
    window.open(target.replace("circuito", circuito), '_self');
}

// Função para alterar o limite da query
export function changeLimit(selectElement, apiEndpoint) {
    var selectedValue = selectElement.value;
    var limit;

    if (selectedValue === "outro") {
        limit = prompt("Digite o valor personalizado:");
        if (isNaN(limit) || limit <= 0) {
            alert("Por favor, insira um valor numérico válido.");
            return;
        }
    } else {
        limit = selectedValue;
    }
    let num_limit = parseInt(limit);
    updateQueryLimit(num_limit, apiEndpoint);
}

// Função para atualizar o limite da query
export function updateQueryLimit(limit, apiEndpoint) {
    let url = new URL(window.location);
    let params = new URLSearchParams(url.search);
    params.set('tamanho_pagina', limit);
    url.search = params.toString();
    window.location.href = url.toString();

    var xhr = new XMLHttpRequest();
    xhr.open(apiEndpoint.method, apiEndpoint.url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            carregarDados(apiEndpoint);
        }
    };
    xhr.send();
}

// Função para filtrar os dados
export function filtrar(apiEndpoint) {
    let circuito = document.querySelector("#circuito").value || "";
    let contrato = document.querySelector("#contrato").value || "";
    let status = document.querySelector("#status").value || "";
    let status_smurfs = document.querySelector("#status_smurfs").value || "";
    let remessa = document.querySelector("#remessa").value || "";
    let modalidade = document.querySelector("#modalidade").value || "";
    let uf = document.querySelector("#uf").value || "";
    let municipio = document.querySelector("#municipio").value || "";
    let solicitante = document.querySelector("#solicitante").value || "";
    let tipologia = document.querySelector("#tipologia").value || "";

    let url = new URL(window.location);
    let params = new URLSearchParams(url.search);

    if (circuito !== "") params.set('circuito', circuito);
    else params.delete('circuito');

    if (contrato !== "") params.set('contrato', contrato);
    else params.delete('contrato');

    if (status !== "") params.set('status', status);
    else params.delete('status');

    if (status_smurfs !== "") params.set('status_smurfs', status_smurfs);
    else params.delete('status_smurfs');

    if (remessa !== "") params.set('remessa', remessa);
    else params.delete('remessa');

    if (modalidade !== "") params.set('modalidade', modalidade);
    else params.delete('modalidade');

    if (uf !== "") params.set('uf', uf);
    else params.delete('uf');

    if (municipio !== "") params.set('municipio', municipio);
    else params.delete('municipio');

    if (solicitante !== "") params.set('solicitante', solicitante);
    else params.delete('solicitante');

    if (tipologia !== "") params.set('tipologia', tipologia);
    else params.delete('tipologia');

    window.history.replaceState({}, '', `${url.pathname}?${params}`);

    const xhr = new XMLHttpRequest();
    xhr.open(apiEndpoint.method, apiEndpoint.url, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
            carregarDados(apiEndpoint);
        }
    };
    xhr.send();
}

// Função para montar a tabela com os dados
export function montarTabela(dados_completos) {
    var corpo = document.getElementById("data-output");
    corpo.innerHTML = "";
    var dados = dados_completos.resposta.dados;

    // Verifica se há separação por solicitante
    if (!(Array.isArray(dados))) {
        // Caso haja, iterar por solicitante
        for (let solicitante in dados) {
            var subtitulo = corpo.insertRow();
            subtitulo.className = "marcador";
            var subname = document.createElement('th');
            subname.innerHTML = solicitante;
            subname.setAttribute('colspan', 7);
            subname.classList.add('solicitante');

            //Adição da linha de Solicitante
            subtitulo.appendChild(subname);

            // Separação das linhas relacionadas ao Solicitante definido acima
            var lista = dados[solicitante];
            lista.forEach(function(linha) {
                var linhaCorpo = corpo.insertRow();
                linhaCorpo.className = "linha_tabela";
                linhaCorpo.style.cursor = "pointer";

                // Verificação da coluna da linha e especificações diferentes para cada coluna da linha
                for (let campo in linha) {
                    if (campo != 'status_detalhamento') {
                        var cell = linhaCorpo.insertCell();
                        if (campo == 'status_circuito') {
                            cell.innerHTML = dicionario_status[linha[campo]];
                            let celula = linha[campo];
                            var status_class;
                            if (celula in dicionario_status) {
                                if (celula == 4) {
                                    status_class = "status_tabela_bom";
                                } else if (celula == 5 || celula == 6 || celula == 7) {
                                    status_class = "status_tabela_ruim";
                                } else {
                                    status_class = "status_tabela_neutro";
                                }
                            } else {
                                status_class = "status_tabela_sem";
                            }
                            cell.classList.add(status_class);

                            // Criação do tooltip para detalhamento do status do circuito
                            if (!!linha['status_detalhamento']) {
                                cell.classList.add('tooltip');
                                var tooltip = document.createElement('span');
                                tooltip.className = 'tooltiptext ' + status_class;
                                tooltip.innerHTML = dicionario_detalhamento[linha['status_detalhamento']];
                                cell.appendChild(tooltip);
                            }
                        } else if (campo == 'circuito') {
                            // Adiciona um Evento de clique na celula do circuito apontando para a pagina do card relacionado
                            cell.style.cursor = "pointer";
                            cell.innerHTML = linha[campo];
                            cell.addEventListener('click', function() {
                                openCard(linha[campo]);
                            });
                        } else if(campo == 'modalidade_migracao') {
                            cell.innerHTML = dicionario_modalidade_migracao[linha[campo]];
                        } else if(campo == 'status_smurfs'){
                            cell.innerHTML = dicionario_status_smurfs[linha[campo]];
                        } else {
                            cell.innerHTML = linha[campo];
                        }
                    }
                }
            });
        }
    } else {
        // Caso não haja, iterar diretamente sobre os dados
        dados.forEach(function(linha) {
            var linhaCorpo = corpo.insertRow();
            linhaCorpo.className = "linha_tabela";
            linhaCorpo.style.cursor = "pointer";

            for (let campo in linha) {
                if (campo != 'status_detalhamento') {
                    var cell = linhaCorpo.insertCell();
                    if (campo == 'status_circuito') {
                        cell.innerHTML = dicionario_status[linha[campo]];
                        let celula = linha[campo];
                        var status_class;
                        if (celula in dicionario_status) {
                            if (celula == 4) {
                                status_class = "status_tabela_bom";
                            } else if (celula == 5 || celula == 6 || celula == 7) {
                                status_class = "status_tabela_ruim";
                            } else {
                                status_class = "status_tabela_neutro";
                            }
                        } else {
                            status_class = "status_tabela_sem";
                        }
                        cell.classList.add(status_class);

                        if (!!linha['status_detalhamento']) {
                            cell.classList.add('tooltip');
                            var tooltip = document.createElement('span');
                            tooltip.className = 'tooltiptext ' + status_class;
                            tooltip.innerHTML = dicionario_detalhamento[linha['status_detalhamento']];
                            cell.appendChild(tooltip);
                        }
                    } else if (campo == 'circuito') {
                        cell.style.cursor = "pointer";
                        cell.innerHTML = linha[campo];
                        cell.addEventListener('click', function() {
                            openCard(linha[campo]);
                        });
                    } else if (campo == 'categoria'){
                        cell.innerHTML = dicionario_log[linha[campo]];
                    } else if(campo == 'modalidade_migracao') {
                        cell.innerHTML = dicionario_modalidade_migracao[linha[campo]];
                    } else if(campo == 'status_smurfs'){
                        cell.innerHTML = dicionario_status_smurfs[linha[campo]];
                    } else {
                        cell.innerHTML = linha[campo];
                    }
                }
            }
        });
    }
}

//Função para montar a paginação
export function montarPagination(dados_completos, url_name) {
    let dados = dados_completos.metadados.contagem;
    let url = new URL(window.location);
    let params = new URLSearchParams(url.search);
    const totalRows = dados; // Número total de linhas
    const rowsPerPage = params.get('tamanho_pagina') || 10; // Número de linhas por página
    const totalPages = Math.ceil(totalRows / rowsPerPage); // Calcular o número total de páginas
    const currentPage = parseInt(params.get('pg') || '1', 10); // Página atual

    const pagination = document.querySelector(".a-pagination");

    // Limpar a paginação antiga
    while (pagination.firstChild) {
        pagination.removeChild(pagination.firstChild);
    }

    const prevI = document.createElement("i");
    prevI.className = "fa-solid fa-caret-left";
    prevI.id = "prevI";
    prevI.setAttribute("aria-hidden", "false");
    pagination.appendChild(prevI);

    function addPage(number) {
        const li = document.createElement("li");
        const a = document.createElement("a");
        
        // Copiar os parâmetros existentes e adicionar/alterar o parâmetro da página
        let newParams = new URLSearchParams(params);
        newParams.set('pg', number);
        a.href = `./${url_name}.html?${newParams.toString()}`;
        a.textContent = number;
        li.className = "a-pagination-item";
        if (currentPage === number) {
            li.classList.add('current-page');
        }
        li.appendChild(a);

        // Adiciona um ouvinte de eventos de clique a cada <li>
        li.addEventListener('click', function() {
            window.location.href = `./${url_name}.html?${newParams.toString()}`;
        });

        pagination.appendChild(li);
    }

    // Função para adicionar as reticências dentro da paginação se for maior que 7
    function addEllipsis() {
        const li = document.createElement("li");
        li.className = "a-pagination-item ellipsis";
        li.textContent = "...";
        li.style.cursor = "pointer";
        li.addEventListener('click', function() {
            const input = document.createElement("input");
            input.type = "number";
            input.min = 1;
            input.max = totalPages;
            input.placeholder = "Ir para página";
            input.className = "pagination-input";
            input.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    const pageNumber = parseInt(input.value, 10);
                    if (pageNumber >= 1 && pageNumber <= totalPages) {
                        let newParams = new URLSearchParams(params);
                        newParams.set('pg', pageNumber);
                        window.location.href = `./${url_name}.html?${newParams.toString()}`;
                    }
                }
            });
            li.textContent = "";
            li.appendChild(input);
            input.focus();
        });
        pagination.appendChild(li);
    }

    if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) {
            addPage(i);
        }
    } else {
        if (currentPage <= 4) {
            for (let i = 1; i <= 4; i++) {
                addPage(i);
            }
            addEllipsis();
            for (let i = totalPages - 2; i <= totalPages; i++) {
                addPage(i);
            }
        } else if (currentPage >= totalPages - 3) {
            for (let i = 1; i <= 3; i++) {
                addPage(i);
            }
            addEllipsis();
            for (let i = totalPages - 3; i <= totalPages; i++) {
                addPage(i);
            }
        } else {
            for (let i = 1; i <= 3; i++) {
                addPage(i);
            }
            addEllipsis();
            addPage(currentPage - 1);
            addPage(currentPage);
            addPage(currentPage + 1);
            addEllipsis();
            for (let i = totalPages - 2; i <= totalPages; i++) {
                addPage(i);
            }
        }
    }

    const nextI = document.createElement("i");
    nextI.className = "fa-solid fa-caret-right";
    nextI.id = "nextI";

    // Desabilita o botão de voltar pagina caso estiver na pagina 1, default
    if (currentPage === 1) {
        prevI.classList.add("a-disabled");
        prevI.style.cursor = "default";
        prevI.addEventListener('click', function(event) {
            event.preventDefault(); // Evita que a página seja recarregada
        });
    } else {
        // Volta a pagina caso não esteja na pagina 1
        prevI.addEventListener('click', function() {
            const prevPage = currentPage - 1 >= 1 ? currentPage - 1 : currentPage;
            let newParams = new URLSearchParams(params);
            newParams.set('pg', prevPage);
            window.location.href = `./${url_name}.html?${newParams.toString()}`;
        });
    }

    // Desabilita o botão de avançar página caso estiver na ultima pagina
    if (currentPage === totalPages) {
        nextI.classList.add("a-disabled");
        nextI.style.cursor = "default";
        nextI.addEventListener('click', function(event) {
            event.preventDefault(); // Evita que a página seja recarregada
        });
    } else {
        // Avança a página caso não seja a ultima
        nextI.addEventListener('click', function() {
            const nextPage = currentPage + 1 <= totalPages ? currentPage + 1 : currentPage;
            let newParams = new URLSearchParams(params);
            newParams.set('pg', nextPage);
            window.location.href = `./${url_name}.html?${newParams.toString()}`;
        });
    }
    pagination.appendChild(nextI);
}

// Função para inserção dos elementos distintos de diferentes colunas do bd para os filtros no html
export function montarFiltro(dados_completos) {
    let dados = dados_completos.metadados.filtros;
    if(!dados) return;
    let ids = ["circuito", "contrato", "status", "status_smurfs", "remessa", "modalidade", "uf", "municipio", "solicitante", "tipologia"];
    
    ids.forEach(function(id) {
        let corpo = document.querySelector(`#${id}`);
        let existingOptions = Array.from(corpo.options).map(option => option.value);

        if (Array.isArray(dados[id])) {
            dados[id].forEach(function(linha) {
                let optionText = linha; // O texto a ser exibido
                if (id === "status") {
                    optionText = dicionario_status[linha]; // Trocando os nomes de acordo com o dicionario
                } else if (id === "status_smurfs") {
                    optionText = dicionario_status_smurfs[linha]; // Trocando os nomes de acordo com o dicionario
                } else if (id === "modalidade") {
                    optionText = dicionario_modalidade_migracao[linha]; // Trocando os nomes de acordo com o dicionario
                }
                
                // Verificar se a opção já existe antes de adicionar
                if (!existingOptions.includes(linha)) {
                    let option = document.createElement('option');
                    option.value = linha; // Valor real do dado, caso precise ser usado na lógica
                    option.innerHTML = optionText; // Texto exibido ao usuário
                    corpo.appendChild(option);
                }
            });
        }
    });
}

// Função para obter parâmetros da URL
export function getParameterByName(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// Função para carregar dados da tabela inicial
export function carregarDados(apiEndpoint, url_name) {
    showLoader();

    let url = new URL(window.location);
    let params = new URLSearchParams(url.search);
    let page = params.get('pg') ?? 1;
    let tamanho_pagina = params.get("tamanho_pagina") ?? 10;
    let busca = document.querySelector(".search-txt")?.value ?? "";
    let circuito = document.querySelector("#circuito")?.value ?? "";
    let contrato = document.querySelector("#contrato")?.value ?? "";
    let status = document.querySelector("#status")?.value ?? "";
    let status_smurfs = document.querySelector("#status_smurfs")?.value ?? "";
    let remessa = document.querySelector("#remessa")?.value ?? "";
    let modalidade = document.querySelector("#modalidade")?.value ?? "";
    let uf = document.querySelector("#uf")?.value ?? "";
    let municipio = document.querySelector("#municipio")?.value ?? "";
    let solicitante = document.querySelector("#solicitante")?.value ?? "";
    let tipologia = document.querySelector("#tipologia")?.value ?? "";

    let xhr = new XMLHttpRequest();
    // Preparação do payload para o envio ao Backend
    var payload = {
        'tamanho_pagina': tamanho_pagina,
        'busca': busca,
        'page': page,
        'circuito': circuito,
        'contrato': contrato,
        'status': status,
        'status_smurfs': status_smurfs,
        'remessa': remessa,
        'modalidade': modalidade,
        'uf': uf,
        'municipio': municipio,
        'solicitante': solicitante,
        'tipologia': tipologia
    };
    
    // Converte o payload para o formato JSON
    var jsonData = JSON.stringify(payload);
    xhr.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var dados = JSON.parse(this.responseText);
            montarTabela(dados);
            montarFiltro(dados);
            montarPagination(dados, url_name);

            hideLoader();
        }
    };

    xhr.open(apiEndpoint.method, apiEndpoint.url, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.send(jsonData);
}

// Função para mostrar o ícone de carregamento
function showLoader() {
    document.getElementById("loader").style.display = "block";
}

// Função para esconder o ícone de carregamento
function hideLoader() {
    document.getElementById("loader").style.display = "none";
}

// Função que esconde os filtros enquanto não está selecionado, para não ficar poluido na pagina
function configurarFiltros() {
    const checkboxFiltros = document.querySelector('.checkbox__filtros'); // Seleciona o checkbox
    const btnFiltros = document.querySelector('#botoes__filtros'); // Seleciona o botão de filtros

    if (!checkboxFiltros || !btnFiltros) {
        console.log('Elementos não encontrados.');
        return;
    }

    // Adiciona um listener de evento 'change' ao checkbox
    checkboxFiltros.addEventListener('change', function () {
        //console.log('Checkbox estado:', this.checked); // Log para verificar o estado do checkbox
        if (this.checked) { // Verifica se o checkbox está marcado
            btnFiltros.style.display = 'flex';
        } else { // Verifica se o checkbox está desmarcado
            btnFiltros.style.display = 'none';
        }
    });

}

document.addEventListener("DOMContentLoaded", function () {
    configurarFiltros();
});
