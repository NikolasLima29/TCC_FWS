<?php
include "../../conn.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Produto não informado.");
}

$produto_id = intval($_GET['id']);

// Buscar dados do produto
$stmt = $sql->prepare("SELECT nome, categoria_id, fornecedor_id, descricao, foto_produto, preco_venda, estoque, status FROM produtos WHERE id = ?");
$stmt->bind_param("i", $produto_id);
$stmt->execute();
$result = $stmt->get_result();
$produto = $result->fetch_assoc();
$stmt->close();

if (!$produto) {
    die("Produto não encontrado.");
}

// Atualizar produto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $fornecedor_id = intval($_POST['fornecedor_id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $preco_venda = trim($_POST['preco_venda'] ?? '');
    $estoque = trim($_POST['estoque'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $foto = $produto['foto_produto']; // manter foto antiga por padrão

    // Validar campos obrigatórios
    if (empty($nome) || empty($categoria_id) || empty($fornecedor_id) || empty($preco_venda) || empty($estoque) || empty($status)) {
        header("Location: editar_produto.php?id=$produto_id&status=erro&msg=Preencha todos os campos");
        exit;
    }

    // Upload da nova foto, se houver
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $dir_img = $_SERVER['DOCUMENT_ROOT'] . "/TCC_FWS/IMG_Produtos/";
        $foto_tmp = $_FILES['foto']['tmp_name'];
        $nome_arquivo = uniqid() . '_' . basename($_FILES['foto']['name']);
        $foto_path = $dir_img . $nome_arquivo;

        // Verifica dimensões
        list($width, $height) = getimagesize($foto_tmp);
        if ($width > 1000 || $height > 700) {
            header("Location: editar_produto.php?id=$produto_id&status=erro&msg=Imagem muito grande. Máx: 1000x700");
            exit;
        }

        if (move_uploaded_file($foto_tmp, $foto_path)) {
            $foto = '/TCC_FWS/IMG_Produtos/' . $nome_arquivo;
        } else {
            header("Location: editar_produto.php?id=$produto_id&status=erro&msg=Falha ao salvar a imagem");
            exit;
        }
    }

    // Atualizar no banco
    $query = "UPDATE produtos SET nome=?, categoria_id=?, fornecedor_id=?, descricao=?, foto_produto=?, preco_venda=?, estoque=?, status=? WHERE id=?";
    $stmt = $sql->prepare($query);
    $stmt->bind_param("siissdisi", $nome, $categoria_id, $fornecedor_id, $descricao, $foto, $preco_venda, $estoque, $status, $produto_id);
    
    if (!$stmt->execute()) {
        die("Erro ao atualizar produto: " . $stmt->error);
    }
    $stmt->close();

    header("Location: editar_produto.php?id=$produto_id&status=sucesso&msg=Produto atualizado com sucesso!");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Editar Produto</title>
<link rel="stylesheet" href="../../css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<style>
body { background-color: #fff8e1; font-family: "Poppins", sans-serif; }
.container { max-width: 700px; margin: 50px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 25px; color: #d11b1b; font-weight: bold; }
label { font-weight: 600; color: #333; margin-bottom: 5px; }
input[type="text"], input[type="number"], select, textarea, input[type="file"] { width: 100%; padding: 10px; border: 2px solid #f4a01d; border-radius: 5px; font-size: 16px; margin-bottom: 15px; }
textarea { resize: none; }
.btn-primary { background-color: #f4a01d; border: none; color: black; font-weight: bold; width: 100%; transition: 0.3s; }
.btn-primary:hover { background-color: #d68c19; color: white; }
.btn-secondary { background-color: #d11b1b; border: none; color: white; font-weight: bold; width: 100%; transition: 0.3s; }
.btn-secondary:hover { background-color: #a31515; }
.alert { text-align: center; font-weight: bold; }
.alert-success { background-color: #d4edda; color: #155724; }
.alert-danger { background-color: #f8d7da; color: #721c24; }
#preview { max-width: 300px; max-height: 200px; border-radius: 10px; border: 2px solid #f4a01d; display: block; margin-top: 10px; }
</style>
</head>
<body>
<div class="container">
<h2>Editar Produto</h2>

<?php if(isset($_GET['status']) && isset($_GET['msg'])): ?>
  <div class="alert <?php echo $_GET['status'] == 'erro' ? 'alert-danger' : 'alert-success'; ?>">
    <?php echo htmlspecialchars($_GET['msg']); ?>
  </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label for="nome">Nome do Produto</label>
    <input type="text" name="nome" id="nome" class="form-control" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>
  </div>

  <div class="mb-3">
    <label for="categoria">Categoria</label>
    <select name="categoria_id" id="categoria" class="form-select" required>
      <option value="">Selecione...</option>
      <?php
      $query = "SELECT id, nome FROM categorias ORDER BY nome";
      $result = $sql->query($query);
      while($row = $result->fetch_assoc()) {
          $selected = $produto['categoria_id'] == $row['id'] ? "selected" : "";
          echo "<option value='" . $row['id'] . "' $selected>" . htmlspecialchars($row['nome']) . "</option>";
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
          $selected = $produto['fornecedor_id'] == $row['id'] ? "selected" : "";
          echo "<option value='" . $row['id'] . "' $selected>" . htmlspecialchars($row['nome']) . "</option>";
      }
      ?>
    </select>
  </div>

  <div class="mb-3">
    <label for="descricao">Descrição</label>
    <textarea name="descricao" id="descricao" rows="3" class="form-control"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>
  </div>

  <div class="mb-3">
    <label for="foto">Foto do Produto</label>
    <input type="file" name="foto" id="foto" class="form-control" accept="image/*">
    <?php if($produto['foto_produto']): ?>
      <img id="preview" src="<?php echo $produto['foto_produto']; ?>" alt="Pré-visualização da imagem">
    <?php else: ?>
      <img id="preview" src="#" alt="Pré-visualização da imagem" style="display:none;">
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label for="preco_venda">Preço de Venda</label>
    <input type="text" name="preco_venda" id="preco_venda" class="form-control" value="<?php echo htmlspecialchars($produto['preco_venda']); ?>" required>
  </div>

  <div class="mb-3">
    <label for="estoque">Quantidade</label>
    <input type="number" name="estoque" id="estoque" class="form-control" value="<?php echo htmlspecialchars($produto['estoque']); ?>" min="0" required>
  </div>

  <div class="mb-3">
    <label for="status">Status</label>
    <select name="status" id="status" class="form-select" required>
      <option value="1" <?php if($produto['status']==1) echo "selected"; ?>>Ativo</option>
      <option value="0" <?php if($produto['status']==0) echo "selected"; ?>>Inativo</option>
    </select>
  </div>

  <button type="submit" class="btn btn-primary mb-2">Atualizar</button>
  <a href="index.html" class="btn btn-secondary mb-2">Voltar</a>
</form>
</div>

<script>
$(document).ready(function(){
    $('#preco_venda').mask('000.000.000,00', {reverse: true});

    $('#foto').change(function(){
        const file = this.files[0];
        if(file){
            const reader = new FileReader();
            reader.onload = function(e){
                $('#preview').attr('src', e.target.result).show();
            }
            reader.readAsDataURL(file);
        }
    });
});
</script>
</body>
</html>
<?php $sql->close(); ?>
