<?php
try {
    // подключаемся к серверу
    $conn = new PDO('mysql:dbname=f0944000_home_library;host=localhost', 'f0944000_home_library', 'admin123');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>