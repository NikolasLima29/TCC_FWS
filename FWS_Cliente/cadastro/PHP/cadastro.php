<?php
session_start
require "../../conn.php";



if (server['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['cadastro_form']) && $_POST['cadastro_form'] == 1){
        $nome = trim($_POST ['nome']);
        $data = trim($_POST ['data']);
        $email = trim($_POST ['email']);
        $senha = trim($_POST ['senha']);
        $cpf = trim($_POST ['cpf']);
        $con_senha = trim($_POST ['con_senha']);
        

        //para verificar se o email ou o cpf já existem:

    $testeEmail = $conn->query("SELECT * FROM usuario WHERE email = '$email'");
    $checarEmail = mysqli_num_rows($testeEmail);

    $testarCPF = $conn->query("SELECT * FROM usuario WHERE cpf = $cpf");
    $checarCPF = mysqli_num_rows($testarCPF);


        //checar se existe um Email ou um CPF duplicados e criptografia:
        if ($checarEmail > 0 && $checarCPF > 0){
            echo "<script>alert('Email ou CPF já estão cadastrados! Por favor, verifique se os dados inseridos estão corretos.'); history.back();</script>";
        }else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuario (nome, data_nascimento, cpf, email, senha) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("svs", $nome, $cpf, $senha_hash);

        //salvar os dados no banco de dados:
        $query = "INSERT INTO usuario (nome, data_nascimento, cpf, email, senha) VALUES ('$nome', '$data', '$cpf', '$email', '$senha')";





    }
}











?>