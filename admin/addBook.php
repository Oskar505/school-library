<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit;
    }


    if (isset($_POST['back'])) {
        header('Location: index.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přidat knihu</title>
    <link rel="stylesheet" href="/styles.css">

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
        <div class="column">
            <input class="input" type="text" id="registration" name="registration" placeholder="Evidenční číslo">
        </div>

        <div class="column">
            <input class="input" type="text" id="isbn" name="isbn" placeholder="Isbn" id='isbn'>
            <button type='button' id='fillBtn'>Vyplnit</button>
        </div>

        <div class="column">
            <input class="input" type="text" id="subject" name="subject" placeholder="Okruh">
        </div>
        
        <div class="column">
            <input class="input" type="text" id="publisher" name="publisher" placeholder="Vydavatel">
        </div>

        <div class="column">
            <input class="input" type="text" id="author" name="author" placeholder="Autor">
        </div>
        
        <div class="column">
            <input class="input" type="text" id="name" name="name" placeholder="Název">
        </div>
        
        <div class="column">
            <input class="input" type="text" id="price" name="price" placeholder="Pořizovací cena">
        </div>
        
        <div class="column">
            <label class='label dateLabel' for="zaevidování">Zaevidování</label>
            <input class="input" type="date" id="dateAdded" name="dateAdded" placeholder="Datum zaevidování">
        </div>
        
        <div class="column">
            <input class="input" type="text" id="lentTo" name="lentTo" placeholder="Vypůjčeno">
        </div>

        <div class="column">
            <input class="input" type="text" id="class" name="class" placeholder="Třída">
        </div>
        
        <div class="column">
            <label class='label dateLabel' for="půjčení">Půjčení</label>
            <input class="input" type="date" id="lendDate" name="lendDate">
        </div>
        
        <div class="column">
            <label class='label dateLabel' for="vrácení">Vrácení</label>
            <input class="input" type="date" id="returnDate" name="returnDate">
        </div>
        
        <div class="column">
            <input class="input" type="text" id="reservation" name="reservation" placeholder="Rezervace">
        </div>
        
        <div class="column note">
            <input class="input" type="text" name="note" id="note" placeholder="poznámka">
        </div>
        
        <div class="column">
            <label for="Vyřazeno">Vyřazeno</label>
            <input class="input" class="checkbox" type="checkbox" id="discarded" name="discarded">
        </div>
        
        <div class="column">
            <input class="input" class="submitPopup" type="submit" name="add" value="Přidat">
        </div>
    </form>

    <form method="post" action="addBook.php">
        <button type="submit" name="back">Zpět</button>
    </form>


    <script src="addFormAutoFill.js"></script>
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