<?php
global $conn;
$conn = require_once('connection.php');

function make_insert($sql): string
{
    global $conn;
    try {
        $response = $conn->prepare($sql);
        $response->execute();
        return "OK";
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

function make_select($sql): string
{
    global $conn;
    try {
        $response = $conn->query($sql);
        $response->execute();
        return $response->fetch(PDO::FETCH_NUM)[0];
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

function add_book($data): string
{

    $name = $data['name'];
    $year_book = $data['year_book'];
    $year_writing = $data['year_writing'];
    $description = $data['description'];
    $isbn = $data['isbn'];
    $genre_data = array();
    foreach ($data["Genres"] as $genre) {
        $genre_data[] = ["name" => $genre, "table" => "Genres"];
    }
    $kind_data = array();
    foreach ($$data["Kinds"] as $kind) {
        $genre_data[] = ["name" => $kind, "table" => "Kinds"];
    }
    $author_data = array();
    foreach ($data["Authors"] as $author) {
        $author_data[] = ["name" => $author, "table" => "Authors"];
    }


    $sql = "INSERT INTO `Books` (`id`, `name`, `year_book`, `year_writing`, `description`, `ISBN`) VALUES ('NULL', '$name', '$year_book', '$year_writing', '$description', '$isbn')";
    $res[0] = make_insert($sql);
    $x = 1;
    foreach ([$genre_data, $kind_data, $author_data] as $add_data) {
        print_r($name);
        print_r($add_data["name"]);
        print_r("Books");
        print_r($add_data["table"]);
        foreach ($add_data as $add_data_row) {
            foreach (array_keys($add_data_row) as $k) {
            }
            $res[$x] = connect_tables($name, $add_data_row["name"], "Books", $add_data_row["table"]);
            $x = $x + 1;
        }
    }
    foreach ($res as $r) {
        if ($r != "OK") {
            return false;
        }
    }
    return true;
}

function add_author($data): string
{
    $name = $data['name'];
    $country_id = get_id($data['country_id'], "Countries");
    $sql = "INSERT INTO `Authors` (`id`, `name`, `country_id`) VALUES ('NULL', '$name', '$country_id')";
    return make_insert($sql);
}

function add_genre($data): string
{
    $name = $data['name'];
    $sql = "INSERT INTO `Genres` (`id`, `name`) VALUES ('NULL', '$name')";
    return make_insert($sql);
}

function add_kind($data): string
{
    $name = $data['name'];
    $sql = "INSERT INTO `Kinds` (`id`, `name`) VALUES ('NULL', '$name')";
    return make_insert($sql);
}

function add_reader($data): string
{
    $name = $data['name'];
    $phone_number = $data['phone_number'];
    $sql = "INSERT INTO `Readers` (`id`, `name`, `phone_number`) VALUES ('NULL', '$name','$phone_number')";
    return make_insert($sql);
}

function add_accounting($data): string
{
    $book_id = get_id($data['book_id'], "Books");
    $reader_id = get_id($data['reader_id'], "Readers");
    $status = get_id($data['Statuses'], "Statuses");
    $sql = "INSERT INTO `Accounting` (`id`, `book_id`, `reader_id`) VALUES ('NULL', '$book_id', '$reader_id')";
    make_insert($sql);
    $acc_id = make_select("SELECT id FROM `Accounting` WHERE `book_id` = '$book_id' AND `reader_id` = '$reader_id'");
    $sql = "INSERT INTO `Status_Accounting` (`id`, `accounting_id`, `status_id`, `time`) VALUES ('NULL', '$acc_id', '$status', DEFAULT)";

    return make_insert($sql);
}

function add_country($data): string
{
    $name = $data['name'];
    $sql = "INSERT INTO `Countries` (`id`, `name`) VALUES ('NULL', '$name')";
    return make_insert($sql);
}

function add_status($data): string
{
    $name = $data['name'];
    $sql = "INSERT INTO `Statuses` (`id`, `name`) VALUES ('NULL', '$name')";
    return make_insert($sql);
}


function get_id($name, $table): string
{
    $sql = "SELECT `id` FROM `$table` WHERE `name` = '$name'";
    return make_select($sql);
}

function connect_tables($f_elem, $s_elem, $f_table, $s_table): string
{
    $f_id = get_id($f_elem, $f_table);
    $s_id = get_id($s_elem, $s_table);


    if ($f_table == "Statuses") {
        $f_table = "Status";
    } elseif ($f_table == "Genres") {
        $f_table = "Genre";
    } else {
        $f_table = str_replace("s", "", $f_table);
    }
    $s_table = str_replace("s", "", $s_table);

    $table = $f_table . "_" . $s_table;
    print_r("connect_tables: $f_table, $s_table, $table<br>");

    $f_key = strtolower($f_table) . "_id";
    $s_key = strtolower($s_table) . "_id";
    if ($f_table == "Accounting") {
        $sql = "INSERT INTO `$table` (`id`, `$f_key`, `$s_key`, `time`) VALUES ('NULL', '$f_id', '$s_id', 'NULL')";
    } else {
        $sql = "INSERT INTO `$table` (`id`, `$f_key`, `$s_key`) VALUES ('NULL', '$f_id', '$s_id')";
    }
    return make_insert($sql);
}