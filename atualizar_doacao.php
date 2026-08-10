<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $nome_doador = $_POST['nome_doador'] ?? '';
    $contato = $_POST['contato'] ?? '';
    $tipo_doacao = $_POST['tipo_doacao'] ?? '';
    $quantidade = (int) ($_POST['quantidade'] ?? 0);
    $data_doacao = $_POST['data_doacao'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    // proteção básica
    $nome_doador = $conexao->real_escape_string($nome_doador);
    $contato = $conexao->real_escape_string($contato);
    $tipo_doacao = $conexao->real_escape_string($tipo_doacao);
    $observacoes = $conexao->real_escape_string($observacoes);

    if ($id > 0) {
        $sql = "UPDATE doacoes 
                SET nome_doador = '$nome_doador',
                    contato = '$contato',
                    tipo_doacao = '$tipo_doacao',
                    quantidade = $quantidade,
                    data_doacao = '$data_doacao',
                    observacoes = '$observacoes'
                WHERE id = $id";

        if ($conexao->query($sql) === TRUE) {
            header("Location: listar_doacoes.php?msg=Doação atualizada com sucesso");
            exit;
        } else {
            echo "Erro ao atualizar: " . $conexao->error;
        }
    } else {
        echo "ID inválido.";
    }
} else {
    header("Location: listar_doacoes.php");
    exit;
}
