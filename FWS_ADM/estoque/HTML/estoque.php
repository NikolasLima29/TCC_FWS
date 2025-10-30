<?php
include "../../conn.php";
if (!$sql){
    die("conexão falhou: " . mysqli_error());
}
$sqli = "SELECT p.id, p.nome, c.nome as categoria_nome, f.nome as fornecedor_nome, p.preco_venda, p.estoque, p.status, p.criado_em 
FROM produtos p 
LEFT JOIN categorias c ON p.categoria_id = c.id 
LEFT JOIN fornecedores f ON p.fornecedor_id = f.id";
$result = $sql->query($sqli);
if(!$result){
    die("Erro na consulta: " . $sql->error);
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <title>Estoque de Produtos</title>
    <link rel="icon" type="image/x-icon" href="../../logotipo.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="../CSS/menu_principal.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-iYQeCzEYFbKjA/T2uDLTpkwGzCiq6soy8tYaI1GyVh/UjpbCx/TYkiZhlZB6+fzT" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 100%;
            max-width: 400px;
            margin-bottom: 15px;
            padding: 8px;
            font-size: 16px;
            border: 2px solid #f4a01d;
            border-radius: 4px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: black;
            color: white;
            box-shadow: 0 2px 8px rgba(255, 255, 255, 0.1);
        }

        thead {
            background-color: #f4a01d;
            color: black;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        tbody tr:hover {
            background-color: #f4a01d;
            color: black;
        }

        .status-in-stock {
            color: green;
            font-weight: bold;
        }

        .status-out-stock {
            color: red;
            font-weight: bold;
        }

        /* 🔹 Barra lateral fixa */
        #fund {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px; /* largura fixa */
            background-color: black !important;
            overflow-y: auto;
            z-index: 1000;
        }

        /* 🔹 Área do menu */
        #menu {
            background-color: black;
        }

        /* 🔹 Ajuste do conteúdo principal */
        #conteudo-principal {
            margin-left: 250px; /* igual à largura da barra */
            padding: 20px;
        }

        /* 🔹 Estilo da sidebar */
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

        @import url('../../Fonte_Config/fonte_geral.css');

        @media (max-width: 768px) {
            #fund {
                width: 200px;
            }
            #conteudo-principal {
                margin-left: 200px;
            }
        }
    </style>
</head>

<body>
<main>
    <div class="container-fluid">
        <div class="row flex-nowrap">

            <!-- 🔹 Barra lateral fixa -->
            <div class="col-auto px-sm-2 px-0 bg-dark" id="fund">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-2 text-white min-vh-100" id="menu">
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start">
                        <li id="logo-linha"><img src="../../menu_principal/IMG/logo_linhas.png" alt="Logo"></li>
                        <li class="nav-item">
                            <a href="menu_principal1.html" class="nav-link align-middle px-0" id="cor-fonte">
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
                            <a href="/FWS_ADM/estoque/HTML/estoque.php" class="nav-link align-middle px-0" id="cor-fonte">
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
                <h1>Estoque de Produtos</h1>
                <input type="text" id="filtro" placeholder="Buscar produto..." onkeyup="filtrarTabela()" />
                <table id="tabelaEstoque">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Fornecedor</th>
                            <th>Preço</th>
                            <th>Qtd</th>
                            <th>Status</th>
                            <th>Criação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0){
                            while ($row = $result->fetch_assoc()){
                                echo "<tr>";
                                echo "<td>" . ($row["id"] ?? "id não disponível") . "</td>";
                                echo "<td>" . ($row["nome"] ?? "nome não disponível") . "</td>";
                                echo "<td>" . ($row["categoria_nome"] ?? "categoria não disponível") . "</td>";
                                echo "<td>" . ($row["fornecedor_nome"] ?? "fornecedor não disponível") . "</td>";
                                echo "<td>" . ($row["preco_venda"] ?? "preço não disponível") . "</td>";
                                echo "<td>" . ($row["estoque"] ?? "Quantidade não disponível") . "</td>";
                                echo "<td>" . ($row["status"] ?? "status não disponível") . "</td>";
                                echo "<td>" . ($row["criado_em"] ?? "data não disponível") . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8'>Nenhum registro encontrado</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                <script>
                function filtrarTabela() {
                    const input = document.getElementById("filtro");
                    const filtro = input.value.toLowerCase();
                    const tabela = document.getElementById("tabelaEstoque");
                    const linhas = tabela.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
                    for (let i = 0; i < linhas.length; i++) {
                        const colunaProduto = linhas[i].getElementsByTagName("td")[1];
                        if (colunaProduto) {
                            const texto = colunaProduto.textContent || colunaProduto.innerText;
                            linhas[i].style.display = texto.toLowerCase().includes(filtro) ? "" : "none";
                        }
                    }
                }
                </script>
            </div>
        </div>
    </div>
</main>

<footer></footer>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
    integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"
    integrity="sha384-7VPbUDkoPSGFnVtYi0QogXtr74QeVeeIs99Qfg5YCF+TidwNdjvaKZX19NZ/e6oz"
    crossorigin="anonymous"></script>

</body>
</html>
