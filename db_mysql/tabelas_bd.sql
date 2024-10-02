--Seria bom colocar NOT NULL no que for indispensável, mas acho que primeiro temos que colocar tudo no banco de dados
--e nem sempre temos todas as informações dos circuitos

CREATE TABLE informacoes_circuito (
    circuito VARCHAR(16) PRIMARY KEY, 
    circuito_antigo VARCHAR(16), 
    id_wfb VARCHAR(50), 
    id_antigo VARCHAR(50), 
    tipo VARCHAR(50),
    cliente_sap VARCHAR(100) NOT NULL,
    solicitante VARCHAR(100) NOT NULL, 
    status_circuito INTEGER NOT NULL, 
    status_detalhamento INTEGER, 
    fornecedor VARCHAR(100) NOT NULL, 
    status_smurfs INTEGER NOT NULL, 
    remessa INTEGER NOT NULL, 
    wifi BOOLEAN NOT NULL,
    carga_id VARCHAR(255) NOT NULL
);

CREATE TABLE usuarios (
    user_id VARCHAR(50) PRIMARY KEY,
    perfil int NOT NULL
);

CREATE TABLE log_mudanca_status (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    circuito VARCHAR(16) NOT NULL, -- 
    status_circuito VARCHAR(50) NOT NULL, 
    status_detalhamento VARCHAR(255), 
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id VARCHAR(50) NOT NULL,
    FOREIGN KEY (circuito) REFERENCES informacoes_circuito(circuito),
    FOREIGN KEY (user_id) REFERENCES usuarios(user_id) 
);

CREATE TABLE comercial (
    oportunidade VARCHAR(50) PRIMARY KEY, 
    vta VARCHAR(50), 
    cotacao VARCHAR(50), 
    contrato VARCHAR(50), 
    produto VARCHAR(100) NOT NULL,
    banda VARCHAR(50), 
    circuito VARCHAR(16), 
    modalidade_migracao INTEGER NOT NULL,  -- Pode ser chamado de fornecedor --
    endereco INT NOT NULL, -- >Base - OS_TotalCircuitos [AC] [AD] [AE] [AF] [AG]
    os VARCHAR(16) NOT NULL, 
    FOREIGN KEY (circuito) REFERENCES informacoes_circuito(circuito),
    FOREIGN KEY (endereco) REFERENCES endereco(endereco_id),
    FOREIGN KEY (os) REFERENCES ordem_servico(os)
);

CREATE TABLE ordem_servico (
    os VARCHAR(16) PRIMARY KEY, 
    as_circuito VARCHAR(16),
    os_data_abertura DATE NOT NULL, 
    os_data_encerramento DATE, 
    tipo_de_os VARCHAR(50),
    status_os VARCHAR(50)
);

CREATE TABLE log_status_os (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    os VARCHAR(16) NOT NULL, 
    novo_status VARCHAR(50),
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id VARCHAR(50) NOT NULL,
    FOREIGN KEY (os) REFERENCES ordem_servico(os),
    FOREIGN KEY (user_id) REFERENCES usuarios(user_id)
);

CREATE TABLE official_demand (
    official_demand_id INT PRIMARY KEY AUTO_INCREMENT,
    data_official_demand DATE,
    user_id VARCHAR(50) NOT NULL,
    tipo_demanda VARCHAR(50), 
    plano VARCHAR(50), 
    equipamento VARCHAR(50), 
    os VARCHAR(16) NOT NULL, 
    FOREIGN KEY (user_id) REFERENCES usuarios(user_id),
    FOREIGN KEY (os) REFERENCES ordem_servico(os)
);

--Conversar sobre:
CREATE TABLE endereco (
    endereco_id INT PRIMARY KEY AUTO_INCREMENT,
    uf VARCHAR(2), 
    municipio VARCHAR(100), 
    tipo_estabelecimento VARCHAR(50), 
    nome_estabelecimento VARCHAR(100), 
    logradouro VARCHAR(255), 
    numero VARCHAR(20), 
    bairro VARCHAR(100), 
    complemento VARCHAR(255), 
    cep VARCHAR(10), 
    zona VARCHAR(50),
    longitude DECIMAL(10, 6), 
    latitude DECIMAL(10, 6), 
    vigente BOOLEAN,
    os VARCHAR(16), 
    FOREIGN KEY (os) REFERENCES ordem_servico(os)
);

CREATE TABLE acompanhamento_implantacao (
    id_fsm VARCHAR(16) PRIMARY KEY,
    account_number VARCHAR(16),
    instalacao_concluida_fsm BOOLEAN, 
    data_conclusao_fsm DATE,
    tipp_responsavel VARCHAR(100),
    tipp_anexo BOOLEAN,
    tipp_data_anexo_fsm DATE,
    trafego_esvt BOOLEAN,
    data_assinatura_tipp DATE,
    status_tipp VARCHAR(50),
    os_fechada BOOLEAN,
    os_data_fechada DATE
);

CREATE TABLE notas (
    nota_id INT PRIMARY KEY AUTO_INCREMENT,
    circuito VARCHAR(16) NOT NULL, 
    gec_anotacao TEXT,
    data_hora DATETIME,
    user_id VARCHAR NOT NULL,
    FOREIGN KEY (circuito) REFERENCES informacoes_circuito(circuito),
    FOREIGN KEY (user_id) REFERENCES usuarios(user_id)
);

CREATE TABLE contato (
    contato_id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100),
    telefone VARCHAR(15),
    endereco INT,
    email VARCHAR(100),
    FOREIGN KEY (endereco) REFERENCES endereco(endereco_id)
);