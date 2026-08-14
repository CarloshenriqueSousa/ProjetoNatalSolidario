<?php
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome_doador = $_POST['nome_doador'] ?? '';
    $contato = $_POST['contato'] ?? '';
    $tipo_doacao = $_POST['tipo_doacao'] ?? '';
    $quantidade = $_POST['quantidade'] ?? 0;
    $data_doacao = $_POST['data_doacao'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    // simples proteção básica
    $nome_doador = $conexao->real_escape_string($nome_doador);
    $contato = $conexao->real_escape_string($contato);
    $tipo_doacao = $conexao->real_escape_string($tipo_doacao);
    $observacoes = $conexao->real_escape_string($observacoes);

    $sql = "INSERT INTO doacoes (nome_doador, contato, tipo_doacao, quantidade, data_doacao, observacoes)
            VALUES ('$nome_doador', '$contato', '$tipo_doacao', $quantidade, '$data_doacao', '$observacoes')";

    if ($conexao->query($sql) === TRUE) {
        header("Location: listar_doacoes.php?msg=Doação cadastrada com sucesso");
        exit;
    } else {
        echo "Erro ao cadastrar: " . $conexao->error;
    }
} else {
    header("Location: index.php");
    exit;
}