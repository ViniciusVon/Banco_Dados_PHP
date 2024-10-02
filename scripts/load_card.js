// Importa os dicionários necessários para converter os status e modalidades dos dados
import { dicionario_status, dicionario_detalhamento, dicionario_modalidade_migracao, dicionario_status_smurfs } from './dicionarios.js';

// Função para montar os dados dos inputs do card
function montarInputs(dados_completos) {
    // Extrai os dados da resposta
    let dados = dados_completos.resposta;
    // IDs dos elementos que contêm os inputs a serem preenchidos
    let ids = ["info-gerais", "contato", "endereco"];
    console.log(dados); // Log dos dados para depuração
    
    // Itera sobre cada ID para processar os inputs
    ids.forEach(function(id) {
        // Seleciona todos os inputs dentro do elemento com o ID atual
        let inputs = document.querySelectorAll(`#${id} input`);
        if (Array.isArray(dados[id])) {
            // Itera sobre cada linha de dados
            dados[id].forEach(function(linha, index) {
                let campos = Object.keys(linha); // Obtém as chaves (nomes dos campos) da linha
                
                // Itera sobre cada campo da linha
                campos.forEach(function(campo) {
                    // Converte o valor do campo de acordo com os dicionários, se aplicável
                    if (campo === 'status_circuito' && dicionario_status[linha[campo]]) {
                        linha[campo] = dicionario_status[linha[campo]];
                    } else if (campo === 'status_smurfs' && dicionario_status_smurfs[linha[campo]]) {
                        linha[campo] = dicionario_status_smurfs[linha[campo]];
                    } else if (campo === 'modalidade_migracao' && dicionario_modalidade_migracao[linha[campo]]) {
                        linha[campo] = dicionario_modalidade_migracao[linha[campo]];
                    }
                    
                    // Preenche o input correspondente com o valor atualizado
                    if (inputs[index]) {
                        inputs[index].value = linha[campo];
                        inputs[index].setAttribute('data-valor-original', linha[campo]); // Armazena o valor original para comparação futura
                        index++;
                    }
                });
            });
        }
    });
}

function carregarDados() {
    return new Promise((resolve, reject) => {
        showLoader(); // Exibe o loader enquanto os dados estão sendo carregados

        let url = new URL(window.location); // Obtém a URL atual
        let params = new URLSearchParams(url.search); // Obtém os parâmetros da URL
        let circuito = params.get('c'); // Obtém o parâmetro 'c' da URL
        document.getElementById("nome-circuito").textContent = circuito; // Exibe o nome do circuito

        let xhr = new XMLHttpRequest(); // Cria uma nova requisição XMLHttpRequest
        xhr.onreadystatechange = function() {
            if (this.readyState === XMLHttpRequest.DONE) {
                if (this.status === 200) {
                    try {
                        var dados = JSON.parse(this.responseText); // Converte a resposta em JSON
                        montarInputs(dados); // Preenche os inputs com os dados recebidos
                        resolve(); // Resolve a promessa se os dados forem carregados com sucesso
                    } catch (e) {
                        console.error("Erro ao processar dados:", e);
                        reject(e); // Rejeita a promessa em caso de erro
                    }
                    hideLoader(); // Oculta o loader
                } else {
                    console.error("Erro ao carregar dados:", this.statusText);
                    reject(this.statusText); // Rejeita a promessa em caso de erro de status
                    hideLoader(); // Oculta o loader
                }
            }
        };
        xhr.open("POST", "../backend/obter_dados_card.php", true); // Configura a requisição para o backend
        xhr.setRequestHeader("Content-Type", "application/json"); // Define o cabeçalho como JSON
        xhr.send(JSON.stringify({ circuito: circuito })); // Envia a requisição com o parâmetro 'circuito'
    });
}

function mostrarBotaoEdicao() {
    const switchCheckbox = document.getElementById('switchCheckbox'); // Obtém o checkbox de controle
    const saveButton = document.getElementById('salvar'); // Obtém o botão de salvar
    const div_conteiner_botao = document.getElementById('botao__card'); // Obtém o container do botão de salvar

    div_conteiner_botao.style.display = 'block'; // Mostra o container do botão de salvar
    //switchCheckbox.style.display = 'block'; // Mostra o checkbox de controle
    //saveButton.style.display = 'block'; // Mostra o botão de salvar

    switchCheckbox.addEventListener('click', function() {
        let flag_switch = switchCheckbox.checked;

        if (flag_switch) {
            // Torna os inputs editáveis
            document.querySelectorAll('input').forEach(function(input) {
                input.removeAttribute('readonly');
            });
            // Exibe o botão de salvar
            saveButton.style.display = 'block';
        } else {
            // Torna os inputs não editáveis
            document.querySelectorAll('input').forEach(function(input) {
                input.setAttribute('readonly', true);
            });
            // Esconde o botão de salvar
            saveButton.style.display = 'none';
        }
    });

    saveButton.addEventListener('click', function() {
        showLoader(); // Exibe o loader enquanto os dados estão sendo salvos
        var dadosInputs = {}; // Objeto para armazenar os dados dos inputs alterados
        var circuito = document.getElementById('circuito').value; // Obtém o valor do input 'circuito'
    
        document.querySelectorAll('.input_card').forEach(function(input) {
            var valorAtual = input.value; // Obtém o valor atual do input
            var valorOriginal = input.getAttribute('data-valor-original'); // Obtém o valor original do input
            if (valorAtual !== valorOriginal) {
                dadosInputs[input.id] = valorAtual; // Armazena o valor alterado se for diferente do original
            }
        });
    
        // Se nenhum dado foi alterado
        if (Object.keys(dadosInputs).length === 0) {
            alert('Nenhum dado foi alterado.');
            hideLoader(); // Oculta o loader
    
            // Esconde o botão de salvar e torna os inputs não editáveis novamente
            saveButton.style.display = 'none';
            document.querySelectorAll('input').forEach(function(input) {
                input.setAttribute('readonly', true);
            });
    
            // Reseta o checkbox e sua cor
            switchCheckbox.checked = false;
            return;
        }
    
        dadosInputs['circuito'] = circuito; // Adiciona o circuito aos dados a serem salvos
    
        var payload = { "dadosInputs": dadosInputs }; // Cria o objeto de payload
        var jsonData = JSON.stringify(payload); // Converte o payload para JSON
    
        // Primeira requisição para atualizar os dados
        var xhr = new XMLHttpRequest(); // Cria uma nova requisição XMLHttpRequest
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Se a atualização foi bem-sucedida, faz uma segunda requisição para o log
                    logUserActionUpdate();
                } else {
                    console.error("Erro ao salvar dados:", xhr.statusText);
                    alert("Erro ao salvar dados. Tente novamente.");
                    hideLoader(); // Oculta o loader
                }
            }
        };
        xhr.open('POST', '../backend/salvar_card.php', true); // Configura a requisição para o backend
        xhr.setRequestHeader('Content-type', 'application/json'); // Define o cabeçalho como JSON
        xhr.send(jsonData); // Envia a requisição com os dados
    });
    
    function logUserActionUpdate() {
        let url = new URL(window.location); // Obtém a URL atual
        let params = new URLSearchParams(url.search); // Obtém os parâmetros da URL
        let circuito = params.get('c'); // Obtém o parâmetro 'c' da URL

        var username = sessionStorage.getItem('username'); // Obtém o username
        var usernameLocal = localStorage.getItem('username');

        if(usernameLocal){
            username = usernameLocal;
        }
    
        var logPayload = JSON.stringify({ "user_id": username, "action": "update", "circuito": circuito }); // Cria o objeto de payload para o log
    
        var xhrLog = new XMLHttpRequest(); // Cria uma nova requisição XMLHttpRequest para o log
        xhrLog.onreadystatechange = function() {
            if (xhrLog.readyState === XMLHttpRequest.DONE) {
                if (xhrLog.status === 200) {
                    hideLoader(); // Oculta o loader
                    alert("Dados atualizados e log registrado com sucesso!");
    
                    // Esconde o botão de salvar e torna os inputs não editáveis novamente
                    saveButton.style.display = 'none';
                    document.querySelectorAll('input').forEach(function(input) {
                        input.setAttribute('readonly', true);
                    });
    
                    // Reseta o checkbox e sua cor
                    switchCheckbox.checked = false;
                } else {
                    console.error("Erro ao registrar log:", xhrLog.statusText);
                    alert("Erro ao registrar log. Tente novamente.");
                    hideLoader(); // Oculta o loader
                }
            }
        };
        xhrLog.open('POST', '../backend/log_usuarios.php', true); // Configura a requisição para o log
        xhrLog.setRequestHeader('Content-type', 'application/json'); // Define o cabeçalho como JSON
        xhrLog.send(logPayload); // Envia a requisição com o log
    }    
}
// Função para exibir o loader
function showLoader() {
    document.getElementById("loader").style.display = "block";
}

// Função para ocultar o loader
function hideLoader() {
    document.getElementById("loader").style.display = "none";
}

window.onload = function() {
    let token = sessionStorage.getItem("token");
    let token_local = localStorage.getItem("token");

    if (token || token_local) {
        // Carrega os dados do card independentemente das permissões
        carregarDados()
            .then(() => {
                // Verifica se o usuário pode editar
                if (sessionStorage.getItem('can_edit') === 'true') {
                    mostrarBotaoEdicao(); // Mostra o botão de edição se o usuário pode editar
                }
            })
            .catch(error => {
                console.error("Erro ao carregar dados ou verificar permissões:", error);
                alert("Erro ao carregar dados ou verificar permissões. Por favor, recarregue a página.");
            });
    } else {
        window.location.href = '../index.html';
    }
};