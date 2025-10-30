<?php 
include "../../conn.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $fornecedor_id = intval($_POST['fornecedor_id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $foto = null; // Implementar upload da foto depois
    $preco_venda = trim($_POST['preco_venda'] ?? '');
    $estoque = trim($_POST['estoque'] ?? '');
    $status = trim($_POST['status'] ?? '');




    if (empty($nome) || empty($categoria_id) || empty($fornecedor_id) || empty($preco_venda) || empty($estoque) || empty($status)) {
        header("Location: cadastro_produto.php?status=erro&msg=Preencha todos os campos");
        exit;
    }

   

//verificar se o produto já existe
    $query = "SELECT COUNT(*) FROM produtos WHERE nome = ?";
    $stmt = $sql->prepare($query);
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        //se o produto já existir, retornar um erro
        header("Location: cadastro_produto.php?status=erro&msg=Produto já cadastrado.");
        exit;
    }
    // Verificar se a categoria existe
    $stmt = $sql->prepare("SELECT id FROM categorias WHERE id = ?");
    $stmt->bind_param("i", $categoria_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
    header("Location: cadastro_produto.php?status=erro&msg=Categoria inválida");
    exit;
}
    $stmt->close();

// Verificar se o fornecedor existe
    $stmt = $sql->prepare("SELECT id FROM fornecedores WHERE id = ?");
    $stmt->bind_param("i", $fornecedor_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
    header("Location: cadastro_produto.php?status=erro&msg=Fornecedor inválido");
    exit;
}

$stmt->close();
 //upload da foto:
    
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $dir_img = $_SERVER['DOCUMENT_ROOT'] . "/TCC_FWS/IMG_Produtos/";
    $foto_tmp = $_FILES['foto']['tmp_name'];
    $nome_arquivo = uniqid() . '_' . basename($_FILES['foto']['name']);
    $foto_path = $dir_img . $nome_arquivo;

    // Verifica dimensões
    list($width, $height) = getimagesize($foto_tmp);
    $max_width = 1000; // largura máxima
    $max_height = 700; // altura máxima
    if ($width > $max_width || $height > $max_height) {
        header("Location: cadastro_produto.php?status=erro&msg=Imagem muito grande. Máximo permitido: 1000x700 pixels.");
        exit;
    }

    // Move o arquivo para o diretório
    if (move_uploaded_file($foto_tmp, $foto_path)) {
    $foto = '/TCC_FWS/IMG_Produtos/' . $nome_arquivo;
    } else {
        header("Location: cadastro_produto.php?status=erro&msg=Falha ao salvar a imagem.");
        exit;
    }
} else {
    $foto = null; // se não houver imagem enviada
}

    // Insere o novo produto no banco de dados
        
    $query = "INSERT INTO produtos (nome, categoria_id, fornecedor_id, descricao, foto_produto, preco_venda, estoque, status, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $sql->prepare($query);

    $stmt->bind_param("siissdis", $nome, $categoria_id, $fornecedor_id, $descricao, $foto, $preco_venda, $estoque, $status);



    if (!$stmt) {
        die("Erro ao preparar INSERT: " . $sql->error);
    }


    if (!$stmt->execute()) {
        die("Erro ao inserir produto: " . $stmt->error);
    }

    $stmt->close();
   

    header("Location: cadastro_produto.php?status=sucesso&msg=Produto cadastrado com sucesso!");
    exit;

}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produto</title>
    <link rel="stylesheet" href="../../css/style.css">
    <!---bootstrap e jquery--->
   
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

    <style>
    body {
      background-color: #fff8e1;
      font-family: "Poppins", sans-serif;
    }

    .container {
      max-width: 700px;
      margin: 50px auto;
      padding: 30px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #d11b1b;
      font-weight: bold;
    }

    label {
      font-weight: 600;
      color: #333;
      margin-bottom: 5px;
    }

    input[type="text"], input[type="number"], select, textarea, input[type="file"] {
      width: 100%;
      padding: 10px;
      border: 2px solid #f4a01d;
      border-radius: 5px;
      font-size: 16px;
      margin-bottom: 15px;
    }

    textarea {
      resize: none;
    }

    .btn-primary {
      background-color: #f4a01d;
      border: none;
      color: black;
      font-weight: bold;
      width: 100%;
      transition: 0.3s;
    }

    .btn-primary:hover {
      background-color: #d68c19;
      color: white;
    }

    .btn-secondary {
      background-color: #d11b1b;
      border: none;
      color: white;
      font-weight: bold;
      width: 100%;
      transition: 0.3s;
    }

    .btn-secondary:hover {
      background-color: #a31515;
    }

    .alert {
      text-align: center;
      font-weight: bold;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
    }

    .alert-danger {
      background-color: #f8d7da;
      color: #721c24;

      
    }
    #preview {
      max-width: 300px;
      max-height: 200px;
      border-radius: 10px;
      border: 2px solid #f4a01d;
      display: none;
      margin-top: 10px;
      
    }
    #editar{
        background-color: #000000ff;
      border: none;
      color: white;
      font-weight: bold;
      width: 100%;
      margin-top: 10px;
      transition: 0.3s;
    }
  </style>
</head>
<body>
    <div class="container">
    <h2>Cadastro de Produto</h2>

    <?php if(isset($_GET['status']) && isset($_GET['msg'])): ?>
      <div class="alert <?php echo $_GET['status'] == 'erro' ? 'alert-danger' : 'alert-success'; ?>">
        <?php echo htmlspecialchars($_GET['msg']); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="nome">Nome do Produto</label>
        <input type="text" name="nome" id="nome" class="form-control" placeholder="Ex: Coca-Cola 350ml" required>
      </div>

      <div class="mb-3">
        <label for="categoria">Categoria</label>
        <select name="categoria_id" id="categoria" class="form-select" required>
          <option value="">Selecione...</option>
          <?php
          $query = "SELECT id, nome FROM categorias ORDER BY nome";
          $result = $sql->query($query);
          while($row = $result->fetch_assoc()) {
              echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['nome']) . "</option>";
          }
          ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="fornecedor">Fornecedor</label>
        <select name="fornecedor_id" id="fornecedor" class="form-select" required>
          <option value="">Selecione...</option>
          <?php
          $query = "SELECT id, nome FROM fornecedores ORDER BY nome";
          $result = $sql->query($query);
          while($row = $result->fetch_assoc()) {
              echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['nome']) . "</option>";
          }
          ?>
        </select>
      </div>

      <div class="mb-3">
        <label for="descricao">Descrição</label>
        <textarea name="descricao" id="descricao" rows="3" class="form-control" placeholder="Descrição do produto..."></textarea>
      </div>

      <div class="mb-3">
  <label for="foto">Foto do Produto</label>
  <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
  
  <!--  Área onde a imagem será exibida -->
  <div class="preview-container">
    <img id="preview" src="#" alt="Pré-visualização da imagem">
  </div>
</div>

      <div class="mb-3">
        <label for="preco_venda">Preço de Venda</label>
        <input type="text" name="preco_venda" id="preco_venda" class="form-control" placeholder="R$ 0,00" required>
      </div>

      <div class="mb-3">
        <label for="estoque">Quantidade</label>
        <input type="number" name="estoque" id="estoque" class="form-control" min="0" required>
      </div>

      <div class="mb-3">
        <label for="status">Status</label>
        <select name="status" id="status" class="form-select" required>
          <option value="1">Ativo</option>
          <option value="0">Inativo</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary mb-2">Cadastrar</button>
      <a href="index.html" class="btn btn-secondary">Voltar</a>
    <button type="button" class="btn btn-primary mb-2" id= "editar" onclick="window.location.href='lista_produtos.php'">Editar Produto</button>
    </form>
  </div>

  <script>
    $(document).ready(function(){
      // Máscara de preço
      $('#preco_venda').mask('000.000.000,00', {reverse: true});

      // Preview da imagem
      $('#foto').change(function(){
        const file = this.files[0];
        if (file) {
          const reader = new FileReader();
          reader.onload = function(e) {
            $('#preview').attr('src', e.target.result).show();
          }
          reader.readAsDataURL(file);
        } else {
          $('#preview').hide();
        }
      });
    });
  </script>
</body>
</html>
<?php
$sql->close();
?>