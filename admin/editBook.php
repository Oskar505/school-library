<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
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
        $h1 = 'Upravit knihu';
        include 'adminHeader.php'; 
    ?>

    <form class="inputForm" method="post" action="index.php">

        <?php
            // get id
            if (isset($_POST['id'])) {
                $id = $_POST['id'];
                
                //connection
                $conn = mysqli_connect('localhost', 'test', 'Test22knih*', 'knihovna');
            
                if (!$conn) {
                    echo 'chyba pripojeni'.mysqli_connect_error();
                }
                

                $sql = "SELECT * FROM books WHERE id=$id";
                $result = mysqli_query($conn, $sql);
            
                if ($result === false) {
                    echo 'Error: '.mysqli_error($conn);
                }
            
                $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

                // variables
                $id = $data[0]['id'];
                $registration = $data[0]['registration'];
                $isbn = $data[0]['isbn'];
                $subject = $data[0]['subject'];
                $publisher = $data[0]['publisher'];
                $author = $data[0]['author'];
                $name = $data[0]['name'];
                $price = $data[0]['price'];
                $dateAdded = $data[0]['dateAdded'];
                $lentTo = $data[0]['lentTo'];
                $class = $data[0]['class'];
                $lendDate = $data[0]['lendDate'];
                $returnDate = $data[0]['returnDate'];
                $reservation = $data[0]['reservation'];
                $note = $data[0]['note'];
                $discarded = $data[0]['discarded'];


                // checkbox value
                if ($discarded == 1) {
                    $discarded = "checked='true'";
                }
                
                else {
                    $discarded = '';
                }

                // html
                echo "<input type='hidden' name='id' value=$id>";
            
                echo "
                <table class='editBookTable'>
                    <tr class='column'>
                        <td class='label'>
                            <label for='registration'>Registrační číslo</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='registration' name='registration' placeholder='Evidenční číslo' value=$registration>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='isbn'>Isbn</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='isbn' name='isbn' placeholder='Isbn' value='$isbn'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='subject'>Okruh</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='subject' name='subject' placeholder='Okruh' value='$subject'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='publisher'>Vydavatel</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='publisher' name='publisher' placeholder='Vydavatel' value='$publisher'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='author'>Autor</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='author' name='author' placeholder='Autor' value='$author'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='name'>Název</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='name' name='name' placeholder='Název' value='$name'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='price'>Cena</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='price' name='price' placeholder='Pořizovací cena' value='$price'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='dateAdded'>Zaevidování</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='date' id='dateAdded' name='dateAdded' placeholder='Datum zaevidování' value='$dateAdded'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='lentTo'>Půjčeno</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='lentTo' name='lentTo' placeholder='Vypůjčeno' value='$lentTo'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='class'>Třída</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='class' name='class' placeholder='Třída' value='$class'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='lendDate'>Datum půjčení</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='date' id='lendDate' name='lendDate' value='$lendDate'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='returnDate'>Datum vrácení</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='date' id='returnDate' name='returnDate' value='$returnDate'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='reservation'>Rezervace</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='reservation' name='reservation' placeholder='Rezervace' value='$reservation'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='note'>Poznámka</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' name='note' id='note' placeholder='poznámka' value='$note'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label'>
                            <label for='discarded'>Vyřazeno</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='checkbox' type='checkbox' id='discarded' name='discarded' $discarded>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td>
                            <input class='submitEditBtn btn' type='submit' name='edit' value='Upravit'>
                        </td>

                        <td></td>

                        <td>
                            <input class='submitDeleteBtn btn' type='submit' value='Smazat' name='delete'>
                        </td>
                    </tr>
                </table>";
            }

            else {
                echo '
                    <br>
                    <h1>Id nebylo zaznamenáno</h1>
                    <p>Vraťte se na základní stránku administrace a zkuste to znovu.</p>
                ';
            }
        ?>
    </form>


    <script src="formAutoFill.js"></script>
</body>
</html>
