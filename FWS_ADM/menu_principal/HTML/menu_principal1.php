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

?>


<!doctype html>
<html lang="pt-br">

<head>
    <title>Principal</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../CSS/menu_principal.css">

    <!-- Bootstrap CSS v5.2.1 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">

</head>

<body>
    <main>
        <div class="container-fluid">
            <div class="row flex-nowrap">
                <div class="col-auto col-md-3 col-xl-2 px-sm-2 px-0 bg-dark" id="fund">
                    <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100"
                        id="menu">
                        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                            <a id="logo-linha"><img src="../IMG/logo_linhas.png"></a>
                            <li class="nav-item">
                                <a href="/Fws/FWS_ADM/menu_principal/HTML/menu_principal1.php" class="nav-link align-middle px-0" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/painelgeral.png"> <span
                                        class="ms-1 d-none d-sm-inline">Painel Geral</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/Fws/FWS_ADM/fast_service/HTML/fast_service.php" class="nav-link align-middle px-0" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/fastservice.png"> <span
                                        class="ms-1 d-none d-sm-inline">Fast Service</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/Fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php" class="nav-link align-middle px-0" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/financeiro.png"> <span
                                        class="ms-1 d-none d-sm-inline">Financeiro</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/Fws/FWS_ADM/menu_vendas/HTML/menu_venda.php" class="nav-link align-middle px-0" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/vendaspai.png"> <span
                                        class="ms-1 d-none d-sm-inline">Vendas</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/Fws/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0"
                                    id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/estoque.png"> <span
                                        class="ms-1 d-none d-sm-inline">Estoque</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/Fws/FWS_ADM/produtos/HTML/cadastro_produto.php"
                                    class="nav-link align-middle px-0" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/produtos.png"> <span
                                        class="ms-1 d-none d-sm-inline">Produtos</span></img>
                                </a>
                            </li>
                            <li>
                                <a href="/Fws/FWS_ADM/fornecedores/HTML/lista_fornecedores.php" class="nav-link align-middle px-0" id="cor-fonte">
                                    <img src="../../menu_principal/IMG/fornecedor.png">
                                    <span class="ms-1 d-none d-sm-inline">Fornecedores</span>
                                </a>
                            </li>
                            <li>
                                <a href="/Fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php" class="nav-link align-middle px-0" id="cor-fonte">
                                    <i class="fs-4 bi-house"></i><img src="../IMG/funcionarios.png"> <span
                                        class="ms-1 d-none d-sm-inline">Funcionários</span></img>
                                </a>
                            </li>
                        </ul>
                        <hr>
                        <div class="dropdown pb-4">
                            <a href="#"
                                class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                                id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="../../fotodeperfiladm.png " width="30" height="30" class="rounded-circle">
                                    <span class="d-none d-sm-inline mx-1"><?= $nome_adm ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                                <li><a class="dropdown-item" href="../../perfil/HTML/perfil.php">Perfil</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../../perfil/HTML/logout.php">Sair da conta</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col py-3">
                    <h3 id="texto">Painel Geral</h3>


                </div>




            </div>


        </div>

    </main>
    <footer>
        <!-- place footer here -->
    </footer>
    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
        integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous">
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
        integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz" crossorigin="anonymous">
    </script>
</body>

</html>
