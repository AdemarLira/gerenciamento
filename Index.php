<?php
include_once('php/config.php');
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style1.css">
  <title>Cadastro de Usuário</title>
</head>

  <body>
    <!-- CABEÇALHO -->
    <div class="custom-container">
      <div id="cabecalho">
        <div id="conteudo-cabecalho">
          <h1>CRUD</h1>
        </div>
      </div>

      <div id="background">
        <video loop autoplay muted>
          <source src="./mp4/backgroundVideo.mp4" type="video/mp4">
        </video>
      </div>
    
          <!-- MODAL DE CADASTRO -->
          <div class="custom-cadastro">
            <h2>Cadastre-se</h1>
              <div class="modal-body">
                <form method="POST" action="cadastrar.php" onsubmit="return validarFormulario()">
                  <div class="form-group">
                    <label for="nome">Nome completo</label>
                    <input type="text" class="form-control mb-2" id="nome" placeholder="Digite seu nome" name="nome">
                  </div>
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type=" email" class="form-control mb-2" id="email" placeholder="Insira seu email..." name="email">
                  </div>
                  <div class="form-group">
                    <label for="password">Senha</label>
                    <input type="password" class="form-control mb-2" id="senha" placeholder="Insira sua senha..." name="senha">
                  </div>
              </div>
              <div class="form-group">
                <button type="submit" class="custom-save-button" onclick="salvarCadastro()">Enviar</button>
              </div>
              </form>
          </div>
      </div>
    </div>
  </div>
    <script src="js/main.js"></script>  
  </body>
</html>