<?php
include_once('php/config.php');

$nome = filter_input(INPUT_POST, 'nome');
$email = filter_input(INPUT_POST, 'email');
$senha = filter_input(INPUT_POST, 'senha');


//   $query = "SELECT id FROM users WHERE email = '$email'";
//   $check_email = mysqli_query($conn, $query);

// if (mysqli_num_rows($check_email) > 0) {
//   echo "Não foi possível concluir o cadastro pois o email inserido já possui um cadastro!";
//   return;
// } else {
  $result_cadastro = "INSERT INTO users (nome, email, senha) VALUES ('$nome', '$email', '$senha')";
  $resultado = mysqli_query($conn, $result_cadastro);

//   if ($resultado) {
//       echo "Nome: $nome <br>
//       Email: $email <br>
//       Cadastro realizado com sucesso!"; 
//       } else {
//       echo "Erro ao realizar o cadastro, tente novamente mais tarde!";
//     }
// }