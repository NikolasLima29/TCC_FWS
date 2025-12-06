<?php 
include "../../conn.php";
session_start();

// Verifica login
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id = $_SESSION['usuario_id_ADM'];

// Busca nome do ADM
$stmt = $sql->prepare("SELECT nome FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nome_adm);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Entrada de dados
    $nome = trim($_POST['nome'] ?? '');
    $categoria_id = intval($_POST['categoria_id'] ?? 0);
    $fornecedor_id = intval($_POST['fornecedor_id'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $preco_venda = trim($_POST['preco_venda'] ?? '');
    $preco_compra = trim($_POST['preco_compra'] ?? '');
    $estoque = trim($_POST['estoque'] ?? '');
    $status = trim($_POST['status'] ?? '');
    
    // Validade e perecibilidade
    $nao_perecivel = isset($_POST['nao_perecivel']) ? 1 : 0;
    $validade = trim($_POST['validade_padrao_meses'] ?? '');

    // Converter preço "12,99" → 12.99
    if ($preco_venda !== "") {
        $preco_venda = str_replace('.', '', $preco_venda);
        $preco_venda = str_replace(',', '.', $preco_venda);
        $preco_venda = floatval($preco_venda);
    }
    if ($preco_compra !== "") {
        $preco_compra = str_replace('.', '', $preco_compra);
        $preco_compra = str_replace(',', '.', $preco_compra);
        $preco_compra = floatval($preco_compra);
    }

    // Validade final
    if ($nao_perecivel) {
        $validade = 0;
    } else {
        $validade = intval($validade);
        if ($validade < 1) {
            header("Location: cadastro_produto.php?status=erro&msg=Validade inválida");
            exit;
        }
    }

    // Validação simples
    if ($nome === "" || $categoria_id == 0 || $fornecedor_id == 0 || 
        $preco_venda === "" || $preco_compra === "" || $estoque === "" || $status === "") {

        header("Location: cadastro_produto.php?status=erro&msg=Preencha todos os campos");
        exit;
    }

    // Verificar duplicidade
    $stmt = $sql->prepare("SELECT COUNT(*) FROM produtos WHERE nome=?");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        header("Location: cadastro_produto.php?status=erro&msg=Produto já cadastrado");
        exit;
    }

    // Inserir produto
    $query = "INSERT INTO produtos 
        (nome, categoria_id, fornecedor_id, descricao, preco_venda, preco_compra, estoque, status, criado_em, validade_padrao_meses)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

    $stmt = $sql->prepare($query);
    $stmt->bind_param("siisddisi",
        $nome, $categoria_id, $fornecedor_id, $descricao,
        $preco_venda, $preco_compra, $estoque, $status, $validade
    );

    if (!$stmt->execute()) {
        die("Erro ao cadastrar: " . $stmt->error);
    }

    $id_produto = $stmt->insert_id;
    $stmt->close();

   // Upload de imagem
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {

    $dir_img = $_SERVER['DOCUMENT_ROOT'] . "/Fws/IMG_Produtos/";
    $foto_tmp = $_FILES['foto']['tmp_name'];

    // Verifica se realmente é uma imagem
    $info_imagem = getimagesize($foto_tmp);
    if ($info_imagem === false) {
        die("O arquivo enviado não é uma imagem válida.");
    }

    // Pegando a extensão real do arquivo
    $extensao = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $tipo_mime = $info_imagem['mime'];

    // Corrige extensão conforme tipo MIME real
    switch ($tipo_mime) {
        case 'image/jpeg':
            $extensao = 'jpg';
            break;
        case 'image/png':
            $extensao = 'png';
            break;
        case 'image/webp':
            $extensao = 'webp';
            break;
        default:
            die("Formato de imagem não suportado.");
    }

    // Nome final do arquivo
    $nome_arquivo = $id_produto . "." . $extensao;
    $foto_path = $dir_img . $nome_arquivo;

    // ----- Redimensionar a imagem -----
    list($largura_original, $altura_original) = $info_imagem;

    $nova_largura  = 1000;
    $nova_altura   = 700;

    // Criar nova imagem
    $imagem_redimensionada = imagecreatetruecolor($nova_largura, $nova_altura);

    // Abrir imagem original
    switch ($extensao) {
        case 'jpg':
        case 'jpeg':
            $imagem_original = @imagecreatefromjpeg($foto_tmp);
            break;
        case 'png':
            $imagem_original = @imagecreatefrompng($foto_tmp);
            imagealphablending($imagem_redimensionada, false);
            imagesavealpha($imagem_redimensionada, true);
            break;
        case 'webp':
            $imagem_original = @imagecreatefromwebp($foto_tmp);
            break;
    }

    if (!$imagem_original) {
        die("Falha ao abrir a imagem. Verifique se o arquivo é válido.");
    }

    // Redimensionar
    imagecopyresampled(
        $imagem_redimensionada, $imagem_original,
        0, 0, 0, 0,
        $nova_largura, $nova_altura,
        $largura_original, $altura_original
    );

    // Salvar imagem final no servidor
    switch ($extensao) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($imagem_redimensionada, $foto_path, 90);
            break;
        case 'png':
            imagepng($imagem_redimensionada, $foto_path);
            break;
        case 'webp':
            imagewebp($imagem_redimensionada, $foto_path, 90);
            break;
    }

    // Limpar memória
    imagedestroy($imagem_original);
    imagedestroy($imagem_redimensionada);

    // Caminho para salvar no banco
    $foto = "/Fws/IMG_Produtos/" . $nome_arquivo;

    // Atualizar no banco
    $stmt = $sql->prepare("UPDATE produtos SET foto_produto=? WHERE id=?");
    $stmt->bind_param("si", $foto, $id_produto);
    $stmt->execute();
    $stmt->close();
}



    header("Location: cadastro_produto.php?status=sucesso&msg=Produto cadastrado!");
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

    /* Barra lateral fixa */
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

    /* Área principal */
    #conteudo-principal {
        margin-left: 250px;
        padding: 40px;
    }

    .container {
        max-width: 750px;
        margin: auto;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
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

    input[type="text"],
    input[type="number"],
    select,
    textarea,
    input[type="file"] {
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

    #preview {
        max-width: 250px;
        max-height: 180px;
        border-radius: 10px;
        border: 2px solid #f4a01d;
        display: block !important;
        /* <--- A CORREÇÃO */
        margin-top: 10px;
        margin-bottom: 15px;
        /* <--- espaço para o próximo campo */
    }


    /* Caixa da validade */
    #validade-container {
        transition: 0.3s ease;
    }

    /* Estilo para campo desativado */
    .desativado {
        background-color: #f0f0f0 !important;
        pointer-events: none;
        opacity: 0.6;
    }

    /* Exibição do campo validade */
    .campo-validade {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    #texto-meses {
        font-weight: bold;
        color: #444;
        min-width: 60px;
    }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap">

            <!-- NAVBAR -->
            <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100"
                    id="menu">

                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                        <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png"></li>

                        <li class="nav-item">
                            <a href="/Fws/FWS_ADM/menu_principal/HTML/menu_principal1.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="/Fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/produtos/HTML/lista_produtos.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a></li>

                        <li><a href="/Fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/funcionarios.png">
                                <span class="ms-1 d-none d-sm-inline">Funcionários</span>
                            </a></li>
                    </ul>

                    <hr>

                    <div class="dropdown pb-4">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                            data-bs-toggle="dropdown">
                            <img src="../../fotodeperfiladm.png" width="30" height="30" class="rounded-circle">
                            <span class="d-none d-sm-inline mx-1"><?= $nome_adm ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark shadow">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="logout.php">Sair</a></li>
                        </ul>
                    </div>

                </div>
            </div>

            <!-- Conteúdo principal -->
            <div class="col py-3" id="conteudo-principal">
                <div class="container">
                    <h2>Cadastro de Produto</h2>

                    <!-- ALERTA -->
                    <?php if(isset($_GET['status']) && isset($_GET['msg'])): ?>
                    <div class="alert <?php echo $_GET['status']=='erro'?'alert-danger':'alert-success'; ?>">
                        <?= htmlspecialchars($_GET['msg']); ?>
                    </div>
                    <?php endif; ?>

                    <!-- FORMULÁRIO -->
                    <form method="POST" enctype="multipart/form-data">

                        <label>Nome do Produto</label>
                        <input type="text" name="nome" required>

                        <label>Categoria</label>
                        <select name="categoria_id" required>
                            <option value="">Selecione...</option>
                            <?php
                            $query = "SELECT id, nome FROM categorias ORDER BY nome";
                            $result = $sql->query($query);
                            while($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>" . $row['nome'] . "</option>";
                            }
                            ?>
                        </select>

                        <label>Fornecedor</label>
                        <select name="fornecedor_id" required>
                            <option value="">Selecione...</option>
                            <?php
                            $query = "SELECT id, nome FROM fornecedores ORDER BY nome";
                            $result = $sql->query($query);
                            while($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>" . $row['nome'] . "</option>";
                            }
                            ?>
                        </select>

                        <label>Descrição</label>
                        <textarea name="descricao" rows="3"></textarea>

                        <label>Foto do Produto</label>
                        <input type="file" name="foto" id="foto" accept="image/*">
                        <img id="preview">

                        <label>Preço de Venda</label>
                        <input type="text" name="preco_venda" id="preco_venda" required>

                        <label>Preço de Compra</label>
                        <input type="text" name="preco_compra" id="preco_compra" required>

                        <label>Quantidade</label>
                        <input type="number" name="estoque" min="0" required>

                        <label>Status</label>
                        <select name="status" required>
                            <option value="">Selecione...</option>
                            <option value="ativo">Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>

                        <hr>

                        <label>
                            <input type="checkbox" id="nao_perecivel" name="nao_perecivel">
                            Produto Não Perecível
                        </label>

                        <div id="validade-container" class="campo-validade">
                            <label>Validade</label>
                            <input type="number" name="validade_padrao_meses" id="validade" min="1" value="1"
                                placeholder="Ex: 12">
                            <span id="texto-meses">mês</span>
                        </div>


                        <button type="submit" class="btn btn-primary mt-3">Cadastrar</button>
                        <a href="lista_produtos.php" class="btn btn-secondary mt-2">Voltar</a>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
    $(document).ready(function() {

    $('#preco_venda').mask('#.##0,00', {
        reverse: true
    });
    $('#preco_compra').mask('#.##0,00', {
        reverse: true
    });

    $('#foto').change(function() {
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

    // Atualizar texto de validade (mês ou meses)
    function atualizarTextoMeses() {
        let valor = parseInt($("#validade").val());
        if (!valor || valor <= 1) {
            $("#texto-meses").text("mês");
        } else {
            $("#texto-meses").text("meses");
        }
    }

    // Chama imediatamente para iniciar como "1 mês"
    atualizarTextoMeses();

    $("#validade").on("input", atualizarTextoMeses);

    // Controle de "Produto Não Perecível"
    $("#nao_perecivel").change(function() {
        if ($(this).is(":checked")) {
            // Se Produto Não Perecível for selecionado, desabilita o campo e o define como 0
            $("#validade").addClass("desativado").val(0);
            $("#texto-meses").text("meses");
            $("#validade").prop('disabled', true); // Desabilita o campo de validade
        } else {
            // Caso contrário, habilita e permite edição
            $("#validade").removeClass("desativado").val(1);
            $("#validade").prop('disabled', false); // Habilita o campo de validade
            atualizarTextoMeses();
        }
    });

});

    </script>

</body>

</html>
<?php $sql->close(); ?>
