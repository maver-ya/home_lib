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

function get_info($row): array
{
    $size = 0;
    $type = $row['Type'];
    if (stripos($type, 'int')) {
        $type = "number";
        $size = str_replace(array("int", "(", ")"), "", $type);
    } elseif (stripos($type, 'varchar')) {
        $type = "text";
        $size = str_replace(array("vatchar", "(", ")"), "", $type);
    }
    return ["name" => $row["Field"],
        "type" => $row["Type"],
        "size" => $size
    ];


}

function get_table_name($word_id): string
{
    $d = ["accounting_id" => "Accounting",
        "book_id" => "Books",
        "genre_id" => "Genres",
        "author_id" => "Authors",
        "country_id" => "Countries",
        "kind_id" => "Kinds",
        "reader_id" => "Readers",
        "status_id" => "Statuses"];
    return $d[$word_id];
}

?>
<?php


if (isset($_POST['tables'])) {
    $table_name = $_POST['tables'];
    $columns = $conn->query("DESCRIBE $table_name")->fetchAll();
    echo "<form action='db.php' method='POST'>";
    echo "<label for='forms'>Сущность</label><br>";
    echo "<input type='text' name='forms' value='$table_name' readonly><br><br>";
    foreach ($columns as $row) {
        $info = get_info($row);
        $type = $info['type'];
        $name = $info['name'];
        $size = $info['size'];
        if ($name === "id") {
            continue;
        }
        echo "<label for='$name'>$name</label><br>";

        if (str_contains($name, "_id")) {
            try {
                echo "<div class='form-group'>";
                echo "<select class='form-control' name='$name' id='values' required>";
                $table = get_table_name($name);
                $values = $conn->query("SELECT `name` FROM `$table`")->fetchAll(PDO::FETCH_COLUMN);
                echo "<option value=''>----</option>";
                foreach ($values as $value) {
                    echo "<option value='$value'>$value</option>";
                }
                echo "</select><br><br>";
                echo "</div>";
                continue;
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
                continue;
            }
        } else {
            echo "<input type='$info[$type]' name='$name' size='$size' required><br><br>";
        }
    }
    if ($table_name == "Books") {
        $additional_tables = ["Genres", "Kinds", "Authors"];
        try {
            foreach ($additional_tables as $table) {
                echo "<label for='$table'>$table</label><br>";
                echo "<div class='form-group'>";
                $name = $table . "[]";
                echo "<select class='form-control' name='$name' id='additional_$table' multiple required>";
                echo "<option value=''>-----</option>";
                $sql = "SELECT `name` FROM `$table`";
                echo $sql;
                $values = $conn->query($sql)->fetchAll(PDO::FETCH_COLUMN);
                foreach ($values as $value) {
                    echo $value;
                    echo "<option value='$value'>$value</option>";
                }
                echo "</select><br><br>";
                echo "</div>";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

    }
    if ($table_name == "Accounting") {
        $table = "Statuses";
        try {
            echo "<label for='$table'>$table</label><br>";
            echo "<div class='form-group'>";
            echo "<select class='form-control' name='$table' id='additional_$table' req uired>";
            echo "<option value=''>-----</option>";
            $sql = "SELECT `name` FROM `$table`";
            $values = $conn->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            foreach ($values as $value) {
                echo $value;
                echo "<option value='$value'>$value</option>";
            }
            echo "</select><br><br>";
            echo "</div>";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    echo "<input type='submit' name='submit' value='Добавить'>";
    echo "</form>";
    echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/slim-select/2.8.0/slimselect.min.js" integrity="sha512-mG8eLOuzKowvifd2czChe3LabGrcIU8naD1b9FUVe4+gzvtyzSy+5AafrHR57rHB+msrHlWsFaEYtumxkC90rg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
';
    echo '<script> new SlimSelect({select: "#values"});</script>';
    echo '<script> new SlimSelect({select: "#additional_Genres"});</script>';
    echo '<script> new SlimSelect({select: "#additional_Kinds"});</script>';
    echo '<script> new SlimSelect({select: "#additional_Authors"});</script>';
    echo '<script> new SlimSelect({select: "#additional_Statuses"});</script>';
} else {
    echo "<span>Nothing</span>";
}
?>