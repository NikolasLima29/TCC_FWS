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
      background-color: #f9f9f9;
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
      background-color: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    thead {
      background-color: #f4a01d;
      color: white;
    }
    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: center;
    }
    tbody tr:hover {
      background-color: #f1f1f1;
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
        <th>Descrição</th>
        <th>Foto</th>
        <th>Preço</th>
        <th>QTD</th>
        <th>Status</th>
        <th>Criação</th>
      </tr>
    </thead>
    <tbody>
      <!-- Dados do estoque vão aqui -->
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

<?php 




?>