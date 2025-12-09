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

$nome_adm = explode(" ", trim($nome_adm))[0];

// Buscar fornecedores
$query = "SELECT id, nome, cnpj, telefone, email FROM fornecedores ORDER BY id ASC";
$result = $sql->query($query);

/* ---------- FUNÇÕES DE FORMATAÇÃO ---------- */

function formatarCNPJ($cnpj) {
    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) !== 14) return $cnpj;
    return preg_replace("/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/", "$1.$2.$3/$4-$5", $cnpj);
}

function formatarTelefone($tel) {
    $tel = preg_replace('/\D/', '', $tel);
    if (strlen($tel) == 11) 
        return preg_replace("/(\d{2})(\d{5})(\d{4})/", "($1) $2-$3", $tel);

    if (strlen($tel) == 10) 
        return preg_replace("/(\d{2})(\d{4})(\d{4})/", "($1) $2-$3", $tel);

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
  <link rel="stylesheet" href="../../menu_principal/CSS/menu_principal.css">

    <style>
        body {
            background-color: #fff8e1;
            font-family: "Poppins", sans-serif;
            margin: 0;
            overflow-x: hidden;
            animation: fadeInBody 0.5s ease;
        }

        @keyframes fadeInBody {
            from { opacity: 0; }
            to { opacity: 1; }
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
            max-width: 1050px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.12);
            animation: fadeInCard 0.6s ease;
        }

        @keyframes fadeInCard {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #ff9100;
            font-weight: bold;
        }

        .btn-cadastro {
            background-color: #ff9100;
            color: white;
            font-weight: bold;
            border: none;
            padding: 10px 18px;
            transition: all 0.25s ease-in-out;
            border-radius: 8px;
        }

        .btn-cadastro:hover {
            transform: scale(1.05);
            background-color: #e68000;
            color: #fff;
            box-shadow: 0 4px 12px rgba(255, 140, 0, 0.45);
        }

        table {
            animation: fadeInTable .6s ease;
        }

        @keyframes fadeInTable {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .table thead.table-dark {
            background-color: #ff9100;
        }

        .table thead.table-dark th {
            background-color: #ff9100;
            color: white;
            border-color: #ff9100;
        }

        tr {
            transition: background .18s ease, transform .15s ease;
        }

        tr:hover {
            background-color: #fff3cd;
            transform: scale(1.007);
        }

        .btn-edit, .btn-produtos {
            transition: all 0.2s ease;
            font-weight: bold;
        }

        .btn-edit {
            background-color: #f4a01d;
            color: black;
            border: none;
        }

        .btn-edit:hover {
            background-color: #d68c19;
            color: white;
            transform: scale(1.07);
        }

        .btn-produtos {
            background-color: #1b8914;
            color: white;
            border: none;
        }

        .btn-produtos:hover {
            background-color: #157d10;
            transform: scale(1.07);
        }

        .sem-quebra { white-space: nowrap; }
        .col-nome { max-width: 220px; white-space: normal; word-wrap: break-word; }
        .col-email { max-width: 280px; white-space: normal; word-wrap: break-word; }

        /* ========== ESTILOS DO CAMPO DE PESQUISA ========== */
        .search-filter-container {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 2px solid #ff9100;
        }

        .search-input {
            width: 100%;
            padding: 12px 15px;
            font-size: 14px;
            border: 2px solid #ff9100;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #f4a01d;
            box-shadow: 0 0 8px rgba(255, 145, 0, 0.3);
            background-color: #fffbf0;
        }

        @import url('../../../FWS_Cliente/Fonte_Config/fonte_geral.css');
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row flex-nowrap">

            <!-- MENU LATERAL COMPLETO (SEM CORTES) -->
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

                        <li><a href="/fws/FWS_ADM/fast_service/HTML/fast_service.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/fastservice.png">
                                <span class="ms-1 d-none d-sm-inline">Fast Service</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_financeiro/HTML/menu_financeiro.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/financeiro.png">
                                <span class="ms-1 d-none d-sm-inline">Financeiro</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/menu_vendas/HTML/menu_venda.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
                                <img src="../../menu_principal/IMG/vendaspai.png">
                                <span class="ms-1 d-none d-sm-inline">Vendas</span>
                            </a></li>

                        <li><a href="/fws/FWS_ADM/estoque/HTML/estoque.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
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

                        <li><a href="/fws/FWS_ADM/funcionarios/HTML/menu_funcionarios.php"
                                class="nav-link align-middle px-0" id="cor-fonte">
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
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../perfil/HTML/logout.php">Sair</a></li>
                        </ul>
                    </div>

                </div>
            </div>
            <!-- FIM MENU SEM CORTES -->

            <!-- CONTEÚDO PRINCIPAL -->
            <div class="col py-3" id="conteudo-principal">
                <div class="container">

                    <h2>Fornecedores Cadastrados</h2>

                    <!-- ========== CAMPO DE PESQUISA ========== -->
                    <div class="search-filter-container">
                        <input 
                            type="text" 
                            id="searchInput" 
                            class="search-input" 
                            placeholder="🔍 Pesquisar por nome do fornecedor..."
                        >
                    </div>

                    <!-- BOTÃO NO TOPO -->
                    <div class="d-flex justify-content-end mb-3">
                        <a href="cadastrar_fornecedor.php" class="btn btn-cadastro">
                            + Cadastrar Novo Fornecedor
                        </a>
                    </div>

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
                                    <td><?= $row['id']; ?></td>
                                    <td class="col-nome"><?= htmlspecialchars($row['nome']); ?></td>
                                    <td class="sem-quebra"><?= htmlspecialchars(formatarCNPJ($row['cnpj'])); ?></td>
                                    <td class="sem-quebra"><?= htmlspecialchars(formatarTelefone($row['telefone'])); ?></td>
                                    <td class="col-email"><?= htmlspecialchars($row['email']); ?></td>

                                    <td>
                                        <a href="produtos_por_fornecedor.php?id=<?= $row['id']; ?>" 
                                           class="btn btn-produtos btn-sm">Ver Produtos</a>
                                    </td>

                                    <td>
                                        <a href="editar_fornecedor.php?id=<?= $row['id']; ?>" 
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

                </div>
            </div>

        </div>
    </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Animação suave ao carregar linhas da tabela
    document.querySelectorAll("tbody tr").forEach((tr, i) => {
        tr.style.opacity = "0";
        tr.style.transform = "translateY(10px)";
        setTimeout(() => {
            tr.style.transition = "0.4s";
            tr.style.opacity = "1";
            tr.style.transform = "translateY(0)";
        }, 80 * i);
    });

    // ========== FUNÇÃO DE BUSCA EM TEMPO REAL ==========
    function filterTable() {
        const searchInput = document.getElementById('searchInput').value.toLowerCase();
        const table = document.querySelector('tbody');
        const rows = table.querySelectorAll('tr');
        let visibleCount = 0;

        rows.forEach(row => {
            if (row.querySelector('td:nth-child(1)') === null) return;

            const nome = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

            if (nome.includes(searchInput)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Mensagem quando não há resultados
        if (visibleCount === 0) {
            if (!table.querySelector('.no-results')) {
                const noResults = document.createElement('tr');
                noResults.className = 'no-results';
                noResults.innerHTML = '<td colspan="7" style="text-align: center; padding: 20px; color: #999;">Nenhum fornecedor encontrado.</td>';
                table.appendChild(noResults);
            }
        } else {
            const noResults = table.querySelector('.no-results');
            if (noResults) noResults.remove();
        }
    }

    // Listener para a busca
    document.getElementById('searchInput').addEventListener('keyup', filterTable);
</script>

</body>
</html>

<?php $sql->close(); ?>
