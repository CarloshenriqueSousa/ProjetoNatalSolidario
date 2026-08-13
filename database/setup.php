<?php
/**
 * Database Setup and Seeder Script for Natal Solidário
 */

$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    // 1. Connect without database to create it
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "Conectado ao MySQL...\n";
    
    // 2. Read and execute schema SQL
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        die("Erro: Arquivo schema.sql não encontrado em: $schemaFile\n");
    }
    
    $sql = file_get_contents($schemaFile);
    
    // Execute raw SQL script
    $pdo->exec($sql);
    echo "Banco de dados e tabelas criados com sucesso.\n";
    
    // 3. Connect to the specific database
    $pdo->exec("USE `natal_solidario`");
    
    // 4. Seed default configurations
    $stmt = $pdo->prepare("INSERT INTO `pontuacao_config` (tipo_produto, pontos_por_unidade) VALUES (?, ?) ON DUPLICATE KEY UPDATE pontos_por_unidade = VALUES(pontos_por_unidade)");
    $stmt->execute(['alimento', 5]);
    $stmt->execute(['roupa', 10]);
    $stmt->execute(['brinquedo', 15]);
    echo "Configuração de pontuação padrão inserida (Alimento=5, Roupa=10, Brinquedo=15).\n";
    
    // 5. Seed initial admin user
    $adminLogin = 'admin';
    $adminPassword = 'admin123';
    $hashedPassword = password_hash($adminPassword, PASSWORD_BCRYPT);
    
    // Check if admin already exists
    $checkAdmin = $pdo->prepare("SELECT id FROM `usuarios` WHERE login = ?");
    $checkAdmin->execute([$adminLogin]);
    $adminUser = $checkAdmin->fetch();
    
    if (!$adminUser) {
        $insertUser = $pdo->prepare("INSERT INTO `usuarios` (nome, login, senha, tipo) VALUES (?, ?, ?, ?)");
        $insertUser->execute(['Administrador Geral', $adminLogin, $hashedPassword, 'admin']);
        echo "Usuário Administrador criado com sucesso: login='admin', senha='admin123'\n";
    } else {
        echo "Administrador já existe.\n";
    }
    
    // 6. Seed test classroom users & profiles
    $testClasses = [
        ['nome' => '1º Ano A', 'login' => 'turma1a', 'senha' => 'turma123'],
        ['nome' => '2º Ano B', 'login' => 'turma2b', 'senha' => 'turma123'],
        ['nome' => '3º Ano C', 'login' => 'turma3c', 'senha' => 'turma123']
    ];
    
    foreach ($testClasses as $cls) {
        $checkClass = $pdo->prepare("SELECT id FROM `usuarios` WHERE login = ?");
        $checkClass->execute([$cls['login']]);
        if (!$checkClass->fetch()) {
            $hashedClsPass = password_hash($cls['senha'], PASSWORD_BCRYPT);
            
            // Insert user
            $insertClsUser = $pdo->prepare("INSERT INTO `usuarios` (nome, login, senha, tipo) VALUES (?, ?, ?, ?)");
            $insertClsUser->execute([$cls['nome'], $cls['login'], $hashedClsPass, 'turma']);
            $userId = $pdo->lastInsertId();
            
            // Insert classroom profile
            $insertClsProfile = $pdo->prepare("INSERT INTO `turmas` (usuario_id, nome) VALUES (?, ?)");
            $insertClsProfile->execute([$userId, $cls['nome']]);
            
            echo "Turma criada: nome='{$cls['nome']}', login='{$cls['login']}', senha='{$cls['senha']}'\n";
        }
    }
    
    // 7. Seed initial Batch (Lote) for testing
    $checkLotes = $pdo->query("SELECT COUNT(*) FROM lotes")->fetchColumn();
    if ($checkLotes == 0) {
        // Find admin user id
        $adminId = $pdo->query("SELECT id FROM usuarios WHERE login = 'admin'")->fetchColumn();
        
        $insertLote = $pdo->prepare("INSERT INTO lotes (codigo, usuario_id) VALUES (?, ?)");
        $insertLote->execute(['LOTE2026-001', $adminId]);
        $insertLote->execute(['LOTE2026-002', $adminId]);
        echo "Lotes iniciais de teste criados: LOTE2026-001, LOTE2026-002.\n";
    }
    
    echo "\nSetup finalizado com sucesso!\n";
    
} catch (PDOException $e) {
    die("Erro ao configurar o banco de dados: " . $e->getMessage() . "\n");
}
