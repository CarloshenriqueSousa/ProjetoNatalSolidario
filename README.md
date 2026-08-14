<<<<<<< HEAD
# 🎄 Natal Solidário - Sistema de Gerenciamento

Sistema web completo para gerenciamento de arrecadação de doações, estoque, turmas, rifas, prestação de contas, famílias beneficiárias, finanças e ranking do **Natal Solidário**.

Desenvolvido em **PHP Puro (sem frameworks)**, **JavaScript Vanilla**, **HTML5**, **CSS3 (sem bibliotecas de estilo)** e **MySQL / MariaDB (via PDO Nativo)**.

---

## 🛑 Regra Zero (Diretrizes Técnicas e Restritivas)

1. **Sem Frameworks Backend**: PHP Puro em arquitetura MVC modular.
2. **Sem Frameworks Frontend**: Interface construída do zero em CSS3 Puro e JavaScript Vanilla sem Bootstrap, Tailwind, React, Vue, jQuery ou similares.
3. **Sem ORMs**: Uso de **PDO (PHP Data Objects)** nativo com *Prepared Statements* em 100% das consultas para prevenir SQL Injection.
4. **Segurança Avançada**:
   - Criptografia de senhas via `password_hash()` (bcrypt) e validação via `password_verify()`.
   - Sanitização de saída na View com `htmlspecialchars()` contra XSS.
   - Proteção de sessão contra sequestro (*Session Hijacking*) através de verificação de IP e User-Agent.
   - Controle de Acesso Baseado em Papéis (**RBAC - Role Based Access Control**) rígido com retorno HTTP 403 para acessos não autorizados.

---

## 👥 Perfis de Acesso & Contas de Teste

| Perfil / Função | E-mail de Login | Senha | Nível de Acesso & Permissões |
| :--- | :--- | :--- | :--- |
| **Administrador / Diretor** | `admin@natal.local` | `admin123` | **Acesso Total**: Ranking, Finanças, Rifas, Famílias, Relatórios e Configurações. |
| **Sub-Administrador** | `subadmin@natal.local` | `subadmin123` | **Permissões Granulares**: Módulos atribuídos via tabela `permissoes_subadmin`. |
| **Coordenador de Rifas** | `coordenador@natal.local` | `coord123` | **Operacional**: Entrega, recebimento, prestação de contas de rifas e famílias. |
| **Responsável de Coleta 3º A** | `coleta.3a@natal.local` | `coleta123` | **Restrito à Turma 1**: Cadastro e consulta de produtos da sua turma. **Bloqueio 403** para Ranking e Finanças. |
| **Responsável de Coleta 3º B** | `coleta.3b@natal.local` | `coleta123` | **Restrito à Turma 2**: Cadastro e consulta de produtos da sua turma. |
| **Acesso Alunos (Turma)** | `turma.3a@natal.local` | `turma123` | **Somente Leitura**: Produtos e rifas da sua turma. Sem acesso a rankings ou dados gerais. |

---

## 📦 Estrutura do Projeto

```text
natal-solidario/
 ├── config/
 │   ├── config.php          # Configurações gerais e segurança de sessão
 │   └── database.php        # Conexão PDO Singleton com MySQL
 ├── core/
 │   ├── Router.php          # Roteador nativo em PHP
 │   ├── Auth.php            # Middleware RBAC e Autenticação
 │   └── Controller.php      # Controller Base
 ├── controllers/
 │   ├── AuthController.php
 │   ├── DashboardController.php
 │   ├── ProdutosController.php
 │   ├── RifasController.php
 │   ├── FinanceiroController.php
 │   ├── FamiliasController.php
 │   └── RelatoriosController.php
 ├── models/
 │   ├── Usuario.php
 │   ├── Turma.php
 │   ├── Produto.php
 │   ├── Rifa.php
 │   ├── Financeiro.php
 │   └── Familia.php
 ├── views/
 │   ├── layouts/
 │   │   ├── header.php      # Menu adaptativo por perfil RBAC
 │   │   └── footer.php
 │   ├── auth/
 │   ├── dashboard/
 │   ├── produtos/
 │   ├── rifas/
 │   ├── familias/
 │   ├── financeiro/
 │   └── relatorios/
 ├── public/
 │   ├── css/
 │   │   ├── style.css       # CSS Global e Design System
 │   │   └── dashboard.css   # Estilos para dashboards e rankings
 │   ├── js/
 │   │   ├── app.js          # Scripts globais Vanilla
 │   │   ├── rifas.js        # Cálculos dinâmicos em tempo real
 │   │   └── estoque.js      # Alternância de campos de formulário
 │   └── index.php           # Front Controller (Ponto de entrada único)
 └── sql/
     ├── schema.sql          # Script DDL completo de tabelas MySQL
     └── seed_users.php      # Script de população de contas de teste
```

---

## 🛠️ Como Executar Localmente

### Pré-requisitos
- PHP 8.0+
- MySQL / MariaDB (ex: XAMPP)

### Passo a Passo

1. **Clonar o Repositório**:
   ```bash
   git clone <URL_DO_REPOSITORIO>
   cd natal-solidario
   ```

2. **Importar o Banco de Dados**:
   Execute o script DDL em `sql/schema.sql` no MySQL / MariaDB ou rode o arquivo de população:
   ```bash
   php sql/seed_users.php
   ```

3. **Iniciar o Servidor PHP**:
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

4. **Acessar no Navegador**:
   Navegue até `http://127.0.0.1:8000/login` e utilize um dos e-mails e senhas listados na tabela de contas de teste.

---

## 📜 Licença

Projeto desenvolvido para o evento **Natal Solidário**. Todos os direitos reservados.
=======
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
>>>>>>> parent of fef2cb1 (Refatorização do projeto)
