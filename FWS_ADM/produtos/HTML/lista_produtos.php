<?php
include "../../conn.php";

// Buscar todos os produtos
$query = "SELECT p.id, p.nome, c.nome AS categoria, f.nome AS fornecedor, p.preco_venda, p.estoque, p.status 
          FROM produtos p
          LEFT JOIN categorias c ON p.categoria_id = c.id
          LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
          ORDER BY p.id ASC";
$result = $sql->query($query);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Lista de Produtos</title>
<link rel="icon" type="image/x-icon" href="../../logotipo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

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
    max-width: 1000px;
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
    vertical-align: middle;
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

                    <li class="nav-item">
                        <a href="/TCC_FWS/FWS_ADM/menu_principal/HTML/menu_principal1.html" class="nav-link align-middle px-0" id="cor-fonte">
                            <img src="../../menu_principal/IMG/painelgeral.png"> 
                            <span class="ms-1 d-none d-sm-inline">Painel Geral</span>
                        </a>
                    </li>

                    <li>
                        <a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                            <img src="../../menu_principal/IMG/fastservice.png"> 
                            <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                        </a>
                    </li>

                    <li>
                        <a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                            <img src="../../menu_principal/IMG/financeiro.png"> 
                            <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                        </a>
                    </li>

                    <li>
                        <a href="#" class="nav-link align-middle px-0" id="cor-fonte">
                            <img src="../../menu_principal/IMG/vendaspai.png"> 
                            <span class="ms-1 d-none d-sm-inline">Vendas</span>
                        </a>
                    </li>

                    <li>
                        <a href="/TCC_FWS/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0" id="cor-fonte">
                            <img src="../../menu_principal/IMG/estoque.png"> 
                            <span class="ms-1 d-none d-sm-inline">Estoque</span>
                        </a>
                    </li>

                    <li>
                        <a href="/TCC_FWS/FWS_ADM/produtos/HTML/cadastro_produto.php" class="nav-link align-middle px-0" id="cor-fonte">
                            <img src="../../menu_principal/IMG/produtos.png"> 
                            <span class="ms-1 d-none d-sm-inline">Produtos</span>
                        </a>
                    </li>

                    <li>
                        <a href="#" class="nav-link align-middle px-0" id="cor-fonte">
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

        <!-- 🔹 Conteúdo principal -->
        <div class="col py-3" id="conteudo-principal">
            <div class="container">
                <h2>Produtos Cadastrados</h2>

                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Fornecedor</th>
                            <th>Preço</th>
                            <th>Estoque</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['categoria']); ?></td>
                                <td><?php echo htmlspecialchars($row['fornecedor']); ?></td>
                                <td>R$ <?php echo number_format($row['preco_venda'],2,',','.'); ?></td>
                                <td><?php echo $row['estoque']; ?></td>
                                <td><?php echo $row['status'] ? 'Ativo' : 'Inativo'; ?></td>
                                <td>
                                    <a href="editar_produto.php?id=<?php echo $row['id']; ?>" class="btn btn-edit btn-sm">Editar</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8">Nenhum produto cadastrado.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>

                <a href="cadastro_produto.php" class="btn btn-dark mt-3">Cadastrar Novo Produto</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php $sql->close(); ?>
