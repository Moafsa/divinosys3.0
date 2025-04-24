<?php

$stmt = mysqli_prepare($conn, "SELECT p.*, pr.nome as produto_nome, pr.preco as produto_preco 
    FROM pedido p 
    JOIN produtos pr ON p.id_produto = pr.id 
    WHERE p.idpedido = ?");

$stmt->bind_param("i", $idpedido);

$stmt->execute();

$result = $stmt->get_result();

$pedidos = [];

while ($row = $result->fetch_assoc()) {
    $pedidos[] = $row;
}

$stmt->close();

return $pedidos; 