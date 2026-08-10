Antes de Utilizar execute no MySQL

CREATE DATABASE natal_solidario_jmf;

USE natal_solidario_jmf;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_nascimento DATE NULL,
    bio TEXT NULL,
    foto_perfil VARCHAR(255) DEFAULT 'default.png',
    ativo TINYINT(1) DEFAULT 1,
    nivel ENUM('user', 'admin') DEFAULT 'user',
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE doacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_doador VARCHAR(100) NOT NULL,
    contato VARCHAR(100),
    tipo_doacao VARCHAR(50) NOT NULL,
    quantidade DECIMAL(10, 2) NOT NULL,
    data_doacao DATE NOT NULL,
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


*Instruções de uso*

1. REGISTRO SIMPLES
O doador informa o que pretende doar (cesta básica, brinquedo, roupa), especifica a quantidade e a data disponível para entrega. Pode incluir observações adicionais, como "posso entregar na igreja no dia 20".
2. ORGANIZAÇÃO INTERNA
Todas as doações registradas são compiladas em uma lista central. A equipe organizadora monitora os itens prometidos e planeja a logística de coleta e distribuição.
3. SEM PAGAMENTO ONLINE
O sistema não processa transações financeiras. Não há integração com PIX, cartão de crédito ou boleto bancário. O foco é exclusivamente em doações de objetos físicos.
4. TRANSPARÊNCIA
O site apresenta o histórico da instituição (15 anos de atividades), informa sobre parcerias com prefeituras e compromete-se a compartilhar fotos e relatórios das entregas realizadas.
FLUXO DO USUÁRIO
Usuário acessa o site
Preenche o formulário de registro
Clica em "Registrar"
Sua doação é adicionada à lista central
Equipe entra em contato para combinar detalhes
Acerta a forma de entrega ou coleta
Doação é distribuída às famílias beneficiadas
PONTOS DE ATENÇÃOPROBLEMAS IDENTIFICADOS
Textos com erros ortográficos ("dosdio", "profeturas")
Layout desorganizado com informações misturadas
Falta de clareza sobre o processo após o registro
Tom excessivamente informal em algumas passagens
PONTOS FORTES
Formulário simples e de preenchimento rápido
Diferenciação por focar em doações físicas
Informações sobre transparência institucional
Prazo estabelecido para doações (18 de dezembro)
SUGESTÕES DE MELHORIA
Corrigir erros de digitação no conteúdo
Adicionar um guia passo a passo acima do formulário
Explicar claramente as etapas após o registro
Destacar os canais de contato (WhatsApp, telefone)
