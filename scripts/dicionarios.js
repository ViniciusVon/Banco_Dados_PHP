// Dicionarios
export const dicionario_status = {1: "Em instalação",2: "Instalado", 3:"Pendente", 4: "Em Operação", 5:"Bloqueado", 6:"Desativado", 7: "Cancelado"};
export const dicionario_detalhamento = {
    201:"TIPP em análise VIASAT", 202:"TIPP em análise TELEBRAS", 203: "Pendência VIASAT - TIPP recusado", 204: "Pendência VIASAT - TIPP ausente",
    205: "Pendência VIASAT - Mapa de Calor",301: "Pendência cliente - Sem contato ou dados divergentes", 302:"Pendência cliente - Demanda duplicada", 303:"Pendência cliente - Sem Autorização para Instalação",
    304: "Pendência cliente - Local inexistente ou desativado", 305: "Pendência cliente - Local em obras ou sem infraestrutura", 306: "Pendência TELEBRAS - Aprovação orçamento", 
    307: "Pendência TELEBRAS - Aguarda definição", 308: "Pendência - Local temporariamente inacessível (VIASAT monitorar)", 309: "Outros", 310: "Pendência TELEBRAS - Orçamento Reprovado",
    401:"Aguarda TAPP", 402: "TAPP Recusado", 403:"TIPP em Re-análise Telebras", 404:"TIPP em Re-análise Viasat", 405:"TAPP Aprovado", 601: "Grace Period", 602:"Substituição Indicada",
    603:"Reduced Fee"
};
export const dicionario_modalidade_migracao = {1: "Presencial", 2: "Remoto", 3: "Hughes", 4: "SES"};
export const dicionario_status_smurfs = {1: "Oportunidade", 2:"VTA", 3:"Cotação", 4:"Em instalação", 5:"Instalado", 6:"Enviado para OM", 7:"TIPP Aceito", 8:"TIPP com Pendência", 9:"TIPP enviado ao MCOM", 
10:"Desativado", 11:"Cancelado"};
export const dicionario_log = {1: "Inserção", 2: "Atualização", 3: "Exclusão"};