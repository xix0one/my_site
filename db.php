<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <link href="style.css" rel="stylesheet">
        <title>База данных</title>
    </head>
    <body>

        <nav>
            <a href="start.php">Главная</a>
            <a href="db.php">База данных</a>
        </nav>

        <h1>База данных</h1>

        <?php

            try {
                $db = new PDO('mysql:host=127.0.0.1; dbname=new_db', 'root', '');
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_NUM);
            } catch (PDOException $e) {
                echo "error: " . $e->getMessage();
                exit();
            }

            $q = $db->query("SELECT `name`, `age`, `rank` FROM `heroes`");
        ?>

        <table>
            <thead>
                <tr>
                    <th>Имя</th>
                    <th>Возраст</th>
                    <th>Ранг</th>
                    <th>Удаление</th>
                </tr>
            </thead>
            
            <form action="#" method="POST">

            <tbody>
                <?php while ($row = $q->fetch()) { ?>
                    <tr>
                        <td><?= $row[0]; ?></td>
                        <td><?= $row[1]; ?></td>
                        <td><?= $row[2]; ?></td>
                        <td><input type="checkbox" name="delete[]" value="<?= $row[0]; ?>"></td>
                    </tr> 
                <?php } ?>
            </tbody>

        </table>
        
        <br>
        
            <input type="submit" value="Удалить" name="delete_heroes">
            <br><br>
            <input type="text" name="name" placeholder="Имя" value="<?= $_POST['name'] ?>">
            <input type="text" name="age" placeholder="Возраст" value="<?= $_POST['age'] ?>">
            <input type="text" name="rank" placeholder="Ранг" value="<?= $_POST['rank'] ?>">
            <input type="submit" value="Добавить" name="add_heroes">
            <br><br>
            <input type="submit" value="Скачать csv" name="down_csv">
            <input type="submit" value="Скачать txt" name="down_txt">
        </form>
    </body>
</html>

<?php
    if (isset($_POST['add_heroes'])) {
        
        if (!empty($_POST['name']) && !empty($_POST['age']) && !empty($_POST['rank'])) {

            $errors = [];
            
            $name = htmlentities(trim($_POST['name']));
            $name = strtr($name, ['_' => '\_', '%' => '\%']);
            if (iconv_strlen($name) <= 1) {
                $errors[] = 'Введите корректное имя';
            }

            $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
            if (is_null($age) || ($age === false)) {
                $errors[] = 'Введите корректный возраст';
            }

            $rank = htmlentities(trim($_POST['rank']));
            $rank = strtr($rank, ['_' => '\_', '%' => '\%']);
            if (iconv_strlen($rank) < 1 || iconv_strlen($rank) > 3) {
                $errors[] = 'Введите корректный ранг';
            }

            $stmt = $db->prepare("INSERT INTO `heroes` (`name`, `age`, `rank`) VALUES (?, ?, ?)");

            if ($errors) { ?>

                <ul>
                    <?php foreach ($errors as $e) { ?>
                        <li><?= $e ?></li>
                    <?php } ?>
                <ul>

            <?php } else {
                $stmt->execute([$name, $age, $rank]);
                header('Location: /db.php');
                exit();
            }


        } else {
            echo "Заполните все поля";
        }
    }

    if (isset($_POST['delete_heroes'])) {


        if (!empty($_POST['name'])) {

            $name = trim($_POST['name']);
            if (iconv_strlen($name) <= 1) {
                echo 'Введите корректное имя';
            } else {

                $stmt = $db->prepare("DELETE FROM `heroes` WHERE `name` = ?");
                foreach ($_POST['delete'] as $d) {
                    $stmt->execute([$d]);
                }
                header('Location: /db.php');
                exit();
            }
        }

        if (!empty($_POST['delete'])) {
            foreach ($_POST['delete'] as $d) {
                $db->exec("DELETE FROM `heroes` WHERE `name` = '$d'");
                header('Location: /db.php');
            }
        }  
        
    }

    if (isset($_POST['down_csv'])) {
        ob_clean();
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="heroes.csv"');
        $fh = fopen('php://output', 'wb');
        $heroes = $db->query('SELECT `name`, `age`, `rank` FROM `heroes`');
        while ($row = $heroes->fetch()) {
            fputcsv($fh, $row);
        }
        fclose($fh);
        exit();
    }

    if (isset($_POST['down_txt'])) {
        ob_clean();
        header('Content-type: text/txt');
        header('Content-Disposition: attachment; filename="heroes.txt"');
        $fh = fopen('php://output', 'wb');
        $heroes = $db->query('SELECT `name`, `age`, `rank` FROM `heroes`');
        while ($row = $heroes->fetch()) {
            fputcsv($fh, $row);
        }
        fclose($fh);
        exit();
    }
?>