// config.js
// Definição dos endpoints
export const apiEndpoints = {
    obterDadosHome: {
        url: "../backend/obter_dados_home.php",
        method: "POST"
    },
    obterDadosAtualizarCircuito: {
        url: "../backend/obter_dados_atualizar_circuito.php",
        method: "GET"
    },
    obterDadosComercial: {
        url: "../backend/obter_dados_comercial.php",
        method: "POST"
    },
    obterDadosTecnica: {
        url: "../backend/obter_dados_tecnica.php",
        method: "POST"
    },
    obterDadosHistoricoModificacoes: {
        url: "../backend/obter_dados_historico_modificacoes.php",
        method: "POST"
    },
    obterDadosGerenciarAcessos: {
        url: "../backend/obter_dados_gerenciar_acessos.php",
        method: "POST"
    }
};
