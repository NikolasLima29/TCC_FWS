<?php
include "../../conn.php";

$fornecedor_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Buscar nome do fornecedor
$fornecedor_query = "SELECT nome FROM fornecedores WHERE id = $fornecedor_id LIMIT 1";
$fornecedor_result = $sql->query($fornecedor_query);
$fornecedor_nome = ($fornecedor_result->num_rows > 0) ? $fornecedor_result->fetch_assoc()['nome'] : "Fornecedor desconhecido";

// Buscar produtos deste fornecedor
$query = "SELECT p.id, p.nome, p.preco_venda, p.estoque
          FROM produtos p
          WHERE p.fornecedor_id = $fornecedor_id
          ORDER BY p.id ASC";
$result = $sql->query($query);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Produtos do Fornecedor - <?php echo htmlspecialchars($fornecedor_nome); ?></title>
<link rel="icon" type="image/x-icon" href="../../logotipo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body {
    background-color: #fff8e1;
    font-family: "Poppins", sans-serif;
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

/* 🔹 Conteúdo */
#conteudo-principal {
    margin-left: 250px;
    padding: 40px;
}

.container {
    max-width: 900px;
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

table th, table td {
    text-align: center;
}
</style>
</head>
<body>

<div class="container-fluid">
    <div class="row flex-nowrap">

        <!-- 🔹 NAVBAR LATERAL COMPLETA -->
        <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
            <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100" id="menu">
                <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                    <li id="logo-linha">
                        <img src="../../menu_principal/IMG/logo_linhas.png" alt="Logo">
                    </li>

                    <li class="nav-item">
                        <a href="/TCC_FWS/FWS_ADM/menu_principal/HTML/menu_principal1.html" id="cor-fonte" class="nav-link align-middle px-0">
                            <img src="../../menu_principal/IMG/painelgeral.png">
                            <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                        </a>
                    </li>

                    <li>
                        <a href="/TCC_FWS/FWS_ADM/fast_service/HTML/fast_service.php" id="cor-fonte" class="nav-link align-middle px-0">
                            <img src="../../menu_principal/IMG/fastservice.png">
                            <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                        </a>
                    </li>

                    <li>
                        <a href="#" id="cor-fonte" class="nav-link align-middle px-0">
                            <img src="../../menu_principal/IMG/financeiro.png">
                            <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                        </a>
                    </li>

                    <li>
                        <a href="#" id="cor-fonte" class="nav-link align-middle px-0">
                            <img src="../../menu_principal/IMG/vendaspai.png">
                            <span class="ms-1 d-none d-sm-inline">Vendas</span>
                        </a>
                    </li>

                    <li>
                        <a href="/TCC_FWS/FWS_ADM/estoque/HTML/estoque.php" id="cor-fonte" class="nav-link align-middle px-0">
                            <img src="../../menu_principal/IMG/estoque.png">
                            <span class="ms-1 d-none d-sm-inline">Estoque</span>
                        </a>
                    </li>

                    <li>
                        <a href="/TCC_FWS/FWS_ADM/produtos/HTML/listar_produtos.php" id="cor-fonte" class="nav-link align-middle px-0">
                            <img src="../../menu_principal/IMG/produtos.png">
                            <span class="ms-1 d-none d-sm-inline">Produtos</span>
                        </a>
                    </li>

                    <li>
                        <a href="/TCC_FWS/FWS_ADM/fornecedores/HTML/listar_fornecedores.php" id="cor-fonte" class="nav-link align-middle px-0">
                            <img src="../../menu_principal/IMG/fornecedor.png">
                            <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                        </a>
                    </li>

                    <li>
                        <a href="#" id="cor-fonte" class="nav-link align-middle px-0">
                            <img src="../../menu_principal/IMG/funcionarios.png">
                            <span class="ms-1 d-none d-sm-inline">Funcionários</span>
                        </a>
                    </li>
                </ul>

                <hr>

                <div class="dropdown pb-4">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                       id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="https://github.com/mdo.png" alt="usuário" width="30" height="30" class="rounded-circle">
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
        <!-- 🔹 FIM DA NAVBAR -->

        <!-- 🔹 CONTEÚDO PRINCIPAL -->
        <div class="col py-3" id="conteudo-principal">
            <div class="container">

                <h2>Produtos do Fornecedor: <span style="color:#f4a01d;"><?php echo htmlspecialchars($fornecedor_nome); ?></span></h2>

                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td>R$ <?php echo number_format($row['preco_venda'],2,',','.'); ?></td>
                                <td><?php echo $row['estoque']; ?></td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="4">Nenhum produto encontrado para este fornecedor.</td>
                        </tr>
                    <?php endif; ?>

                    </tbody>
                </table>

                <a href="lista_fornecedores.php" class="btn btn-dark mt-3">⬅ Voltar para Fornecedores</a>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php $sql->close(); ?>
