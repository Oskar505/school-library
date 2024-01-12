<?php
    //TODO: upozornit admina kdyz pujcuje knihu nekomu kdo ju nema rezervovanou, ukazovat pocet pujcenych knih pri pujcovani nove

    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /userLogin.php');
        exit;
    }

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravit knihu</title>
    <link rel="stylesheet" href="styles.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&family=Playfair+Display&family=Roboto&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    <?php
        $h1 = 'Upravit knihu';
        include 'adminHeader.php'; 
    ?>

    <form class="inputForm" method="post" action="index.php" autocomplete="off">

        <?php
            /*
            include 'log.php';
            writeToLog('test');
            */



            // get id
            if (isset($_POST['id'])) {
                $id = $_POST['id'];

                require('/var/secrets.php');
                $sqlUser = $secrets['sql-user'];
                $sqlPassword = $secrets['sql-password'];
                $database = $secrets['sql-database'];
                
                //connection
                $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
            
                if (!$conn) {
                    echo 'chyba pripojeni'.mysqli_connect_error();
                }

                $selectedLinesVal = '';


                if ($id != 'all') {
                    $selectedLinesVal = 'editOne';

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
                    $history = $data[0]['history'];
                    $reservation = $data[0]['reservation'];
                    $reservationExpiration = $data[0]['reservationExpiration'];
                    $note = $data[0]['note'];
                    $discarded = $data[0]['discarded'];


                    // checkbox value
                    if ($discarded == 1) {
                        $discarded = "checked='true'";
                    }
                    
                    else {
                        $discarded = '';
                    }


                    //history
                    if ($history == '') {
                        $history = 'Tato kniha ještě nebyla půjčena';
                    }
                }



                else { // hromadna uprava
                    // set default values
                    $id = '';
                    $registration = '';
                    $isbn = '';
                    $subject = '';
                    $publisher = '';
                    $author = '';
                    $name = '';
                    $price = '';
                    $dateAdded = '';
                    $lentTo = '';
                    $class = '';
                    $lendDate = '';
                    $returnDate = '';
                    $history = '';
                    $reservation = '';
                    $reservationExpiration = '';
                    $note = '';
                    $discarded = '';
                }



                
                

                



                // html
                echo "
                <input type='hidden' name='id' value=$id>
                <input type='hidden' name='oldLentTo' value=$lentTo>
                <input type='hidden' name='oldReservation' value=$reservation>
                <input type='hidden' name='selectedInputs' id='selectedInputs' value=$selectedLinesVal>
                ";
            
                echo "
                <table class='editBookTable'>
                    <tr class='column'>
                        <td class='label' id='0'>
                            <label for='lentTo'>Půjčeno</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td class='tdWithIcon'>
                            <input class='input' list='suggestionsList' type='text' id='lentTo' name='lentTo' placeholder='Vypůjčeno' value='$lentTo'>
                            <datalist id='suggestionsList'></datalist>

                            <span class='usernameWarning' id='lentToUsernameWarning' title='Uživatelské jméno neexistuje'>warning</span>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='nameRow'></td>

                        <td class='middleColumn'></td>

                        <td>
                            <p id='lentToName'></p>
                        </td>
                    </tr>


                    <tr class='column'>
                        <td class='label' id='1'>
                            <label for='class'>Třída</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='class' name='class' placeholder='Třída' value='$class'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='2'>
                            <label for='lendDate'>Datum půjčení</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='date' id='lendDate' name='lendDate' value='$lendDate'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='3'>
                            <label for='returnDate'>Datum vrácení</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td class='returnDateTd'>
                            <input class='input' type='date' id='returnDate' name='returnDate' value='$returnDate'>
                            <span class='material-symbols-outlined infiniteDate' id='infiniteDateBtn' title='Půjčit napořád'>all_inclusive</span>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='4'>
                            <label for='reservation'>Rezervace</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' list='reservationSuggestionsList' id='reservation' name='reservation' placeholder='Rezervace' value='$reservation'>
                            <datalist id='reservationSuggestionsList'></datalist>

                            <span class='usernameWarning' id='reservationUsernameWarning'>warning</span>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='nameRow'></td>

                        <td class='middleColumn'></td>

                        <td>
                            <p id='reservationName'></p>
                        </td>
                    </tr>


                    <tr class='column'>
                        <td class='label' id='5'>
                            <label for='reservation'>Konec rezervace</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='date' id='reservationExpiration' name='reservationExpiration' placeholder='Konec rezervace' title='Pokud prázdné vyplní se automaticky +3 dny.' value='$reservationExpiration'>
                        </td>
                    </tr>



                    <tr class='break'></tr>



                    <tr class='column'>
                        <td class='label' id='6'>
                            <label for='registration'>Registrační číslo</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='registration' name='registration' placeholder='Evidenční číslo' value=$registration>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='7'>
                            <label for='isbn'>Isbn</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='isbn' name='isbn' placeholder='Isbn' value='$isbn'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='8'>
                            <label for='subject'>Okruh</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='subject' name='subject' placeholder='Okruh' value='$subject'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='9'>
                            <label for='publisher'>Vydavatel</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='publisher' name='publisher' placeholder='Vydavatel' value='$publisher'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='10'>
                            <label for='author'>Autor</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='author' name='author' placeholder='Autor' value='$author'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='11'>
                            <label for='name'>Název</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='name' name='name' placeholder='Název' value='$name'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='12'>
                            <label for='price'>Cena</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' id='price' name='price' placeholder='Pořizovací cena' value='$price'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='13'
                            <label for='dateAdded'>Zaevidování</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='date' id='dateAdded' name='dateAdded' placeholder='Datum zaevidování' value='$dateAdded'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='14'>
                            <label for='note'>Poznámka</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='input' type='text' name='note' id='note' placeholder='poznámka' value='$note'>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='15'>
                            <label for='history'>Historie</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <p class='history' id='history'>$history</p>
                        </td>
                    </tr>

                    <tr class='column'>
                        <td class='label' id='16'>
                            <label for='discarded'>Vyřazeno</label>
                        </td>

                        <td class='middleColumn'></td>

                        <td>
                            <input class='checkbox' type='checkbox' id='discarded' name='discarded' $discarded>
                        </td>
                    </tr>

                    <tr class='column btnColumn' id='17'>
                        <td>
                            <input class='submitEditBtn btn' type='submit' name='edit' value='Upravit'>
                        </td>

                        <td class='backBtnTd'>
                            <button onclick='back()' class='btn backBtn'>Zpět</button>
                        </td>

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


    <script src="/scripts/editBook.js"></script>

    <script>
        let historyElem = document.getElementById('history');
        let history = historyElem.textContent;
        let shortHistory;
        let long = false;
        let shortened = false;

        console.log(history)

        if (history != '' && history.includes(',')) { // long
            shortHistory = history.split(',')[0] + ', ...';
            historyElem.textContent = shortHistory;
            long = true;
            shortened = true
            console.log('long');
        }


        historyElem.addEventListener('click', function(e) {
            historyElem = document.getElementById('history');

            console.log('click');

            if (long) {
                if (shortened) {
                    historyElem.textContent = history;
                    shortened = false;
                    console.log(history);
                }

                else {
                    historyElem.textContent = shortHistory;
                    shortened = true;
                    console.log(shortHistory);
                }
            }
        });



        function back() {
            window.location.href = 'index.php';
        }
    </script>
</body>
</html>
