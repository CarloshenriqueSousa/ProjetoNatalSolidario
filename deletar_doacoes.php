<?php
require 'conexao.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $sql = "DELETE FROM doacoes WHERE id = $id";

    if ($conexao->query($sql) === TRUE) {
        header("Location: listar_doacoes.php?msg=Doação apagada com sucesso");
        exit;
    } else {
        echo "Erro ao apagar: " . $conexao->error;
    }
} else {
    header("Location: listar_doacoes.php");
    exit;
}
