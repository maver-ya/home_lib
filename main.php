<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" type="text/css" href="templates/styles/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slim-select/2.8.0/slimselect.min.css"
          integrity="sha512-QhrDqeRszsauAfwqszbR3mtxV3ZWp44Lfuio9t1ccs7H15+ggGbpOqaq4dIYZZS3REFLqjQEC1BjmYDxyqz0ZA=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <title>Заголовок страницы</title>
</head>
<body>
<div class="topnav">
    <a href="main.php">Таблицы</a>
    <a href="add.php">Добавить</a>
    <a href="javascript:void(0);" style="font-size:15px;" class="icon" onclick="myFunction()">&#9776;</a>
</div>

<div style="padding:20px;margin-top:30px;line-height:25px;">
</div>

<div class="horizontal-menu">
    <form method="POST">
        <label for="tables">Выберите таблицу</label>
        <button class="tables" name="tables" value="Books">Книги</button>
        <button class="tables" name="tables" value="Authors">Авторы</button>
        <button class="tables" name="tables" value="Genres">Жанры</button>
        <button class="tables" name="tables" value="Kinds">Виды</button>
        <button class="tables" name="tables" value="Countries">Страны</button>
        <button class="tables" name="tables" value="Readers">Читатели</button>
        <button class="tables" name="tables" value="Statuses">Статусы</button>
        <button class="tables" name="tables" value="Accounting">Учёт</button>
    </form>
</div>

<script>
    function myFunction() {
        var x = document.getElementById("myTopnav");
        if (x.className === "topnav") {
            x.className += " responsive";
        } else {
            x.className = "topnav";
        }
    }
</script>
</body>
</html>
<?php

$conn = require_once("connection.php");

function show_tables($table_name, $sql = ""): void
{
    global $conn;

    $columns = $conn->query("DESCRIBE $table_name")->fetchAll(PDO::FETCH_COLUMN);
    if ($table_name == "Books") {
        array_push($columns, "Genres", "Kinds", "Authors", "Country_Author");
    }
    if ($table_name == "Accounting") {
        $columns[] = "time";
        $columns[] = "status";
    }
    echo "<div class='table-responsive-xxl'>";
    if ($table_name == "Books" or $table_name == "Authors" or $table_name == "Accounting") {
        require_once("filters.php");
        try {
            get_filters($table_name);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    echo "<p>$table_name</p>";
    echo "<table border='1' id='myTable'>";
    echo "<tr>";
    foreach ($columns as $column) {
        if (str_contains($column, "_id")) {
            $col = str_replace("_id", "", $column);
            echo "<th>$col</th>";
        } else {
            echo "<th>$column</th>";
        }
    }
    echo "</tr>";
    if ($sql) {
        $stmt = $conn->query($sql);
    } else {
        $stmt = $conn->query("SELECT * FROM $table_name ORDER BY id ASC");
    }
    if ($table_name == "Authors") {
        if ($sql) {
            $stmt = $conn->query($sql);
        } else {
            $stmt = $conn->query("SELECT Authors.id, Authors.name, Countries.name as country FROM Authors JOIN Countries ON Countries.id = Authors.country_id ORDER BY Authors.id ASC");
        }
    }
    if ($table_name == "Accounting") {
        if ($sql) {
            $stmt = $conn->query($sql);
        } else {
            $stmt = $conn->query("SELECT Accounting.id, Books.name AS book, Readers.name AS reader, Status_Accounting.time as time, Statuses.name AS status FROM Accounting JOIN Books ON Books.id = Accounting.book_id JOIN Readers ON Readers.id = Accounting.reader_id JOIN Status_Accounting ON Status_Accounting.accounting_id = Accounting.id JOIN Statuses ON Statuses.id = Status_Accounting.status_id ORDER BY Accounting.id ASC");
        }
    }
    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td>$cell</td>";
        }
        if ($table_name == "Books") {
            $id = $row[0];
            $cells = $conn->query("SELECT Genres.name FROM Genres JOIN Book_Genre ON Book_Genre.book_id = $id AND Book_Genre.genre_id = Genres.id")->fetchAll(PDO::FETCH_COLUMN);
            echo "<td>";
            foreach ($cells as $cell) {
                echo "$cell ";
            }
            echo "</td>";

            $cells = $conn->query("SELECT Kinds.name FROM Kinds JOIN Book_Kind ON Book_Kind.book_id = $id AND Book_Kind.kind_id = Kinds.id")->fetchAll(PDO::FETCH_COLUMN);
            echo "<td>";
            foreach ($cells as $cell) {
                echo "$cell ";
            }
            echo "</td>";

            $cells = $conn->query("SELECT Authors.name FROM Authors JOIN Book_Author ON Book_Author.book_id = $id AND Book_Author.author_id = Authors.id")->fetchAll(PDO::FETCH_COLUMN);
            echo "<td>";
            foreach ($cells as $cell) {
                echo "$cell ";
            }
            echo "</td>";
            $cells = $conn->query("SELECT DISTINCT c.name AS country FROM Countries c JOIN Authors a ON c.id = a.country_id JOIN Book_Author ba ON a.id = ba.author_id JOIN Books b ON ba.book_id = $id;")->fetchAll(PDO::FETCH_COLUMN);
            echo "<td>";
            foreach ($cells as $cell) {
                echo "$cell ";
            }
            echo "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
    echo "</div>";
}

if (isset($_POST["filter"])) {
    $table = $_POST["table"];
    $keys = array_keys($_POST);
    if ($table == "Books") {
        $year_book = $_POST["year_book"];
        $year_writing = $_POST["year_writing"];
        $genres = $_POST["genres"];
        $kinds = $_POST["kinds"];
        $authors = $_POST["authors"];
        $where = [];
        if ($year_book != "") {
            $list = "(" . join(", ", $year_book) . ")";
            $where[] = "b.year_book IN $list";
        }
        if ($year_writing != "") {
            $list = "(" . join(", ", $year_writing) . ")";
            $where[] = "b.year_writing IN $list";
        }
        if ($genres != "") {
            for ($i = 0; $i < count($genres); $i++) {
                $genres[$i] = '"' . $genres[$i] . '"';
            }
            $list = "(" . join(", ", $genres) . ")";
            $where[] = "g.name IN $list";
        }
        if ($kinds != "") {
            for ($i = 0; $i < count($kinds); $i++) {
                $kinds[$i] = '"' . $kinds[$i] . '"';
            }
            $list = "(" . join(", ", $kinds) . ")";
            $where[] = "k.name IN $list";
        }
        if ($authors != "") {
            for ($i = 0; $i < count($authors); $i++) {
                $authors[$i] = '"' . $authors[$i] . '"';
            }
            $list = "(" . join(", ", $authors) . ")";
            $where[] = "a.name IN $list";
        }
        $conds = join(" AND ", $where);
        if ($conds != "") {
            $where = " WHERE " . $conds;
        } else {
            $where = '';
        }
        $sql = "SELECT DISTINCT b.* FROM Books b JOIN Book_Genre bg ON b.id = bg.book_id JOIN Genres g ON g.id = bg.genre_id JOIN Book_Kind bk ON b.id = bk.book_id JOIN Kinds k ON k.id = bk.kind_id JOIN Book_Author ba ON b.id = ba.book_id JOIN Authors a ON a.id = ba.author_id" . $where . " ORDER BY id ASC";

    }
    if ($table == 'Authors') {
        $country = $_POST["countries"];
        if ($country != "") {
            for ($i = 0; $i < count($country); $i++) {
                $country[$i] = '"' . $country[$i] . '"';
            }
            $list = "(" . join(", ", $country) . ")";
            $where = " WHERE Countries.name IN $list";
        } else {
            $where = "";
        }
        $sql = "SELECT Authors.id, Authors.name, Countries.name as country FROM Authors JOIN Countries ON Countries.id = Authors.country_id" . $where . " ORDER BY Authors.id ASC";
    }

    if ($table == "Accounting") {
        $book = $_POST["book"];
        $reader = $_POST["reader"];
        $status = $_POST["status"];
        $where = [];

        if ($book != "") {
            for ($i = 0; $i < count($book); $i++) {
                $book[$i] = '"' . $book[$i] . '"';
            }
            $list = "(" . join(", ", $book) . ")";
            $where[] = "b.name IN $list";
        }
        if ($reader != "") {
            for ($i = 0; $i < count($reader); $i++) {
                $reader[$i] = '"' . $reader[$i] . '"';
            }
            $list = "(" . join(", ", $reader) . ")";
            $where[] = "r.name IN $list";
        }
        if ($status != "") {
            for ($i = 0; $i < count($status); $i++) {
                $status[$i] = '"' . $status[$i] . '"';
            }
            $list = "(" . join(", ", $status) . ")";
            $where[] = "s.name IN $list";
        }
        $conds = join(" AND ", $where);
        if ($conds != "") {
            $where = " WHERE " . $conds;
        } else {
            $where = '';
        }

        $sql = "SELECT DISTINCT a.id, b.name, r.name, sa.time, s.name FROM Accounting a JOIN Status_Accounting sa ON sa.accounting_id = a.id JOIN Statuses s ON s.id = sa.status_id JOIN Books b ON b.id = a.book_id JOIN Readers r ON r.id = a.reader_id" . $where . " ORDER BY id ASC";

    }
    show_tables($table, $sql);
}

if (isset($_POST['tables'])) {
    $table_name = $_POST['tables'];
    show_tables($table_name);

}
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/slim-select/2.8.0/slimselect.min.js" integrity="sha512-mG8eLOuzKowvifd2czChe3LabGrcIU8naD1b9FUVe4+gzvtyzSy+5AafrHR57rHB+msrHlWsFaEYtumxkC90rg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
';
echo '<script> new SlimSelect({select: "#add_year_book"});</script>';
echo '<script> new SlimSelect({select: "#add_year_writing"});</script>';
echo '<script> new SlimSelect({select: "#add_genres"});</script>';
echo '<script> new SlimSelect({select: "#add_kinds"});</script>';
echo '<script> new SlimSelect({select: "#add_authors"});</script>';
echo '<script> new SlimSelect({select: "#add_countries"});</script>';
echo '<script> new SlimSelect({select: "#add_book"});</script>';
echo '<script> new SlimSelect({select: "#add_reader"});</script>';
echo '<script> new SlimSelect({select: "#add_status"});</script>';

?>
