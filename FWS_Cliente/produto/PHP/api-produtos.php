<?php
include "../../conn.php";

$q = isset($_GET['q']) ? $_GET['q'] : '';
$q_esc = mysqli_real_escape_string($conn, $q);

$sql = "SELECT id, nome, foto_produto FROM produtos WHERE status = 'ativo' AND nome LIKE '%$q_esc%' LIMIT 10";
$result = mysqli_query($conn, $sql);

$out = [];
while ($row = mysqli_fetch_assoc($result)) {
    $out[] = [
        'id' => $row['id'],
        'label' => $row['nome'],
        'foto' => $row['foto_produto']
    ];
}
header('Content-Type: application/json');
echo json_encode($out);
?>
