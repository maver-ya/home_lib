<?php
require "db_logic.php";
$db_func = ["Books" => 'add_book',
    "Authors" => 'add_author',
    "Genres" => 'add_genre',
    "Kinds" => 'add_kind',
    "Countries" => 'add_country',
    "Accounting" => 'add_accounting',
    "Statuses" => 'add_status',
    "Readers" => 'add_reader'];



$table = $_POST['forms'];
$db_func[$table]($_POST);
header("location: ../main.php");