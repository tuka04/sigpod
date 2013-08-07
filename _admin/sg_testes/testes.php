<?php

$pdo = new PDO('mysql:host=143.106.193.63;dbname=teste', 'teste', 'q1w2e3');
$pdo2 = new PDO('mysql:host=localhost;dbname=teste', 'root', '');
$statement = $pdo->query("SELECT * FROM a");
$row = $statement->fetchAll(PDO::FETCH_ASSOC);
$statement = $pdo2->query("SELECT * FROM teste_federated");
$row2 = $statement->fetchAll(PDO::FETCH_ASSOC);
echo "<PRE>";
print_r($row);
echo "</PRE>";
echo "<PRE>";
print_r($row2);
echo "</PRE>";
die('sd');
?>