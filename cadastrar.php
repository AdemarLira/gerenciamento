<?php
include_once('php/config.php');

$nome = filter_input(INPUT_POST, 'nome');
$email = filter_input(INPUT_POST, 'email');
$senha = filter_input(INPUT_POST, 'senha');

echo "Nome: $nome <br>";
echo "Email: $email <br>";
echo "Senha: $senha <br>";

$result_cadastro = "INSERT INTO users (nome, email, senha) VALUES ('$nome', '$email', '$senha')";

$resultado = mysqli_query($conn, $result_cadastro);
