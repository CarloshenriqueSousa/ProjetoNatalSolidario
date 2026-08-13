<?php
$servidor = "localhost";
$usuario = "root";      // padrão do XAMPP
$senha = "bdjmf";            // normalmente vazio
$banco = "natal_solidario_jmf";

$conexao = new mysqli($servidor, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>
