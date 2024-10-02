// Flag para controlar se um arquivo já foi selecionado
let fileSelected = false;

document.addEventListener('DOMContentLoaded', function() {
    hideLoader(); // Garante que o loader está escondido quando a página é carregada
    checkUserPermission(); // Verifica as permissões do usuário
    loadCargas(); // Carrega as cargas disponíveis para o usuário
});

function handleFileSelect(input) {
    // Se um arquivo já foi selecionado, exibe um alerta e não permite selecionar outro
    if (fileSelected) {
        alert('Você já selecionou um arquivo. Não é possível enviar outro.');
        return;
    }

    const file = input.files[0]; // Obtém o primeiro arquivo selecionado
    if (file) {
        document.getElementById('fileName').innerText = `Arquivo selecionado: ${file.name}`;
        document.getElementById('uploadButton').disabled = false; // Habilita o botão de upload
        fileSelected = true; // Marca que um arquivo foi selecionado
        input.disabled = true; // Desabilita o campo de seleção de arquivo para evitar múltiplas seleções

        simulateProgress(); // Simula o progresso do upload

        const customFileInput = document.querySelector(".custom-fileInput");
        customFileInput.style.border = '1px solid rgba(100, 100, 100, 0.5)';
        customFileInput.style.color = 'rgba(100, 100, 100, 0.5)';
        customFileInput.style.cursor = 'not-allowed';
        customFileInput.style.outline = '1px solid rgba(100, 100, 100, 0.5)'; // Ajusta a cor do contorno
    }
}

function simulateProgress() {
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    progressBar.style.display = 'block'; // Torna a barra de progresso visível
    let progress = 0; // Inicializa o progresso em 0

    const interval = setInterval(() => {
        progress += 1;
        progressBar.value = progress;
        progressText.innerText = `${progress}%`;

        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                progressBar.style.display = 'none';
                progressText.innerText = '';
            }, 1000);
        }
    }, 10); // Ajuste o intervalo de tempo conforme necessário
}

function handleFileUpload() {
    showLoader(); // Mostra o loader enquanto o arquivo está sendo processado
    const fileInput = document.getElementById('fileInput');
    const file = fileInput.files[0];
    if (!file) {
        alert('Por favor, selecione um arquivo.'); // Alerta se nenhum arquivo foi selecionado
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const data = new Uint8Array(e.target.result); // Obtém os dados do arquivo como array de bytes
        const workbook = XLSX.read(data, { type: 'array' }); // Lê o arquivo Excel
        const sheetName = workbook.SheetNames[3]; // Obtém o nome da primeira aba
        const sheet = workbook.Sheets[sheetName];

        const columnsToRead = ['F', 'A', 'E', 'B', 'AU', 'AC', 'R', 'Q']; // Circuito, Nome, etc.
        const startRow = 2; // Define a linha inicial para leitura (presumindo que a primeira linha é o cabeçalho)

        const rows = [];
        for (let row = startRow; row <= sheet['!ref'].split(':')[1].replace(/\D/g, ''); row++) {
            const rowData = columnsToRead.map(col => sheet[col + row]?.v || null);
            rows.push(rowData);
        }

        fetch('../backend/upload.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ data: rows }) // Envia os dados junto com cargaId
        })
        .then(response => response.json())
        .then(data => {
            hideLoader(); // Oculta o loader após o processamento
            if (data.success) {
                showLoader();
                logUserActionInsert(data.array_success); // Passa os dados para a função de log
                alert(`Arquivo XLSX enviado e processado com sucesso! \nO ID da carga feita é: ${data.carga_id}`);
                hideLoader();
            } else if(data.todos_duplicados){
                alert('Todos os circuitos que subiu já estão cadastrados no Banco de Dados');
            } else {
                alert('Erro ao processar o arquivo XLSX.');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
        });
    };

    reader.readAsArrayBuffer(file); // Lê o arquivo como ArrayBuffer
}

function showLoader() {
    document.getElementById("loader").style.display = "block";
}

function hideLoader() {
    document.getElementById("loader").style.display = "none";
}

function checkUserPermission() {
    let usuario = sessionStorage.getItem("username");
    let usernameLocal = localStorage.getItem('username');

    if(usernameLocal){
        usuario = usernameLocal;
    }

    $.ajax({
        url: '../backend/verificar_perfil.php',
        method: 'POST',
        data: { usuario: usuario }, // Envia o valor de usuario para o servidor
        dataType: 'json',
        success: function(response) {
            if (response.canDelete) {
                $('#delete-section').show(); // Mostra a seção de deletar se o usuário tiver permissão
            } else {
                $('#delete-section').hide(); // Oculta a seção de deletar se o usuário não tiver permissão
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao verificar permissões:', error);
        }
    });
}

function loadCargas() {
    $.ajax({
        url: '../backend/listar_cargas.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.cargas) {
                const cargaSelect = $('#cargaSelect');
                cargaSelect.empty(); // Limpa as opções existentes
                response.cargas.forEach(carga => {
                    cargaSelect.append(new Option(`${carga.carga_id}`, carga.carga_id)); // Adiciona novas opções ao seletor
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar cargas:', error);
        }
    });
}

function handleDeleteCarga() {
    const cargaSelect = document.getElementById('cargaSelect');
    const cargaId = cargaSelect.value;
    const username = sessionStorage.getItem("username");
    const usernameLocal = localStorage.getItem('username');

    if(usernameLocal){
        username = usernameLocal;
    }

    if (!cargaId) {
        alert('Nenhuma carga selecionada para exclusão.'); // Alerta se nenhuma carga foi selecionada
        return;
    }

    if (confirm(`Tem certeza que deseja deletar a carga com ID ${cargaId}?`)) {
        fetch('../backend/deletar_carga.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ carga_id: cargaId, username: username }) // Envia o ID da carga para exclusão
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                logUserActionDelete();
            } else {
                alert('Erro ao deletar a carga: ' + (data.error || 'Erro desconhecido.'));
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao deletar a carga.');
        });
    }
}

function logUserActionInsert(rows) {
    let username = sessionStorage.getItem('username'); // Obtém o username
    const usernameLocal = localStorage.getItem('username');

    if (usernameLocal) {
        username = usernameLocal;
    }

    let logs = [];

    rows.forEach(row => {
        const circuito = row; // Supondo que a primeira coluna seja o circuito
        logs.push({
            "user_id": username,
            "action": "insert",
            "circuito": circuito
        });
    });

    var logPayload = JSON.stringify(logs);

    var xhrLog = new XMLHttpRequest();
    xhrLog.onreadystatechange = function() {
        if (xhrLog.readyState === XMLHttpRequest.DONE) {
            if (xhrLog.status === 200) {
                hideLoader(); // Oculta o loader
                alert('Logs registrados com sucesso!');
            } else {
                console.error("Erro ao registrar logs:", xhrLog.statusText);
                alert("Erro ao registrar logs. Tente novamente.");
                hideLoader(); // Oculta o loader
            }
        }
    };
    xhrLog.open('POST', '../backend/log_usuarios.php', true);
    xhrLog.setRequestHeader('Content-type', 'application/json');
    xhrLog.send(logPayload); // Envia todos os logs em uma única requisição
}


function logUserActionDelete() {
    const username = sessionStorage.getItem('username'); // Obtém o username
    const usernameLocal = localStorage.getItem('username');

    if(usernameLocal){
        username = usernameLocal;
    }

    var logPayload = JSON.stringify({ "user_id": username, "action": "delete" }); // Adiciona o tipo de ação

    var xhrLog = new XMLHttpRequest(); // Cria uma nova requisição XMLHttpRequest para o log
    xhrLog.onreadystatechange = function() {
        if (xhrLog.readyState === XMLHttpRequest.DONE) {
            if (xhrLog.status === 200) {
                hideLoader(); // Oculta o loader
                alert('Carga deletada com sucesso!');
                //document.getElementById('cargaInfo').style.display = 'none'; // Oculta as informações da carga
                //document.getElementById('fileName').innerText = ''; // Limpa o nome do arquivo exibido
                //document.getElementById('uploadButton').disabled = true; // Desabilita o botão de upload
                //document.querySelector(".custom-fileInput").style = ''; // Reseta o estilo do campo de seleção de arquivo
                //document.getElementById('fileInput').disabled = false; // Habilita novamente o campo de seleção de arquivo
                //fileSelected = false; // Marca que nenhum arquivo está selecionado
                loadCargas(); // Atualiza a lista de cargas após a exclusão
                
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
