<?php
require 'db.php';


function getServices($pdo) {
    $stmt = $pdo->query("SELECT * FROM Services");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTherapists($pdo) {
    $stmt = $pdo->query("SELECT user_id, full_name FROM Users WHERE role = 'therapist'");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
