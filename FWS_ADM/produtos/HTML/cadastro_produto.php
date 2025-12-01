<?php 
include "../../conn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $fornecedor_id = intval($_POST['fornecedor_id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $foto = null;
    $preco_venda = trim($_POST['preco_venda'] ?? '');
    $estoque = trim($_POST['estoque'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if (empty($nome) || empty($categoria_id) || empty($fornecedor_id) || empty($preco_venda) || empty($estoque) || empty($status)) {
        header("Location: cadastro_produto.php?status=erro&msg=Preencha todos os campos");
        exit;
    }

    // Verificar se o produto já existe
    $query = "SELECT COUNT(*) FROM produtos WHERE nome = ?";
    $stmt = $sql->prepare($query);
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
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

    // Upload da foto
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

        
        $dir_img = $_SERVER['DOCUMENT_ROOT'] . "/TCC_FWS/IMG_Produtos/";
        $foto_tmp = $_FILES['foto']['tmp_name'];


        $nome_arquivo = $id_produto . "." . $extensao;
        $foto_path = $dir_img . $nome_arquivo;

        $new_width = 1000;
        $new_height = 700;

        list($width, $height, $type) = getimagesize($foto_tmp);

        switch ($type) {
            case IMAGETYPE_JPEG:
                $src_image = imagecreatefromjpeg($foto_tmp);
                break;
            case IMAGETYPE_PNG:
                $src_image = imagecreatefrompng($foto_tmp);
                break;
            case IMAGETYPE_WEBP:
                $src_image = imagecreatefromwebp($foto_tmp);
                break;
            default:
                header("Location: cadastro_produto.php?status=erro&msg=Tipo de imagem não suportado.");
                exit;
        }


        $new_img = imagecreatetruecolor($new_width, $new_height);

        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($new_img, false);
            imagesavealpha($new_img, true);
        }
        imagecopyresampled($new_img, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($new_img, $foto_path, 90);
                break;
            case IMAGETYPE_PNG:
                imagepng($new_img, $foto_path);
                break;
            case IMAGETYPE_WEBP:
                imagewebp($new_img, $foto_path, 90);
                break;
        }
        imagedestroy($src_image);
        imagedestroy($new_img);

        $foto = '/TCC_FWS/IMG_Produtos/' . $nome_arquivo;

        

    } else {
        $foto = null;
    }

    // Inserir produto
    $query = "INSERT INTO produtos (nome, categoria_id, fornecedor_id, descricao, foto_produto, preco_venda, estoque, status, criado_em)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $sql->prepare($query);
    if (!$stmt) {
        die("Erro ao preparar INSERT: " . $sql->error);
    }
    $stmt->bind_param("siissdis", $nome, $categoria_id, $fornecedor_id, $descricao, $foto, $preco_venda, $estoque, $status);

    if (!$stmt->execute()) {
        die("Erro ao inserir produto: " . $stmt->error);
    }
    $stmt->close();

    header("Location: cadastro_produto.php?status=sucesso&msg=Produto cadastrado com sucesso!");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Produto</title>
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
        }

        #editar {
            background-color: #000;
            color: white;
            font-weight: bold;
            width: 100%;
            margin-top: 10px;
        }

        .alert {
            text-align: center;
            font-weight: bold;
        }

        #preview {
            max-width: 300px;
            max-height: 200px;
            border-radius: 10px;
            border: 2px solid #f4a01d;
            display: none;
            margin-top: 10px;
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
                        <li class="nav-item"><a href="menu_principal1.html" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/painelgeral.png"> <span class="ms-1 d-none d-sm-inline">Painel Geral</span></a></li>
                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/fastservice.png"> <span class="ms-1 d-none d-sm-inline">Fast Service</span></a></li>
                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/financeiro.png"> <span class="ms-1 d-none d-sm-inline">Financeiro</span></a></li>
                        <li><a href="#" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/vendaspai.png"> <span class="ms-1 d-none d-sm-inline">Vendas</span></a></li>
                        <li><a href="/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/estoque.png"> <span class="ms-1 d-none d-sm-inline">Estoque</span></a></li>
                        <li><a href="/TCC_FWS/FWS_ADM/produtos/HTML/cadastro_produto.php" class="nav-link align-middle px-0" id="cor-fonte"><img src="../../menu_principal/IMG/produtos.png"> <span class="ms-1 d-none d-sm-inline">Produtos</span></a></li>
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
                    <h2>Cadastro de Produto</h2>

                    <?php if(isset($_GET['status']) && isset($_GET['msg'])): ?>
                        <div class="alert <?php echo $_GET['status'] == 'erro' ? 'alert-danger' : 'alert-success'; ?>">
                            <?php echo htmlspecialchars($_GET['msg']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <label for="nome">Nome do Produto</label>
                        <input type="text" name="nome" id="nome" placeholder="Ex: Coca-Cola 350ml" required>

                        <label for="categoria">Categoria</label>
                        <select name="categoria_id" id="categoria" required>
                            <option value="">Selecione...</option>
                            <?php
                            $query = "SELECT id, nome FROM categorias ORDER BY nome";
                            $result = $sql->query($query);
                            while($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['nome']) . "</option>";
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
                                echo "<option value='{$row['id']}'>" . htmlspecialchars($row['nome']) . "</option>";
                            }
                            ?>
                        </select>

                        <label for="descricao">Descrição</label>
                        <textarea name="descricao" id="descricao" rows="3" placeholder="Descrição do produto..."></textarea>

                        <label for="foto">Foto do Produto</label>
                        <input type="file" name="foto" id="foto" accept="image/*">
                        <img id="preview" src="#" alt="Pré-visualização da imagem">

                        <label for="preco_venda">Preço de Venda</label>
                        <input type="text" name="preco_venda" id="preco_venda" placeholder="R$ 0,00" required>

                        <label for="estoque">Quantidade</label>
                        <input type="number" name="estoque" id="estoque" min="0" required>

                        <label for="status">Status</label>
                        <select name="status" id="status" required>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>

                        <button type="submit" class="btn btn-primary mb-2">Cadastrar</button>
                        <a href="index.html" class="btn btn-secondary mb-2">Voltar</a>
                        <button type="button" id="editar" class="btn btn-dark" onclick="window.location.href='lista_produtos.php'">Editar Produto</button>
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
