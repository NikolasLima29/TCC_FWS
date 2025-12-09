<?php
include "../../conn.php";
session_start();

$id = $_SESSION['usuario_id'] ?? 1;
$status = "";
$msg = "";

// Atualização de dados pessoais
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_dados'])) {
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

// Atualização de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_senha'])) {
    $senha_atual = $_POST['senha_atual'];
    $senha_nova = $_POST['senha_nova'];
    $senha_confirma = $_POST['senha_confirma'];

    // Buscar senha atual do usuário
    $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if (!password_verify($senha_atual, $result['senha'])) {
        $status = "erro";
        $msg = "Senha atual inválida.";
    } elseif ($senha_nova !== $senha_confirma) {
        $status = "erro";
        $msg = "As novas senhas não coincidem.";
    } elseif (strlen($senha_nova) < 6) {
        $status = "erro";
        $msg = "A nova senha deve ter no mínimo 6 caracteres.";
    } else {
        $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET senha=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $senha_hash, $id);
        $stmt->execute();

        $status = "sucesso";
        $msg = "Senha alterada com sucesso!";
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
    <title>Meu Perfil</title>
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

<!-- Seção Hero -->
<section class="profile-header">
    <div class="container">
        <h1>Meu Perfil</h1>
        <p>Gerencie suas informações e segurança</p>
    </div>
</section>

<!-- Alerta -->
<div class="container mt-4">
    <div id="alerta" class="d-none text-center p-3 rounded"></div>
</div>

<!-- Conteúdo Principal -->
<div class="container py-5">
    <div class="row">
        <!-- Card Avatar -->
        <div class="col-lg-3 mb-4">
            <div class="profile-card">
                <div class="avatar-container">
                    <img src="../IMG/usuario.png" alt="avatar" class="avatar">
                </div>
                <h3 class="mt-4"><?= htmlspecialchars($usuario['nome']) ?></h3>
                <div class="info-badge">
                    <span class="badge bg-success">✓ Online</span>
                </div>
            </div>
        </div>

        <!-- Conteúdo Abas -->
        <div class="col-lg-9">
            <div class="tabs-container">
                <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados" type="button">Dados Pessoais</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="senha-tab" data-bs-toggle="tab" data-bs-target="#senha" type="button">Segurança</button>
                    </li>
                </ul>

                <div class="tab-content" id="profileTabsContent">
                    <!-- Aba Dados Pessoais -->
                    <div class="tab-pane fade show active" id="dados" role="tabpanel">
                        <form method="POST" id="formDados">
                            <input type="hidden" name="atualizar_dados">
                            
                            <div class="form-section">
                                <h5>Informações Básicas</h5>
                                
                                <div class="mb-3">
                                    <label for="nome" class="form-label">Nome Completo</label>
                                    <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($usuario['email']) ?>" required readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="text" name="telefone" id="telefone" class="form-control" value="<?= htmlspecialchars($usuario['telefone']) ?>" required readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="cpf" class="form-label">CPF</label>
                                    <input type="text" id="cpf" class="form-control" value="<?= htmlspecialchars($usuario['cpf']) ?>" readonly disabled>
                                </div>

                                <div class="mb-3">
                                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                    <input type="date" name="data_nascimento" id="data_nascimento" class="form-control" value="<?= htmlspecialchars($usuario['data_nascimento']) ?>" required readonly>
                                </div>
                            </div>

                            <div class="form-section">
                                <h5>Informações de Cadastro</h5>
                                <p class="text-muted small">
                                    <strong>Membro desde:</strong> <?= date('d/m/Y', strtotime($usuario['criado_em'])) ?><br>
                                    <strong>Último acesso:</strong> <?= $usuario['ultimo_login'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_login'])) : 'Primeiro acesso' ?>
                                </p>
                            </div>

                            <div class="button-group mt-4">
                                <button type="button" class="btn btn-primary" id="btnEditar">✏️ Editar Dados</button>
                                <button type="submit" class="btn btn-success d-none" id="btnSalvar">Salvar Alterações</button>
                                <button type="button" class="btn btn-secondary d-none" id="btnCancelar">Cancelar</button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba Segurança -->
                    <div class="tab-pane fade" id="senha" role="tabpanel">
                        <form method="POST" id="formSenha">
                            <input type="hidden" name="atualizar_senha">
                            
                            <div class="form-section">
                                <h5>Alterar Senha</h5>
                                <p class="text-muted small">Por segurança, escolha uma senha forte com no mínimo 6 caracteres.</p>
                                
                                <div class="mb-3">
                                    <label for="senha_atual" class="form-label">Senha Atual</label>
                                    <input type="password" name="senha_atual" id="senha_atual" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label for="senha_nova" class="form-label">Nova Senha</label>
                                    <input type="password" name="senha_nova" id="senha_nova" class="form-control" required pattern="(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{6,}">
                                    <small class="form-text text-muted d-block mt-2">A senha deve conter: no mínimo 6 caracteres, 1 letra maiúscula, 1 número e 1 símbolo (!@#$%^&*)</small>
                                </div>

                                <div class="mb-3">
                                    <label for="senha_confirma" class="form-label">Confirmar Senha</label>
                                    <input type="password" name="senha_confirma" id="senha_confirma" class="form-control" required minlength="6">
                                </div>
                            </div>

                            <div class="button-group mt-4">
                                <button type="submit" class="btn btn-danger">Alterar Senha</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara de telefone
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

// Aplica máscaras ao carregar
window.addEventListener("DOMContentLoaded", () => {
    const tel = document.getElementById("telefone");
    const cpf = document.getElementById("cpf");
    tel.value = mascaraTelefone(tel.value);
    cpf.value = mascaraCPF(cpf.value);
});

const formDados = document.getElementById("formDados");
const formSenha = document.getElementById("formSenha");
const btnEditar = document.getElementById("btnEditar");
const btnSalvar = document.getElementById("btnSalvar");
const btnCancelar = document.getElementById("btnCancelar");
const alerta = document.getElementById("alerta");

// Ativa edição
btnEditar.addEventListener("click", () => {
    formDados.querySelectorAll("input:not([disabled])").forEach(input => input.removeAttribute("readonly"));
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

// Validação form dados
formDados.addEventListener("submit", function(e) {
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
        showAlert("⚠️ Você deve ter pelo menos 18 anos.", "erro");
        return;
    }

    if (confirm("Confirmar alteração dos dados pessoais?")) {
        this.submit();
    }
});

// Validação form senha
formSenha.addEventListener("submit", function(e) {
    e.preventDefault();

    const senhaAtual = document.getElementById("senha_atual").value;
    const senhaNova = document.getElementById("senha_nova").value;
    const senhaConfirma = document.getElementById("senha_confirma").value;
    const regexSenha = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*]).{6,}$/;

    if (!regexSenha.test(senhaNova)) {
        showAlert("❌ A senha deve conter: no mínimo 6 caracteres, 1 letra maiúscula, 1 número e 1 símbolo (!@#$%^&*).", "erro");
        return;
    }

    if (senhaNova !== senhaConfirma) {
        showAlert("❌ As senhas não coincidem.", "erro");
        return;
    }

    if (senhaAtual === senhaNova) {
        showAlert("⚠️ A nova senha deve ser diferente da senha atual.", "erro");
        return;
    }

    if (confirm("Confirmar alteração de senha?")) {
        this.submit();
    }
});

// Alerta visual
function showAlert(msg, status) {
    alerta.classList.remove("d-none", "alert-success", "alert-danger");
    alerta.classList.add("alert", status === "sucesso" ? "alert-success" : "alert-danger");
    alerta.innerHTML = msg;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

<?php if ($status && $msg): ?>
showAlert("<?= addslashes($msg) ?>", "<?= $status ?>");
if ("<?= $status ?>" === "sucesso") {
    setTimeout(() => {
        alerta.innerHTML += `<div class="mt-2"><div class="spinner-border text-dark me-2" role="status"></div><span>Atualizando...</span></div>`;
    }, 1500);
    setTimeout(() => window.location.href = "info_usuario.php", 3000);
}
<?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
