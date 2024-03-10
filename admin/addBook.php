<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /userLogin.php');
        exit;
    }


    if (isset($_POST['back'])) {
        header('Location: index.php');
        exit;
    }

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];




    try {
        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
    }
    
    catch (mysqli_sql_exception $e) {
        showError('Chyba připojení', "Nastala chyba připojení k databázi, zkuste to prosím později.");
    }


    $sql = "SELECT registration FROM books ORDER BY `books`.`id` DESC LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        echo 'Error: '.mysqli_error($conn);
    }

    $lastRegistration = intval(mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['registration']);

    if ($lastRegistration == 0) {
        $newRegistration = '';
    }

    else {
        $newRegistration = $lastRegistration + 1;
    }
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat knihu</title>
    <link rel="stylesheet" href="styles.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&family=Playfair+Display&family=Roboto&display=swap" rel="stylesheet">
</head>

<body>
    <?php
        $h1 = 'Přidat knihu';
        include 'adminHeader.php'; 
    ?>


    <form class="inputForm" method="post" action="index.php">
    <table class='editBookTable'>
        <tr class='column'>
            <td class='label'>
                <label for='registration'>Registrační číslo</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='input' type='text' id='registration' name='registration' placeholder='Evidenční číslo' value="<?php echo $newRegistration ?>">
            </td>
        </tr>

        <tr class='column'>
            <td class='label'>
                <label for='isbn'>Isbn</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='input' type='text' id='isbn' name='isbn' placeholder='Isbn'>
            </td>
        </tr>

        <tr class='column'>
            <td class='label'>
                <label for='subject'>Okruh</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='input' type='text' id='subject' name='subject' placeholder='Okruh'>
            </td>
        </tr>

        <tr class='column'>
            <td class='label'>
                <label for='publisher'>Vydavatel</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='input' type='text' id='publisher' name='publisher' placeholder='Vydavatel'>
            </td>
        </tr>

        <tr class='column'>
            <td class='label'>
                <label for='author'>Autor</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='input' type='text' id='author' name='author' placeholder='Autor'>
            </td>
        </tr>

        <tr class='column'>
            <td class='label'>
                <label for='name'>Název</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='input' type='text' id='name' name='name' placeholder='Název'>
            </td>
        </tr>

        <tr class='column'>
            <td class='label'>
                <label for='price'>Cena</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='input' type='text' id='price' name='price' placeholder='Pořizovací cena'>
            </td>
        </tr>

        <tr class='column'>
            <td class='label'>
                <label for='dateAdded'>Zaevidování</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='input' type='date' id='dateAdded' name='dateAdded' placeholder='Datum zaevidování'>
            </td>
        </tr>

        <tr class='column'>
            <td class='label'>
                <label for='note'>Poznámka</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='input' type='text' name='note' id='note' placeholder='poznámka'>
            </td>
        </tr>

        <tr class='column'>
            <td class='label'>
                <label for='discarded'>Vyřazeno</label>
            </td>

            <td class='middleColumn'></td>

            <td>
                <input class='checkbox' type='checkbox' id='discarded' name='discarded'>
            </td>
        </tr>


        <tr class='column'>
            <td>
                <input class="submitEditBtn btn" type="submit" name="add" value="Přidat">
            </td>

            <td></td>

            <td>
                <form method="post" action="addBook.php">
                    <button class='submitDeleteBtn backBtn btn' type="submit" name="back">Zpět</button>
                </form>
            </td>
        </tr>
    </table>
    </form>


    <script src="/scripts/addFormAutoFill.js"></script>
</body>
</html>

<!--
<script>
    document.querySelector('form').addEventListener('submit', function() {
        setTimeout(() => {
        }, 1000);

      window.close();
    });
</script>
-->