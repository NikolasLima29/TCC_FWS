<?php
session_start();
include "../../conn.php";

/* ============================================================
   PROTEÇÃO DE LOGIN
   ============================================================ */
if (!isset($_SESSION['usuario_id_ADM'])) {
    header("Location: ../../index.html?status=erro&msg=Faça login primeiro");
    exit;
}

$id = $_SESSION['usuario_id_ADM'];

/* ============================================================
   CARREGAR DADOS DO FUNCIONÁRIO (para navbar)
   ============================================================ */
$stmt = $sql->prepare("SELECT nome, cpf, email, nivel_permissao FROM funcionarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nome, $cpf, $email, $nivel);
$stmt->fetch();
$stmt->close();

function nivel($n) {
    return $n == 1 ? "Atendente" : "Gerente";
}

$foto = "../../fotodeperfiladm.png";

/* ============================================================
   PROCESSAR EXCLUSÃO
   ============================================================ */
if (isset($_GET['delete'])) {
    $id_delete = intval($_GET['delete']);
    $sql->query("DELETE FROM despesas WHERE id = $id_delete");

    header("Location: despesas.php?status=sucesso&msg=Despesa excluída!");
    exit;
}

/* ============================================================
   PROCESSAR CADASTRO OU EDIÇÃO
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_edit      = intval($_POST['id_edit'] ?? 0);
    $data_despesa = trim($_POST['data_despesa'] ?? '');
    $tipo         = trim($_POST['tipo'] ?? '');
    $valor        = trim($_POST['valor'] ?? '');
    $descricao    = trim($_POST['descricao'] ?? '');

    // Converter valor
    if ($valor !== "") {
        $valor = str_replace('.', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valor = floatval($valor);
    }

    if ($data_despesa === "" || $tipo === "" || $valor === "") {
        header("Location: despesas.php?status=erro&msg=Preencha todos os campos obrigatórios!");
        exit;
    }

    if ($id_edit > 0) {
        $stmt = $sql->prepare("UPDATE despesas SET data_despesa=?, tipo=?, valor=?, descricao=? WHERE id=?");
        $stmt->bind_param("ssdsi", $data_despesa, $tipo, $valor, $descricao, $id_edit);
        $stmt->execute();
        $stmt->close();
        header("Location: despesas.php?status=sucesso&msg=Despesa atualizada!");
        exit;

    } else {
        $stmt = $sql->prepare("INSERT INTO despesas (data_despesa, tipo, valor, descricao)
                               VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $data_despesa, $tipo, $valor, $descricao);
        $stmt->execute();
        $stmt->close();
        header("Location: despesas.php?status=sucesso&msg=Despesa registrada!");
        exit;
    }
}

/* ============================================================
   BUSCAR DADOS PARA EDIÇÃO
   ============================================================ */
$edit_item = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $res = $sql->query("SELECT * FROM despesas WHERE id=$id_edit");
    $edit_item = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Controle de Despesas</title>
<link rel="icon" type="image/x-icon" href="../../logotipo.png">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>

<style>
/* ======== DESIGN PADRÃO DO SISTEMA ======== */

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

#conteudo-principal {
    margin-left: 250px;
    padding: 40px;
}

/* Formulário */
.container-box {
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

input, select, textarea {
    width: 100%;
    padding: 10px;
    border: 2px solid #f4a01d;
    border-radius: 5px;
    margin-bottom: 15px;
}

textarea {
    resize: none;
}

/* Botões */
.btn-primary {
    background-color: #f4a01d;
    border: none;
    color: black;
    font-weight: bold;
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
}

/* Lista */
.item-despesa {
    padding: 12px;
    border: 2px solid #f4a01d;
    border-radius: 8px;
    background: #fff4d0;
    margin-bottom: 12px;
}

.edit-btn, .delete-btn {
    font-size: 14px;
    padding: 4px 10px;
    color: white;
}

.edit-btn { background:#007bff; }
.delete-btn { background:#d11b1b; }
</style>
</head>

<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <!-- NAVBAR COMPLETA -->
        <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2
                        text-white min-vh-100" id="menu">

                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                    <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png"></li>

                    <li>
                        <a href="/TCC_FWS/FWS_ADM/menu_principal/HTML/menu_principal1.html"
                           class="nav-link align-middle px-0" id="cor-fonte">
                            <img src="../../menu_principal/IMG/painelgeral.png">
                            <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                        </a>
                    </li>

                    <li><a href="#" class="nav-link px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/fastservice.png">
                        <span class="ms-1 d-none d-sm-inline">Fast Service</span></a>
                    </li>

                    <li><a href="#" class="nav-link px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/financeiro.png">
                        <span class="ms-1 d-none d-sm-inline">Financeiro</span></a>
                    </li>

                    <li><a href="#" class="nav-link px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/vendaspai.png">
                        <span class="ms-1 d-none d-sm-inline">Vendas</span></a>
                    </li>

                    <li><a href="/TCC_FWS/FWS_ADM/estoque/HTML/estoque.php" class="nav-link px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/estoque.png">
                        <span class="ms-1 d-none d-sm-inline">Estoque</span></a>
                    </li>

                    <li><a href="/TCC_FWS/FWS_ADM/produtos/HTML/cadastro_produto.php"
                           class="nav-link px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/produtos.png">
                        <span class="ms-1 d-none d-sm-inline">Produtos</span></a>
                    </li>

                    <li><a href="/TCC_FWS/FWS_ADM/fornecedores/HTML/listar_fornecedores.php"
                           class="nav-link px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/fornecedor.png">
                        <span class="ms-1 d-none d-sm-inline">Fornecedores</span></a>
                    </li>

                    <li><a href="#" class="nav-link px-0" id="cor-fonte">
                        <img src="../../menu_principal/IMG/funcionarios.png">
                        <span class="ms-1 d-none d-sm-inline">Funcionários</span></a>
                    </li>
                </ul>

                <hr>

                <!-- Usuário -->
                <div class="dropdown pb-4">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                       data-bs-toggle="dropdown">
                        <img src="<?= $foto ?>" width="30" height="30" class="rounded-circle">
                        <span class="d-none d-sm-inline mx-1"><?= $nome ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark shadow">
                        <li><a class="dropdown-item" href="../perfil/perfil.php">Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../../logout.php">Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CONTEÚDO PRINCIPAL -->
        <div class="col py-3" id="conteudo-principal">

            <div class="container-box">

                <h2><?= $edit_item ? "Editar Despesa" : "Registrar Despesa" ?></h2>

                <!-- ALERTA -->
                <?php if(isset($_GET['status'])): ?>
                    <div class="alert <?= $_GET['status']=="erro" ? "alert-danger" : "alert-success" ?>">
                        <?= htmlspecialchars($_GET['msg']) ?>
                    </div>
                <?php endif; ?>

                <!-- FORMULÁRIO -->
                <form method="POST" id="form-despesa">

                    <input type="hidden" name="id_edit" value="<?= $edit_item['id'] ?? 0 ?>">

                    <label>Data *</label>
                    <input type="date" name="data_despesa" required
                           value="<?= $edit_item['data_despesa'] ?? '' ?>">

                    <label>Tipo *</label>
                    <select name="tipo" required>
                        <option value="">Selecione...</option>
                        <?php
                        $tipos = ['manutencao','agua','luz','internet','outros'];
                        foreach($tipos as $t){
                            $sel = ($edit_item && $edit_item['tipo']==$t) ? "selected" : "";
                            echo "<option value='$t' $sel>".ucfirst($t)."</option>";
                        }
                        ?>
                    </select>

                    <label>Valor *</label>
                    <input type="text" name="valor" id="valor"
                           value="<?= isset($edit_item['valor']) ? number_format($edit_item['valor'],2,",",".") : "" ?>"
                           required>

                    <label>Descrição</label>
                    <textarea name="descricao" rows="4"><?= $edit_item['descricao'] ?? "" ?></textarea>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary w-50" id="cancelar">Cancelar</button>
                        <button type="submit" class="btn btn-primary w-50">
                            <?= $edit_item ? "Salvar Alterações" : "Registrar" ?>
                        </button>
                    </div>

                </form>

                <hr>

                <h3 class="mt-4">Despesas Registradas</h3>

                <?php
                $res = $sql->query("SELECT * FROM despesas ORDER BY data_despesa DESC, id DESC");
                while($d = $res->fetch_assoc()):
                ?>
                <div class="item-despesa">
                    <strong><?= date("d/m/Y", strtotime($d["data_despesa"])) ?></strong><br>
                    Tipo: <?= ucfirst($d["tipo"]) ?><br>
                    Valor: <strong>R$ <?= number_format($d["valor"],2,",",".") ?></strong><br>

                    <?php if ($d["descricao"] != ""): ?>
                        <em><?= htmlspecialchars($d["descricao"]) ?></em><br>
                    <?php endif; ?>

                    <div class="mt-2 d-flex gap-2">
                        <a href="despesas.php?edit=<?= $d['id'] ?>" class="btn edit-btn">Editar</a>
                        <a href="despesas.php?delete=<?= $d['id'] ?>" class="btn delete-btn"
                           onclick="return confirm('Excluir esta despesa?')">Excluir</a>
                    </div>
                </div>

                <?php endwhile; ?>

            </div>

        </div>
    </div>
</div>

<script>
// Máscara de valor
$("#valor").mask("#.##0,00", { reverse: true });

// CANCELAR → limpa tudo
$("#cancelar").click(function () {
    $("#form-despesa")[0].reset();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php $sql->close(); ?>
