<?php
try {
	$dbh = new PDO('mysql:dbname=f0940059_home_library;host=localhost', 'f0940059_home_library', 'admin123');
} catch (PDOException $e) {
	die($e->getMessage());
}
?>