<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/style1.css">
  <title>Document</title>
</head>
<body>
  <!-- CABEÇALHO -->
  <div class="custom-container">
    <div id="cabecalho">
      <div id="conteudo-cabecalho">
        <h1>Desafio - Vox</h1>
      </div>
    </div>
    
    <div id="background">
      <video loop autoplay muted>
        <source src="./mp4/backgroundVideo.mp4" type="video/mp4">
      </video>
    </div>
    <a href="index.php" id="botao-retornar"> retornar</a>

  <?php
    include_once('php/config.php');

    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $senha = filter_input(INPUT_POST, 'senha', FILTER_SANITIZE_STRING);


    // if(isset($_POST['nome']) || isset($_POST['email']) || isset($_POST[['senha']])) {
    //   if(strlen($_POST['email']) == 0) {
    //     echo "Preencha seu email":
    //   }else if(strlen($_POST['senha']) == 0){
    //     echo "Preencha sua senha";
    //   }else {
    //     $email = $conn->real_escape_string($_POST['email']);
    //     $senha = $conn->real_escape_string($_POST['senha']);

    //     $sql_code = "SELECT * FROM users WHere email = '$email' AND senha = '$enha'";
    //     $sql_query = $conn->query($sql_code) or die ("Falha na exceução do código SQl");
    //   }

    // }
    if ($email === false) {
        echo "E-mail inválido!";
        exit();
    }

    if (empty($nome) || empty($email) || empty($senha)){
      echo "Todos os campos são obrigatórios";
    }

    if (strlen($senha) < 8) {
        echo "A senha deve ter pelo menos 8 caracteres!";
        exit();
    }

    // Verificação se o email já existe no banco de dados
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "Não foi possível concluir o cadastro pois o email inserido já possui um cadastro!";
        $stmt->close();
        $conn->close();
        exit();
    } 

    $stmt->close();

    // Inserção de segurança nas senhas
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Preparação e execução da inserção no banco de dados
    $stmt = $conn->prepare("INSERT INTO users (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nome, $email, $senha_hash);

    if ($stmt->execute()) {
        echo "Cadastro realizado com sucesso!";
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
  ?>
  
  <script src="js/main.js"></script>  
</body>
</html>