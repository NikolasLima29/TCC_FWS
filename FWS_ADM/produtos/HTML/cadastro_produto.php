<?php
include "../../conn.php";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Produto não informado.");
}

$produto_id = intval($_GET['id']);

/* ------------------------------------------------------------
   Buscar dados atuais do produto
-------------------------------------------------------------*/
$stmt = $sql->prepare("SELECT * FROM produtos WHERE id = ?");
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

    $nome = trim($_POST['nome'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $fornecedor_id = intval($_POST['fornecedor_id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $preco_venda = trim($_POST['preco_venda'] ?? '');
    $estoque = trim($_POST['estoque'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $validade_padrao_meses = intval($_POST['validade'] ?? 1);
    $nao_perecivel = isset($_POST['nao_perecivel']);

    // produto não perecível → validade 0
    if ($nao_perecivel) $validade_padrao_meses = 0;

    $foto = $produto['foto_produto'];

    // Converter preço "12,99" -> 12.99
    if ($preco_venda !== "") {
        $preco_venda = str_replace(".", "", $preco_venda);
        $preco_venda = str_replace(",", ".", $preco_venda);
        $preco_venda = floatval($preco_venda);
    }

    // Validação
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
       UPLOAD DE FOTO (Se houver nova)
    -------------------------------------------------------------*/
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

        $dir_img = $_SERVER['DOCUMENT_ROOT'] . "/TCC_FWS/IMG_Produtos/";
        $foto_tmp = $_FILES['foto']['tmp_name'];
        $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        $nome_arquivo = $produto_id . "." . $extensao;
        $foto_path = $dir_img . $nome_arquivo;

        // Remove imagem antiga
        if (!empty($produto['foto_produto'])) {
            $caminho_antigo = $_SERVER['DOCUMENT_ROOT'] . $produto['foto_produto'];
            if (file_exists($caminho_antigo)) unlink($caminho_antigo);
        }

        // Redimensionamento
        list($width, $height, $type) = getimagesize($foto_tmp);

        switch ($type) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($foto_tmp); break;
            case IMAGETYPE_PNG:  $src = imagecreatefrompng($foto_tmp); break;
            case IMAGETYPE_WEBP: $src = imagecreatefromwebp($foto_tmp); break;
            default:
                header("Location: editar_produto.php?id=$produto_id&status=erro&msg=Imagem inválida");
                exit;
        }

        $new_img = imagecreatetruecolor(1000, 700);

        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_WEBP) {
            imagealphablending($new_img, false);
            imagesavealpha($new_img, true);
        }

        imagecopyresampled($new_img, $src, 0, 0, 0, 0, 1000, 700, $width, $height);

        switch ($type) {
            case IMAGETYPE_JPEG: imagejpeg($new_img, $foto_path, 90); break;
            case IMAGETYPE_PNG:  imagepng($new_img, $foto_path); break;
            case IMAGETYPE_WEBP: imagewebp($new_img, $foto_path, 90); break;
        }

        imagedestroy($src);
        imagedestroy($new_img);

        $foto = "/TCC_FWS/IMG_Produtos/" . $nome_arquivo;
    }

    /* ------------------------------------------------------------
       Atualizar no banco
    -------------------------------------------------------------*/
    $query = "UPDATE produtos SET 
        nome=?, categoria_id=?, fornecedor_id=?, descricao=?, foto_produto=?, 
        preco_venda=?, estoque=?, status=?, validade_padrao_meses=? 
        WHERE id=?";

    $stmt = $sql->prepare($query);
    $stmt->bind_param(
        "siissdisii",
        $nome,
        $categoria_id,
        $fornecedor_id,
        $descricao,
        $foto,
        $preco_venda,
        $estoque,
        $status,
        $validade_padrao_meses,
        $produto_id
    );

    $stmt->execute();
    $stmt->close();

    header("Location: editar_produto.php?id=$produto_id&status=sucesso&msg=Produto atualizado!");
    exit;
}
?>



<?php
include "../../conn.php";

// --------------------------------------------------------------
// 1. BUSCAR PRODUTO PELO ID
// --------------------------------------------------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: lista_produtos.php?status=erro&msg=ID inválido");
    exit;
}

$id = intval($_GET['id']);

$stmt = $sql->prepare("SELECT * FROM produtos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: lista_produtos.php?status=erro&msg=Produto não encontrado");
    exit;
}

$produto = $result->fetch_assoc();
$stmt->close();

// --------------------------------------------------------------
// 2. SE ENVIAR O FORMULÁRIO → ATUALIZA PRODUTO
// --------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $fornecedor_id = intval($_POST['fornecedor_id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $preco_venda = trim($_POST['preco_venda'] ?? '');
    $estoque = intval($_POST['estoque'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $validade = intval($_POST['validade'] ?? 0);
    $nao_perecivel = isset($_POST['nao_perecivel']) ? 1 : 0;

    // Preço BR → US
    if ($preco_venda !== "") {
        $preco_venda = str_replace('.', '', $preco_venda);
        $preco_venda = str_replace(',', '.', $preco_venda);
        $preco_venda = floatval($preco_venda);
    }

    // Validação
    if (
        $nome === "" || $categoria_id == 0 || $fornecedor_id == 0 ||
        $preco_venda === "" || $status === ""
    ) {
        header("Location: editar_produto.php?id=$id&status=erro&msg=Preencha todos os campos");
        exit;
    }

    // Se "não perecível", validade = 0
    if ($nao_perecivel == 1) {
        $validade = 0;
    }

    // Atualização
    $stmt = $sql->prepare("
        UPDATE produtos SET 
            nome=?, categoria_id=?, fornecedor_id=?, descricao=?, preco_venda=?, 
            estoque=?, status=?, validade_padrao_meses=?
        WHERE id=?
    ");

    $stmt->bind_param(
        "siisdisii",
        $nome, $categoria_id, $fornecedor_id, $descricao, $preco_venda,
        $estoque, $status, $validade,
        $id
    );

    if (!$stmt->execute()) {
        die("Erro ao atualizar: " . $stmt->error);
    }
    $stmt->close();

    // ------------------------ FOTO ---------------------------
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

        $dir_img = $_SERVER['DOCUMENT_ROOT'] . "/TCC_FWS/IMG_Produtos/";

        // apagar antiga se existir
        if (!empty($produto['foto_produto']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $produto['foto_produto'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . $produto['foto_produto']);
        }

        $foto_tmp = $_FILES['foto']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        $novo_nome = $id . "." . $ext;
        $path_final = $dir_img . $novo_nome;

        list($w, $h, $tipo) = getimagesize($foto_tmp);

        switch ($tipo) {
            case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($foto_tmp); break;
            case IMAGETYPE_PNG:  $src = imagecreatefrompng($foto_tmp); break;
            case IMAGETYPE_WEBP: $src = imagecreatefromwebp($foto_tmp); break;
            default:
                header("Location: editar_produto.php?id=$id&status=erro&msg=Imagem inválida");
                exit;
        }

        $nw = 1000;
        $nh = 700;
        $new = imagecreatetruecolor($nw, $nh);

        if ($tipo == IMAGETYPE_PNG || $tipo == IMAGETYPE_WEBP) {
            imagealphablending($new, false);
            imagesavealpha($new, true);
        }

        imagecopyresampled($new, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        switch ($tipo) {
            case IMAGETYPE_JPEG: imagejpeg($new, $path_final, 90); break;
            case IMAGETYPE_PNG:  imagepng($new, $path_final); break;
            case IMAGETYPE_WEBP: imagewebp($new, $path_final, 90); break;
        }

        imagedestroy($src);
        imagedestroy($new);

        $foto_url = "/TCC_FWS/IMG_Produtos/" . $novo_nome;

        $stmt = $sql->prepare("UPDATE produtos SET foto_produto=? WHERE id=?");
        $stmt->bind_param("si", $foto_url, $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: editar_produto.php?id=$id&status=sucesso&msg=Produto atualizado!");
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
        background: #fff8e1;
        font-family: "Poppins", sans-serif;
    }

    #fund {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 250px;
        background: black;
    }

    #conteudo-principal {
        margin-left: 250px;
        padding: 40px;
    }

    .container {
        max-width: 700px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px #0002;
    }

    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #d11b1b;
        font-weight: bold;
    }

    input,
    select,
    textarea {
        width: 100%;
        padding: 10px;
        border: 2px solid #f4a01d;
        border-radius: 5px;
        margin-bottom: 12px;
    }

    #preview {
        max-width: 300px;
        max-height: 200px;
        display: block;
        margin-top: 10px;
        border-radius: 10px;
    }

    .btn-primary {
        background: #f4a01d;
        border: none;
        font-weight: bold;
    }

    .btn-primary:hover {
        background: #c87f17;
    }
    </style>
</head>

<body>

    <div id="conteudo-principal">
        <div class="container">
            <h2>Editar Produto</h2>

            <?php if(isset($_GET['status'])): ?>
            <div class="alert <?= $_GET['status']=='erro'?'alert-danger':'alert-success' ?>">
                <?= htmlspecialchars($_GET['msg']) ?>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <label>Nome</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required>

                <label>Categoria</label>
                <select name="categoria_id" required>
                    <option value="">Selecione...</option>
                    <?php
                $res = $sql->query("SELECT id, nome FROM categorias ORDER BY nome");
                while($row = $res->fetch_assoc()):
                ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id']==$produto['categoria_id']?'selected':'' ?>>
                        <?= htmlspecialchars($row['nome']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>

                <label>Fornecedor</label>
                <select name="fornecedor_id" required>
                    <option value="">Selecione...</option>
                    <?php
                $res = $sql->query("SELECT id, nome FROM fornecedores ORDER BY nome");
                while($row = $res->fetch_assoc()):
                ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id']==$produto['fornecedor_id']?'selected':'' ?>>
                        <?= htmlspecialchars($row['nome']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>

                <label>Descrição</label>
                <textarea name="descricao" rows="3"><?= htmlspecialchars($produto['descricao']) ?></textarea>

                <label>Foto</label>
                <input type="file" name="foto" accept="image/*">
                <img id="preview" src="<?= $produto['foto_produto'] ? $produto['foto_produto'] : '#' ?>"
                    style="<?= $produto['foto_produto']?'display:block;':'display:none;' ?>">

                <label>Preço</label>
                <input type="text" name="preco_venda" id="preco_venda"
                    value="<?= number_format($produto['preco_venda'],2,',','.') ?>" required>

                <label>Estoque</label>
                <input type="number" name="estoque" value="<?= $produto['estoque'] ?>" min="0" required>

                <label>Validade</label>
                <div style="display:flex;gap:10px;align-items:center;">
                    <input type="number" id="validade" name="validade" value="<?= $produto['validade_padrao_meses'] ?>"
                        min="0" max="120">
                    <span id="validade_text">meses</span>
                </div>

                <label>
                    <input type="checkbox" id="nao_perecivel" name="nao_perecivel"
                        <?= $produto['validade_padrao_meses']==0 ? 'checked' : '' ?>>
                    Produto não perecível
                </label>

                <label>Status</label>
                <select name="status" required>
                    <option value="ativo" <?= $produto['status']=='ativo'?'selected':'' ?>>Ativo</option>
                    <option value="inativo" <?= $produto['status']=='inativo'?'selected':'' ?>>Inativo</option>
                </select>

                <button class="btn btn-primary w-100 mt-3">Salvar Alterações</button>
                <a href="lista_produtos.php" class="btn btn-danger w-100 mt-2">Voltar</a>

            </form>
        </div>
    </div>

    <script>
    $('#preco_venda').mask('#.##0,00', {
        reverse: true
    });

    $('#foto').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => $('#preview').attr('src', e.target.result).show();
            reader.readAsDataURL(file);
        }
    });

    // Validade
    $('#validade').on('input', function() {
        let v = parseInt($(this).val());
        $('#validade_text').text(v == 1 ? 'mês' : 'meses');
    });

    // Não perecível
    $('#nao_perecivel').on('change', function() {
        if ($(this).is(':checked')) {
            $('#validade').val(0).prop('disabled', true);
            $('#validade_text').text('n/a');
        } else {
            $('#validade').prop('disabled', false);
            $('#validade').val(1);
            $('#validade_text').text('mês');
        }
    });
    </script>

</body>

</html>

<?php
$sql->close();
?>