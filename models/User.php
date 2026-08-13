<?php
/**
 * User Model handling authentication and credentials management
 */
class User extends BaseModel {
    
    public function authenticate($login, $password) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE login = ? LIMIT 1");
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['senha'])) {
            return $user;
        }
        return false;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByLogin($login) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE login = ?");
        $stmt->execute([$login]);
        return $stmt->fetch();
    }

    public function create($nome, $login, $senha, $tipo) {
        $hashed = password_hash($senha, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (nome, login, senha, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $login, $hashed, $tipo]);
        return $this->db->lastInsertId();
    }

    public function update($id, $nome, $login, $senha = null) {
        if ($senha) {
            $hashed = password_hash($senha, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, login = ?, senha = ? WHERE id = ?");
            $stmt->execute([$nome, $login, $hashed, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE usuarios SET nome = ?, login = ? WHERE id = ?");
            $stmt->execute([$nome, $login, $id]);
        }
        return true;
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM usuarios ORDER BY nome ASC");
        return $stmt->fetchAll();
    }
}
