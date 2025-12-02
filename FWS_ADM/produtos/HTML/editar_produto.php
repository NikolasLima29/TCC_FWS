<?php
include "../../conn.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Produto não informado.");
}

$produto_id = intval($_GET['id']);

/* ------------------------------------------------------------
   Buscar dados do produto
-------------------------------------------------------------*/
$stmt = $sql->prepare("SELECT nome, categoria_id, fornecedor_id, descricao, foto_produto, preco_venda, estoque, status 
                       FROM produtos WHERE id = ?");
$stmt->bind_param("i", $produto_id);
$stmt->execute();
$result = $stmt->get_result();
$produto = $result->fetch_assoc();
$stmt->close();

if (!$produto) {
    die("Produto não encontrado.");
}

/* ------------------------------------------------------------
   Atualizar produto
-------------------------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Entrada
    $nome = trim($_POST['nome'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $fornecedor_id = intval($_POST['fornecedor_id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $preco_venda = trim($_POST['preco_venda'] ?? '');
    $estoque = trim($_POST['estoque'] ?? '');
    $status = trim($_POST['status'] ?? ''); // ENUM('ativo','inativo')
    $foto = $produto['foto_produto'];

    /* ------------------------------------------------------------
       Converter preço "12,99" → 12.99
    -------------------------------------------------------------*/
    if ($preco_venda !== "") {
        $preco_venda = str_replace(".", "", $preco_venda);
        $preco_venda = str_replace(",", ".", $preco_venda);
        $preco_venda = floatval($preco_venda);
    }

    /* ------------------------------------------------------------
       Validação
    -------------------------------------------------------------*/
    if (
        $nome === "" ||
        $categoria_id == 0 ||
        $fornecedor_id == 0 ||
        $preco_venda === "" ||
        $estoque === "" ||
        $status === ""
    ) {
        header("Location: editar_produto.php?id=$produto_id&status=erro&msg=Preencha todos os campos");
        exit;
    }

    /* ------------------------------------------------------------
       Upload da nova foto (opcional)
    -------------------------------------------------------------*/
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

        $dir_img = $_SERVER['DOCUMENT_ROOT'] . "/TCC_FWS/IMG_Produtos/";
        $foto_tmp = $_FILES['foto']['tmp_name'];
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        $nome_arquivo = $produto_id . "." . $extensao;
        $foto_path = $dir_img . $nome_arquivo;

        list($width, $height, $type) = getimagesize($foto_tmp);

        switch ($type) {
            case IMAGETYPE_JPEG: $src_image = imagecreatefromjpeg($foto_tmp); break;
            case IMAGETYPE_PNG:  $src_image = imagecreatefrompng($foto_tmp); break;
            case IMAGETYPE_WEBP: $src_image = imagecreatefromwebp($foto_tmp); break;
            default:
                header("Location: editar_produto.php?id=$produto_id&status=erro&msg=Tipo de imagem não suportado");
                exit;
        }

        $new_width = 1000;
        $new_height = 700;

        $new_img = imagecreatetruecolor($new_width, $new_height);

        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($new_img, false);
            imagesavealpha($new_img, true);
        }

        imagecopyresampled(
            $new_img, $src_image,
            0, 0, 0, 0,
            $new_width, $new_height,
            $width, $height
        );

        switch ($type) {
            case IMAGETYPE_JPEG: imagejpeg($new_img, $foto_path, 90); break;
            case IMAGETYPE_PNG:  imagepng($new_img, $foto_path); break;
            case IMAGETYPE_WEBP: imagewebp($new_img, $foto_path, 90); break;
        }

        imagedestroy($src_image);
        imagedestroy($new_img);

        $foto = "/TCC_FWS/IMG_Produtos/" . $nome_arquivo;
    }

    /* ------------------------------------------------------------
       Atualizar no banco
    -------------------------------------------------------------*/
    $query = "UPDATE produtos 
              SET nome=?, categoria_id=?, fornecedor_id=?, descricao=?, foto_produto=?, preco_venda=?, estoque=?, status=?
              WHERE id=?";

    $stmt = $sql->prepare($query);
    $stmt->bind_param("siissdisi",
        $nome,
        $categoria_id,
        $fornecedor_id,
        $descricao,
        $foto,
        $preco_venda,
        $estoque,
        $status,
        $produto_id
    );
    $stmt->execute();
    $stmt->close();

    header("Location: editar_produto.php?id=$produto_id&status=sucesso&msg=Produto atualizado com sucesso!");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Editar Produto</title>
<link rel="icon" type="image/x-icon" href="../../logotipo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<style>
body {
    background-color: #fff8e1;
    font-family: "Poppins", sans-serif;
    margin: 0;
}

/* 🔹 Barra lateral fixa */
#fund {
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px;
    background-color: black !important;
    overflow-y: auto;
    z-index: 1000;
}

#menu {
    background-color: black;
}

#cor-fonte {
    color: #ff9100;
    font-size: 23px;
    padding-bottom: 30px;
}

#cor-fonte:hover {
    background-color: #f4a21d67 !important;
}

#cor-fonte img {
    width: 44px;
}

#logo-linha img {
    width: 170px;
}

/* 🔹 Área principal */
#conteudo-principal {
    margin-left: 250px;
    padding: 40px;
}

.container {
    max-width: 700px;
    margin: auto;
    background: white;
    padding: 30px;
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
}

input[type="text"], input[type="number"], select, textarea, input[type="file"] {
    width: 100%;
    padding: 10px;
    border: 2px solid #f4a01d;
    border-radius: 5px;
    font-size: 16px;
    margin-bottom: 15px;
}

textarea { resize: none; }

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
}

#preview {
    max-width: 300px;
    max-height: 200px;
    border-radius: 10px;
    border: 2px solid #f4a01d;
    display: block;
    margin-top: 10px;
}

.alert {
    text-align: center;
    font-weight: bold;
}

@import url('../../Fonte_Config/fonte_geral.css');
</style>
</head>
<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <!-- 🔹 Barra lateral -->
        <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100" id="menu">
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                    <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png" alt="Logo"></li>
                    <li class="nav-item"><a href="../../menu_principal/HTML/menu_principal1.html" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/painelgeral.png"> <span class="ms-1 d-none d-sm-inline">Painel Geral</span></a></li>
                    <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/fastservice.png"> <span class="ms-1 d-none d-sm-inline">Fast Service</span></a></li>
                    <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/financeiro.png"> <span class="ms-1 d-none d-sm-inline">Financeiro</span></a></li>
                    <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/vendaspai.png"> <span class="ms-1 d-none d-sm-inline">Vendas</span></a></li>
                    <li><a href="/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/estoque.png"> <span class="ms-1 d-none d-sm-inline">Estoque</span></a></li>
                    <li><a href="/TCC_FWS/FWS_ADM/produtos/HTML/cadastro_produto.php" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/produtos.png"> <span class="ms-1 d-none d-sm-inline">Produtos</span></a></li>
                    <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/fornecedor.png"> <span class="ms-1 d-none d-sm-inline">Fornecedores</span></a></li>
                    <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/funcionarios.png"> <span class="ms-1 d-none d-sm-inline">Funcionários</span></a></li>
                </ul>
                <hr>
                <div class="dropdown pb-4">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                        id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://github.com/mdo.png" alt="hugenerd" width="30" height="30" class="rounded-circle">
                        <span class="d-none d-sm-inline mx-1">Usuário</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <li><a class="dropdown-item" href="#">Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#">Sair da conta</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- 🔹 Conteúdo principal -->
        <div class="col py-3" id="conteudo-principal">
            <div class="container">
                <h2>Editar Produto</h2>

                <?php if(isset($_GET['status']) && isset($_GET['msg'])): ?>
                  <div class="alert <?php echo $_GET['status'] == 'erro' ? 'alert-danger' : 'alert-success'; ?>">
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                  </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <label for="nome">Nome do Produto</label>
                    <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($produto['nome']); ?>" required>

                    <label for="categoria">Categoria</label>
                    <select name="categoria_id" id="categoria" required>
                        <option value="">Selecione...</option>
                        <?php
                        $query = "SELECT id, nome FROM categorias ORDER BY nome";
                        $result = $sql->query($query);
                        while($row = $result->fetch_assoc()) {
                            $selected = $produto['categoria_id'] == $row['id'] ? "selected" : "";
                            echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['nome']) . "</option>";
                        }
                        ?>
                    </select>

                    <label for="fornecedor">Fornecedor</label>
                    <select name="fornecedor_id" id="fornecedor" required>
                        <option value="">Selecione...</option>
                        <?php
                        $query = "SELECT id, nome FROM fornecedores ORDER BY nome";
                        $result = $sql->query($query);
                        while($row = $result->fetch_assoc()) {
                            $selected = $produto['fornecedor_id'] == $row['id'] ? "selected" : "";
                            echo "<option value='{$row['id']}' $selected>" . htmlspecialchars($row['nome']) . "</option>";
                        }
                        ?>
                    </select>

                    <label for="descricao">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="3"><?php echo htmlspecialchars($produto['descricao']); ?></textarea>

                    <label for="foto">Foto do Produto</label>
                    <input type="file" name="foto" id="foto" accept="image/*">
                    <?php if($produto['foto_produto']): ?>
                        <img id="preview" src="<?php echo $produto['foto_produto']; ?>" alt="Pré-visualização da imagem">
                    <?php else: ?>
                        <img id="preview" src="#" alt="Pré-visualização da imagem" style="display:none;">
                    <?php endif; ?>

                    <label for="preco_venda">Preço de Venda</label>
                    <input type="text" name="preco_venda" id="preco_venda" value="<?php echo htmlspecialchars($produto['preco_venda']); ?>" required>

                    <label for="estoque">Quantidade</label>
                    <input type="number" name="estoque" id="estoque" value="<?php echo htmlspecialchars($produto['estoque']); ?>" min="0" required>

                    <label for="status">Status</label>
                    <select name="status" id="status" required>
<option value="ativo" <?php if($produto['status']=='ativo') echo "selected"; ?>>Ativo</option>
<option value="inativo" <?php if($produto['status']=='inativo') echo "selected"; ?>>Inativo</option>
                    </select>

                    <button type="submit" class="btn btn-primary mb-2">Atualizar</button>
                    <a href="lista_produtos.php" class="btn btn-secondary mb-2">Voltar</a>
                </form>
            </div>
        </div>
    </div>
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
