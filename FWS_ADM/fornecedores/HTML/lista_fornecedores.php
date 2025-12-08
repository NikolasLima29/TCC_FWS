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

// Sobrescreve o nome para conter apenas o primeiro nome
$nome_adm = explode(" ", trim($nome_adm))[0];

// Buscar todos os fornecedores
$query = "SELECT id, nome, cnpj, telefone, email FROM fornecedores ORDER BY id ASC";
$result = $sql->query($query);

/* ------------------------------
      FUNÇÕES DE MÁSCARA
------------------------------ */

// Máscara de CNPJ: 00.000.000/0000-00
function formatarCNPJ($cnpj) {
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) !== 14) return $cnpj; 
    return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $cnpj);
}

// Mascara telefone (fixo ou celular)
function formatarTelefone($tel) {
    $tel = preg_replace('/\D/', '', $tel);

    if (strlen($tel) == 11) { 
        return preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $tel);
    } elseif (strlen($tel) == 10) {
        return preg_replace("/(\d{2})(\d{4})(\d{4})/", "($1) $2-$3", $tel);
    }

    return $tel;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Lista de Fornecedores</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
        background-color: #fff8e1;
        font-family: "Poppins", sans-serif;
        margin: 0;
    }

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

    #conteudo-principal {
        margin-left: 250px;
        padding: 40px;
    }

    .container {
        max-width: 1000px;
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #ff9100;
        font-weight: bold;
    }

    table th,
    table td {
        text-align: center;
        vertical-align: middle;
    }

    .table thead.table-dark {
        background-color: #ff9100;
    }

    .table thead.table-dark th {
        background-color: #ff9100;
        color: white;
        border-color: #ff9100;
        border-right: 1px solid #e68000;
    }

    .table thead.table-dark th:last-child {
        border-right: none;
    }

    .btn-edit {
        background-color: #f4a01d;
        border: none;
        color: black;
        font-weight: bold;
    }

    .btn-edit:hover {
        background-color: #d68c19;
        color: white;
    }

    .btn-produtos {
        background-color: #1b8914;
        border: none;
        color: white;
        font-weight: bold;
    }

    .btn-produtos:hover {
        background-color: #157d10;
        color: white;
    }

    .btn-cadastro {
        background-color: #ff9100;
        border: none;
        color: white;
        font-weight: bold;
    }

    .btn-cadastro:hover {
        background-color: #e68000;
        color: white;
    }

    /* 🔹 impede quebra de linha no CNPJ e telefone */
    .sem-quebra {
        white-space: nowrap;
    }

    /* 🔹 Nome com quebra e largura reduzida */
    .col-nome {
        max-width: 220px;
        white-space: normal;
        word-wrap: break-word;
    }

    /* 🔹 Email mais largo e SEM cortar */
    .col-email {
        max-width: 280px;
        white-space: normal;
        word-wrap: break-word;
    }

    @import url('../../Fonte_Config/fonte_geral.css');
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
                            <a href="/fws/FWS_ADM/menu_principal/HTML/menu_principal1.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/painelgeral.png">
                                <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                            </a>
                        </li>

                        <li><a href="/fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                id="cor-fonte">
                                <img src="../../menu_principal/IMG/estoque.png">
                                <span class="ms-1 d-none d-sm-inline">Estoque</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/produtos/HTML/lista_produtos.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/produtos.png">
                                <span class="ms-1 d-none d-sm-inline">Produtos</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fornecedor.png">
                                <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link align-middle px-0" id="cor-fonte">
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
                            <li><a class="dropdown-item" href="../../perfil/HTML/perfil.php">Perfil</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="../../perfil/HTML/logout.php">Sair</a></li>
                        </ul>
                    </div>

                </div>
            </div>

            <!-- Conteúdo principal -->
            <div class="col py-3" id="conteudo-principal">
                <div class="container">
                    <h2>Fornecedores Cadastrados</h2>

                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>CNPJ</th>
                                <th>Telefone</th>
                                <th>Email</th>
                                <th>Produtos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>

                                <td class="col-nome"><?php echo htmlspecialchars($row['nome']); ?></td>

                                <td class="sem-quebra"><?php echo htmlspecialchars(formatarCNPJ($row['cnpj'])); ?></td>

                                <td class="sem-quebra"><?php echo htmlspecialchars(formatarTelefone($row['telefone'])); ?></td>

                                <td class="col-email"><?php echo htmlspecialchars($row['email']); ?></td>

                                <td>
                                    <a href="produtos_por_fornecedor.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-produtos btn-sm">Ver Produtos</a>
                                </td>

                                <td>
                                    <a href="editar_fornecedor.php?id=<?php echo $row['id']; ?>"
                                        class="btn btn-edit btn-sm">Editar</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="7">Nenhum fornecedor cadastrado.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>

                    </table>

                    <a href="cadastrar_fornecedor.php" class="btn btn-cadastro mt-3">Cadastrar Novo Fornecedor</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

<?php $sql->close(); ?>
