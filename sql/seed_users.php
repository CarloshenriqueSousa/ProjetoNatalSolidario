<?php
require_once __DIR__ . '/../config/database.php';

$db = Database::getInstance();

echo "--- SEMEANDO USUÁRIOS E DADOS DE TESTE ---\n";

// Array de Usuários Exemplo
$usuarios = [
    [
        'nome' => 'Diretor Geral (Admin)',
        'email' => 'admin@natal.local',
        'senha' => 'admin123',
        'perfil' => 'admin',
        'turma_id' => null
    ],
    [
        'nome' => 'Carlos (Sub-Administrador)',
        'email' => 'subadmin@natal.local',
        'senha' => 'subadmin123',
        'perfil' => 'subadmin',
        'turma_id' => null
    ],
    [
        'nome' => 'Brenno (Coordenador de Rifas)',
        'email' => 'coordenador@natal.local',
        'senha' => 'coord123',
        'perfil' => 'coordenador',
        'turma_id' => null
    ],
    [
        'nome' => 'Pedro Henrique (Resp. Coleta 3º A)',
        'email' => 'coleta.3a@natal.local',
        'senha' => 'coleta123',
        'perfil' => 'coleta',
        'turma_id' => 1
    ],
    [
        'nome' => 'Lorenzo (Resp. Coleta 3º B)',
        'email' => 'coleta.3b@natal.local',
        'senha' => 'coleta123',
        'perfil' => 'coleta',
        'turma_id' => 2
    ],
    [
        'nome' => 'Acesso Alunos 3º A',
        'email' => 'turma.3a@natal.local',
        'senha' => 'turma123',
        'perfil' => 'turma',
        'turma_id' => 1
    ]
];

foreach ($usuarios as $u) {
    $hash = password_hash($u['senha'], PASSWORD_DEFAULT);
    
    // Inserir ou Atualizar Usuário
    $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha_hash, perfil, ativo) 
                          VALUES (:nome, :email, :hash, :perfil, 1)
                          ON DUPLICATE KEY UPDATE nome = VALUES(nome), senha_hash = VALUES(senha_hash), perfil = VALUES(perfil)");
    $stmt->execute([
        ':nome' => $u['nome'],
        ':email' => $u['email'],
        ':hash' => $hash,
        ':perfil' => $u['perfil']
    ]);

    // Buscar ID do usuário inserido/atualizado
    $stmtId = $db->prepare("SELECT id FROM usuarios WHERE email = :email");
    $stmtId->execute([':email' => $u['email']]);
    $userId = (int)$stmtId->fetchColumn();

    // Se tiver turma vinculada, atualizar tabela usuarios_turmas
    if ($u['turma_id']) {
        $stmtUt = $db->prepare("INSERT INTO usuarios_turmas (usuario_id, turma_id) VALUES (:uid, :tid)
                                ON DUPLICATE KEY UPDATE turma_id = VALUES(turma_id)");
        $stmtUt->execute([':uid' => $userId, ':tid' => $u['turma_id']]);
    }

    echo "✅ Usuário criado: {$u['email']} | Perfil: {$u['perfil']} | Senha: {$u['senha']}\n";
}

// Inserir Permissões de Exemplo para Sub-Administrador
$stmtSub = $db->prepare("SELECT id FROM usuarios WHERE email = 'subadmin@natal.local'");
$subadminId = (int)$stmtSub->fetchColumn();

if ($subadminId) {
    $modulos = ['produtos', 'rifas', 'familias', 'patrocinadores'];
    foreach ($modulos as $mod) {
        foreach (['criar', 'ler', 'editar'] as $acao) {
            $stmtPerm = $db->prepare("INSERT IGNORE INTO permissoes_subadmin (usuario_id, modulo, acao) VALUES (:uid, :mod, :acao)");
            $stmtPerm->execute([':uid' => $subadminId, ':mod' => $mod, ':acao' => $acao]);
        }
    }
    echo "✅ Permissões atribuídas ao Sub-Administrador.\n";
}

// Inserir Famílias Exemplo para Teste
$familias = [
    ['nome' => 'Família Silva', 'membros' => 4, 'filhos' => 2, 'endereco' => 'Rua das Flores, 123 - Bairro Centro'],
    ['nome' => 'Família Oliveira', 'membros' => 5, 'filhos' => 3, 'endereco' => 'Av. Brasil, 456 - Bairro Alto'],
    ['nome' => 'Família Souza', 'membros' => 3, 'filhos' => 1, 'endereco' => 'Rua São José, 789 - Bairro Novo']
];

foreach ($familias as $fam) {
    $stmtFam = $db->prepare("INSERT IGNORE INTO familias (nome_responsavel, quantidade_membros, quantidade_filhos, endereco, status_entrega) 
                            VALUES (:nome, :membros, :filhos, :end, 'pendente')");
    $stmtFam->execute([
        ':nome' => $fam['nome'],
        ':membros' => $fam['membros'],
        ':filhos' => $fam['filhos'],
        ':end' => $fam['endereco']
    ]);
}
echo "✅ Famílias de exemplo criadas.\n";

// Inserir Patrocinadores Exemplo para Teste
$patrocinadores = [
    ['nome' => 'Supermercado Central', 'tipo' => 'cestas', 'valor' => 0, 'desc' => 'Doação de 50 Cestas Básicas'],
    ['nome' => 'Empresa Construtora Alfa', 'tipo' => 'dinheiro', 'valor' => 1500.00, 'desc' => 'Patrocínio em Dinheiro'],
    ['nome' => 'Loja de Brinquedos Alegria', 'tipo' => 'produtos', 'valor' => 0, 'desc' => 'Doação de 30 Brinquedos']
];

foreach ($patrocinadores as $pat) {
    $stmtPat = $db->prepare("INSERT IGNORE INTO patrocinadores (nome, tipo, valor, descricao, ativo) VALUES (:nome, :tipo, :valor, :desc, 1)");
    $stmtPat->execute([
        ':nome' => $pat['nome'],
        ':tipo' => $pat['tipo'],
        ':valor' => $pat['valor'],
        ':desc' => $pat['desc']
    ]);
}
echo "✅ Patrocinadores de exemplo criados.\n";

echo "--- CONCLUÍDO COM SUCESSO! ---\n";
