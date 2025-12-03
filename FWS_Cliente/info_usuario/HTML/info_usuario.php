<?php
include "../../conn.php";
session_start();

$id = $_SESSION['usuario_id'] ?? 1;
$status = "";
$msg = "";

// Atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $data_nascimento = $_POST['data_nascimento'];

    $check = $conn->prepare("SELECT id FROM usuarios WHERE (email=? OR telefone=?) AND id<>?");
    $check->bind_param("ssi", $email, $telefone, $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $status = "erro";
        $msg = "E-mail ou telefone já cadastrados.";
    } else {
        $sql = "UPDATE usuarios SET nome=?, email=?, telefone=?, data_nascimento=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $email, $telefone, $data_nascimento, $id);
        $stmt->execute();

        $status = "sucesso";
        $msg = "Dados atualizados com sucesso!";
    }
}

// Buscar dados do usuário
$sql = "SELECT * FROM usuarios WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) die("Usuário não encontrado.");
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <title>Informações do Usuário</title>
    <link rel="icon" type="image/x-icon" href="../../cadastro/IMG/Shell.png">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../CSS/info_usuario.css">
</head>

<body>
<header id="header">
    <div class="logo">
        <a href="../../index.php"><img src="../../index/IMG/shell_select.png" alt="logo" /></a>
    </div>

    <button class="menu-toggle" aria-label="Abrir menu">
        <i class="fas fa-bars"></i>
    </button>

    <nav class="nav-links">
        <ul class="ul align-items-center">
            <li><a href="../../produto/HTML/produto.php">Produtos</a></li>
            <li><a href="../../meus_pedidos/HTML/Meus_pedidos.php">Meus pedidos</a></li>
            <li><a href="../../tela_sobre_nos/HTML/sobre_nos.php">Sobre nós</a></li>
        </ul>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleButton = document.querySelector('.menu-toggle');
        const navLinks = document.querySelector('nav.nav-links');

    if (!toggleButton || !navLinks) return;

    toggleButton.setAttribute('aria-expanded', 'false');

    function setMenu(open) {
        if (open) {
            navLinks.classList.add('active');
            toggleButton.innerHTML = '<i class="fas fa-times"></i>';
            toggleButton.setAttribute('aria-expanded', 'true');
        } else {
            navLinks.classList.remove('active');
            toggleButton.innerHTML = '<i class="fas fa-bars"></i>';
            toggleButton.setAttribute('aria-expanded', 'false');
        }
    }

    toggleButton.addEventListener('click', (e) => {
        e.stopPropagation();
        setMenu(!navLinks.classList.contains('active'));
    });

    document.addEventListener('click', (e) => {
        if (!navLinks.classList.contains('active')) return;
        if (!navLinks.contains(e.target) && !toggleButton.contains(e.target)) {
            setMenu(false);
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            navLinks.classList.remove('active');
        }
    });
});
</script>

    <div class="carrinho">
        <a href="../../carrinho/HTML/carrinho.php"><img src="../../index/IMG/carrinho.png" alt="carrinho" id="carrinho" /></a>
    </div>
</header>

<!-- Alerta -->
<div class="container mt-3">
    <div id="alerta" class="d-none text-center p-3 rounded"></div>
</div>

<!-- Conteúdo -->
<div class="container py-5">
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <img src="../IMG/usuario.png" alt="avatar" class="rounded-circle img-fluid" style="width: 140px;">
                    <h5 class="my-3"><?= htmlspecialchars($usuario['nome']) ?></h5>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <form method="POST" id="formUsuario">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-3"><label class="mb-0">Nome completo</label></div>
                            <div class="col-sm-9"><input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required readonly></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3"><label class="mb-0">E-mail</label></div>
                            <div class="col-sm-9"><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required readonly></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3"><label class="mb-0">Telefone</label></div>
                            <div class="col-sm-9"><input type="text" name="telefone" id="telefone" class="form-control" value="<?= htmlspecialchars($usuario['telefone']) ?>" required readonly></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3"><label class="mb-0">CPF</label></div>
                            <div class="col-sm-9"><input type="text" id="cpf" class="form-control" value="<?= htmlspecialchars($usuario['cpf']) ?>" readonly disabled></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-3"><label class="mb-0">Data de nascimento</label></div>
                            <div class="col-sm-9"><input type="date" name="data_nascimento" id="data_nascimento" class="form-control" value="<?= htmlspecialchars($usuario['data_nascimento']) ?>" required readonly></div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 mt-4">
                            <button type="button" class="btn btn-outline-primary" id="btnEditar">Editar</button>
                            <button type="submit" class="btn btn-success d-none" id="btnSalvar">Salvar alterações</button>
                            <button type="button" class="btn btn-secondary d-none" id="btnCancelar">Cancelar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Função de máscara
function mascaraTelefone(valor) {
    let v = valor.replace(/\D/g, "");
    v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
    v = v.replace(/(\d{4,5})(\d{4})$/, "$1-$2");
    return v;
}
function mascaraCPF(valor) {
    let v = valor.replace(/\D/g, "");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d)/, "$1.$2");
    v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    return v;
}

// Aplica máscaras ao carregar a página
window.addEventListener("DOMContentLoaded", () => {
    const tel = document.getElementById("telefone");
    const cpf = document.getElementById("cpf");
    tel.value = mascaraTelefone(tel.value);
    cpf.value = mascaraCPF(cpf.value);
});

const form = document.getElementById("formUsuario");
const btnEditar = document.getElementById("btnEditar");
const btnSalvar = document.getElementById("btnSalvar");
const btnCancelar = document.getElementById("btnCancelar");
const alerta = document.getElementById("alerta");

// Ativa edição
btnEditar.addEventListener("click", () => {
    form.querySelectorAll("input:not([disabled])").forEach(input => input.removeAttribute("readonly"));
    btnEditar.classList.add("d-none");
    btnSalvar.classList.remove("d-none");
    btnCancelar.classList.remove("d-none");

    // Habilita máscaras dinâmicas
    document.getElementById("telefone").addEventListener("input", (e) => {
        e.target.value = mascaraTelefone(e.target.value);
    });
});

// Cancelar edição
btnCancelar.addEventListener("click", () => {
    window.location.reload();
});

// Validação antes de salvar
form.addEventListener("submit", function(e) {
    e.preventDefault();

    const telefone = document.getElementById("telefone").value.trim();
    const dataNasc = document.getElementById("data_nascimento").value;
    const hoje = new Date();
    const nascimento = new Date(dataNasc);
    const idade = hoje.getFullYear() - nascimento.getFullYear() - ((hoje < new Date(hoje.getFullYear(), nascimento.getMonth(), nascimento.getDate())) ? 1 : 0);

    const telNumeros = telefone.replace(/\D/g, "");
    if (telNumeros.length < 10 || telNumeros.length > 11) {
        showAlert("❌ Telefone inválido. Use um número com DDD.", "erro");
        return;
    }

    if (idade < 18) {
        showAlert("⚠️ Você deve ter pelo menos 18 anos para atualizar seus dados.", "erro");
        return;
    }

    const formData = new FormData(form);
    let resumo = "";
    for (const [campo, valor] of formData.entries()) {
        resumo += `• ${campo}: ${valor}\n`;
    }

    if (confirm("Tem certeza que deseja salvar as alterações?\n\nAlterações:\n" + resumo)) {
        form.submit();
    }
});

// Alerta visual
function showAlert(msg, status) {
    alerta.classList.remove("d-none", "alert-success", "alert-danger");
    alerta.classList.add("alert", status === "sucesso" ? "alert-success" : "alert-danger");
    alerta.innerHTML = msg;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
</script>

<?php if ($status && $msg): ?>
<script>
showAlert("<?= addslashes($msg) ?>", "<?= $status ?>");
if ("<?= $status ?>" === "sucesso") {
    setTimeout(() => {
        alerta.innerHTML += `
        <div class="mt-2 d-flex justify-content-center align-items-center">
            <div class="spinner-border text-dark me-2" role="status"></div>
            <span>Atualizando página...</span>
        </div>`;
    }, 2000);
    setTimeout(() => window.location.href = "info_usuario.php", 3500);
}
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
