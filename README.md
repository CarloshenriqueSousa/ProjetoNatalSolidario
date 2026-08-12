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
