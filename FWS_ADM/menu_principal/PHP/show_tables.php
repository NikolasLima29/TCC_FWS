<?php
$sql = new mysqli("localhost","root","","FWS");
$sql->set_charset("utf8");
$res = $sql->query("SHOW TABLES");
if ($res) {
    while ($row = $res->fetch_array()) {
        echo $row[0] . "<br>";
    }
}
?>