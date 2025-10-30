<?php
include "../../conn.php";

// Buscar todos os produtos
$query = "SELECT p.id, p.nome, c.nome AS categoria, f.nome AS fornecedor, p.preco_venda, p.estoque, p.status 
          FROM produtos p
          LEFT JOIN categorias c ON p.categoria_id = c.id
          LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
          ORDER BY p.nome";
$result = $sql->query($query);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Lista de Produtos</title>
<link rel="stylesheet" href="../../css/style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #fff8e1; font-family: "Poppins", sans-serif; }
.container { max-width: 1000px; margin: 50px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
h2 { text-align: center; margin-bottom: 25px; color: #d11b1b; font-weight: bold; }
table th, table td { text-align: center; vertical-align: middle; }
.btn-edit { background-color: #f4a01d; border: none; color: black; font-weight: bold; }
.btn-edit:hover { background-color: #d68c19; color: white; }
</style>
</head>
<body>
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
</div>
</body>
</html>
<?php $sql->close(); ?>
