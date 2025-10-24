<?php  
include "..\..\conn.php";

if (!$sql){
  die("conexão falhou: " . mysqli_connect_error());
}
$sqli = "SELECT id, nome, categoria_id, fornecedor_id, preco_venda, estoque, status, criado_em FROM produtos";

  $result = $sql->query($sqli);

  if(!$result){
      die ("Erro na consulta: ") . $sql->error; 
    }
  
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tabela de Estoque - Produtos</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background-color: #000000ff;
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
    }
    .status-in-stock {
      color: green;
      font-weight: bold;
    }
    .status-out-stock {
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>

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
        <th>qtd</th>
        <th>Status</th>
        <th>Criação</th>
      </tr>
    </thead>
    <tbody>
     <?php
if ($result->num_rows > 0){
  while ($row = $result->fetch_assoc()){
    echo "<tr>";
  echo "<td>" . (isset($row["id"]) ? $row["id"] : "id não disponível") . "</td>";
  echo "<td>" . (isset($row["nome"]) ? $row["nome"] : "nome não disponível") . "</td>";
  echo "<td>" . (isset($row["categoria_id"]) ? $row["id"] : "categoria não disponível") . "</td>";
  echo "<td>" . (isset($row["fornecedor_id"]) ? $row["fornecedor_id"] : "fornecedor não disponível") . "</td>";
  echo "<td>" . (isset($row["preco_venda"]) ? $row["preco_venda"] : "preço não disponível") . "</td>";
  echo "<td>" . (isset($row["estoque"]) ? $row["estoque"] : "Quantidade não disponível") . "</td>";
  echo "<td>" . (isset($row["status"]) ? $row["status"] : "status não disponível") . "</td>";
  echo "<td>" . (isset($row["criado_em"]) ? $row["criado_em"] : "data não disponível") . "</td>";
  echo "</tr>";

  }
}else{
  echo "<tr><td colspan='9'>nenhum registro encontrado</td>";
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
          if (texto.toLowerCase().indexOf(filtro) > -1) {
            linhas[i].style.display = "";
          } else {
            linhas[i].style.display = "none";
          }
        }
      }
    }
  </script>

</body>
</html>
