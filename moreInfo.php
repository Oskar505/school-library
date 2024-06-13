<?php
    // get secrets
    require('/var/secrets.php');
    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    include 'functions.php';
    include 'sendMail.php';

    session_start();

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);



    // update reserved books in session
    if (isset($_SESSION['userLoggedIn'])) {
        $userLogin = $_SESSION['login'];

        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

        $sql = "SELECT reserved, borrowed FROM users WHERE login = '$userLogin'";
        $result = mysqli_query($conn, $sql);

        if ($result === false) {
            echo 'Error: '.mysqli_error($conn);
        }
        
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];
        $reservedBooks = $data['reserved'];
        $borrowedBooks = $data['borrowed'];

        $_SESSION['reserved'] = $reservedBooks;
        $_SESSION['borrowed'] = $borrowedBooks;

        $conn->close();
    }
    

    $reservationOk = false;


    // get book data
    if (isset($_GET['id'])) {
        $bookId = intval($_GET['id']);


        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

        if (!$conn) {
            echo 'chyba pripojeni'.mysqli_connect_error();
        }


        $bookId = mysqli_real_escape_string($conn, $bookId); // escape string

        $sql = "SELECT author, name, returnDate, reservation FROM books WHERE id=$bookId";
        $result = mysqli_query($conn, $sql);

        if ($result === false) {
            echo 'Error: '.mysqli_error($conn);
        }


        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $name = $data[0]['name'];
        $author = $data[0]['author'];
        $returnDate = $data[0]['returnDate'];

        $reserveBtnText = 'Rezervovat';


        // reserve book or cancel reservation
        if (isset($_POST['id'])) {
            if (isset($_SESSION['userLoggedIn'])) {
                $bookId = intval($_POST['id']);
                $userLogin = $_SESSION['login'];


                $reservedBooks = $_SESSION['reserved'];

                if (!empty($reservedBooks)) {
                    $reservedBooks = explode(',', $reservedBooks);
                }

                else {
                    $reservedBooks = [];
                }
                

                if (!in_array($bookId, $reservedBooks)) { // reserve

                    if (!$conn) {
                        echo 'chyba pripojeni'.mysqli_connect_error();
                    }

                    // escape string
                    $bookId = mysqli_real_escape_string($conn, $bookId); 
                    $userLogin = mysqli_real_escape_string($conn, $userLogin);


                    // if book is not reserved
                    $sql = "SELECT COUNT(*) FROM books WHERE id = $bookId AND (reservation IS NULL OR reservation = '')";
                    $result = mysqli_query($conn, $sql);

                    if ($result === false) {
                        echo 'Error: '.mysqli_error($conn);
                    }
                    
                    $reserved = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];

                    // reservation limit (3)
                    $sql = "SELECT reserved, class FROM users WHERE login = '$userLogin'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $reservedString = $row['reserved'];
                        $class = $row['class'];

                        if ($class == 'Zaměstnanec') { // infinite for teachers
                            $reservedBooksCount = 0;
                        }

                        else { // students
                            // split
                            $reservedBooksCount = 0;

                            if ($reservedString != null && $reservedString != '') {
                                $reservedArray = explode(',', $reservedString);
                                $reservedBooksCount = count($reservedArray);
                            }
                        }
                    }

                    
                    if ($reserved != 0 && $reservedBooksCount < 3) {
                        // reservation expiration date
                        if ($returnDate != '') { // lent
                            $returnDateObj = new DateTime($returnDate);
                            $today = new DateTime();

                            if ($today > $returnDateObj) { // if book wasn't returned in time, adds 3 days to today's date
                                $today->add(new DateInterval('P3D')); // +3 d
                                $reservationExpiration = $today->format('Y-m-d');
                            }

                            else {
                                $returnDateObj->add(new DateInterval('P3D')); // +3 d
                                $reservationExpiration = $returnDateObj->format('Y-m-d');
                            }
                            
                        }

                        else { // available
                            $today = new DateTime();
                            $today->add(new DateInterval('P3D'));// +3 d
                            $reservationExpiration = $today->format('Y-m-d');
                        }


                        // update books table
                        $sql = "UPDATE books SET reservation='$userLogin', reservationExpiration='$reservationExpiration' WHERE id=$bookId";
                        $result = mysqli_query($conn, $sql);

                        if ($result === false) {
                            echo 'Error: '.mysqli_error($conn);
                        }


                        // update users table
                        $sql = "UPDATE users 
                        SET reserved = IF(reserved IS NULL OR reserved = '', '$bookId', CONCAT(reserved, ',', '$bookId'))
                        WHERE login='$userLogin'";

                        $result = mysqli_query($conn, $sql);

                        if ($result === false) {
                            echo 'Error: '.mysqli_error($conn);
                        }

                        else {
                            $reservationOk = true;

                            //TODO: nejaky lepsi upozorneni
                            echo '<h1>Kniha byla zarezervována</h1>
                            <a href="index.php">Domů</a>';

                            

                            // send mail
                            $mailReservationExpiration = date_create($reservationExpiration);
                            $mailReservationExpiration = date_format($mailReservationExpiration, "j. n.");

                            $mail = new SendMail($userLogin);
                            $mail->bookReserved($name, $mailReservationExpiration);

                            exit;
                        }
                    }


                    else {
                        showError('Rezervace se nezdařila.', 'Překročili jste limit rezervací, nebo tato kniha byla už rezervována.');
                    }
                }




                else { // cancel reservation
                    // escape string
                    $bookId = mysqli_real_escape_string($conn, $bookId); 
                    $userLogin = mysqli_real_escape_string($conn, $userLogin);


                    // if book is reserved
                    $sql = "SELECT COUNT(*) FROM books WHERE id = $bookId AND (reservation IS NULL OR reservation = '')";
                    $result = mysqli_query($conn, $sql);

                    if ($result === false) {
                        echo 'Error: '.mysqli_error($conn);
                    }
                    
                    $reserved = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];

                    
                    if ($reserved == 0) { // book is reserved
                        // update books table
                        $sql = "UPDATE books SET reservation=NULL, reservationExpiration=NULL WHERE id=$bookId";
                        $result = mysqli_query($conn, $sql);

                        if ($result === false) {
                            echo 'Error: '.mysqli_error($conn);
                        }


                        // update users table
                        $reservedBooks = $_SESSION['reserved'];
                        $reservedBooks = explode(',', $reservedBooks);

                        $index = array_search($bookId, $reservedBooks, true); // get index
                        if ($index !== false) {
                            unset($reservedBooks[$index]); // delete
                            $reservedBooks = implode(',', $reservedBooks);
                        }

                        $sql = "UPDATE users SET reserved='$reservedBooks' WHERE login='$userLogin'";
                        $result = mysqli_query($conn, $sql);

                        if ($result === false) {
                            echo 'Error: '.mysqli_error($conn);
                        }

                        $_SESSION['reserved'] = $reservedBooks; // update session


                        echo '<h1>Rezervace byla zrušena</h1>
                        <a href="index.php">Domů</a>';
                        exit;
                    }


                    else {
                        showError('Zrušení rezervace se nezdařilo.', 'Tato kniha není rezervována. V tabulce books není zapsaná rezervace.');
                    }
                }

                


                
            }


            else {
                showError('Rezervace se nezdařila', 'Přihlašte se prosím uživatelským účtem.');
            }
        }


        $conn->close();
    }


    

    // error
    else {
        showError('Nepodařilo se získat id knihy', 'Vraťte se na hlavní stránku a zkuste to znovu');
    }



 // reservation btn text and reservation info

    //default values
    $reserveBtnText = 'Rezervovat';
    $reservationInfo = '';
    $reserveBtnDeactivate = '';
    $iconHelp = '';
    $btnHelp = '';


    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    if (!$conn) {
        showError('Chyba připojení', 'Nastala chyba připojení k databázi, zkuste to prosím později.');
    }

    // book reservation
    // not available = 0, available = 1
    $sql = "SELECT COUNT(*) FROM books WHERE id = $bookId AND (reservation IS NULL OR reservation = '')";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
    }
    
    $reserved = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];


    // lent to someone
    // not available = 0, available = 1
    $sql = "SELECT COUNT(*) FROM books WHERE id = $bookId AND (lentTo IS NULL OR lentTo = '')";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
    }
    
    $lentToSomeone = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];

    $conn->close();


    $reservedByUser = false;


    if (isset($_SESSION['userLoggedIn'])) {

        $reservedBooks = $_SESSION['reserved'];

        if ($reservedBooks != null) {
            $reservedBooks = explode(',', $reservedBooks);

            if (in_array($bookId, $reservedBooks) || $reservationOk) { // rezervovano uzivatelem
                $reserveBtnText = 'Zrušit rezervaci';
                $iconHelp = 'Kniha je rezervovaná';
                $availabilityIcon = '
                <svg title="Kniha je rezervovaná" id="availabilityInfoIcon" xmlns="http://www.w3.org/2000/svg" height="44px" viewBox="0 -960 960 960" width="44px" fill="#00D100">
                    <path d="M378-246 154-470l43-43 181 181 384-384 43 43-427 427Z"/>
                </svg>';
                $reservedByUser = true;
            }
        }
        

        if ($reserved == 0 && !$reservedByUser) { // not available
            $reserveBtnText = 'Rezervovat';
            $btnHelp = 'Knihu má zarezervovanou někdo jiný';
            $availabilityIcon = '';
            $reserveBtnDeactivate = 'deactivateBtn';
        }
        

        elseif ($lentToSomeone == 0 && $reserved != 0) {
            $reserveBtnText = 'Rezervovat';
            $availabilityIcon = '
            <svg title="Kniha je půjčená, stále si ji můžete zarezervovat." id="availabilityInfoIcon" xmlns="http://www.w3.org/2000/svg" height="44px" viewBox="0 -960 960 960" width="44px" fill="#ff8000">
                <path d="m40-120 440-760 440 760H40Zm104-60h672L480-760 144-180Zm340.18-57q12.82 0 21.32-8.68 8.5-8.67 8.5-21.5 0-12.82-8.68-21.32-8.67-8.5-21.5-8.5-12.82 0-21.32 8.68-8.5 8.67-8.5 21.5 0 12.82 8.68 21.32 8.67 8.5 21.5 8.5ZM454-348h60v-224h-60v224Zm26-122Z"/>
            </svg>';
            $iconHelp = 'Kniha je půjčená, stále si ji můžete zarezervovat.';


            //pujceno uzivatelem
            $borrowedBooks = $_SESSION['borrowed'];
            $borrowedBooks = explode(',', $borrowedBooks);

            if (in_array($bookId, $borrowedBooks)) {
                $availabilityIcon = '';
                $reserveBtnDeactivate = 'deactivateBtn';
                $iconHelp = '';
                $btnHelp = 'Knihu už máte půjčenou';
            }
        }
    }

    else {
        if ($reserved == 0) { // not available
            $reserveBtnText = 'Rezervovat';
            $availabilityIcon = '';
            $reserveBtnDeactivate = 'deactivateBtn';
            $btnHelp = 'Knihu má zarezervovanou někdo jiný';
        }

        elseif ($lentToSomeone == 0 && $reserved != 0) {
            $reserveBtnText = 'Rezervovat';
            $iconHelp = 'Kniha je půjčená, stále si ji můžete zarezervovat.';
            $availabilityIcon = '
            <svg title="Kniha je půjčená, stále si ji můžete zarezervovat." id="availabilityInfoIcon" xmlns="http://www.w3.org/2000/svg" height="44px" viewBox="0 -960 960 960" width="44px" fill="#ff8000">
                <path d="m40-120 440-760 440 760H40Zm104-60h672L480-760 144-180Zm340.18-57q12.82 0 21.32-8.68 8.5-8.67 8.5-21.5 0-12.82-8.68-21.32-8.67-8.5-21.5-8.5-12.82 0-21.32 8.68-8.5 8.67-8.5 21.5 0 12.82 8.68 21.32 8.67 8.5 21.5 8.5ZM454-348h60v-224h-60v224Zm26-122Z"/>
            </svg>';
        }
    }
?>








<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name?></title>

    <link rel="stylesheet" href="stylesBlue.css">
    <link rel="stylesheet" href="mediaQueries.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@900&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="container moreInfoBody">
    <?php include_once 'nav.php'?>

    <main class="moreInfoMain">
        <!-- <div class="moreInfoTextGlow" data-text="Bílá nemoc"></div> -->

        <section class="moreInfoMainContent">
            <h1 class="bookNameHeader"><?php echo $name?></h1>
            
            <h2 class="moreInfoAuthor"><?php echo $author?></h2>

            <div>
                <p class="bookDescription" id="bookInfo">
                    popis
                </p>

                <p class="moreInfoSource">Zdroj: <a href="#" id="bookSourceLink">Google knihy</a></p>
            </div>
            

            <form action="moreInfo.php?id=<?php echo $bookId ?>" method="post" id="reserveForm" name="reserve">
                <div class="moreInfoReserveBtn <?php echo $reserveBtnDeactivate ?>" id="reserveBtn" title="<?php echo $btnHelp ?>">
                    <input type="hidden" name="id" value="<?php echo $bookId ?>">
                    <p>Rezervovat</p>
                    <div class="availabilityInfo" id="availabilityInfo" title="<?php echo $iconHelp ?>">
                        <?php echo $availabilityIcon ?>
                    </div>
                </div>
            </form>
            
        </section>
    </main>

        
    



    <script>

        document.getElementById('reserveBtn').addEventListener('click', function() {
            if (event.target.id !== 'availabilityInfo' && event.target.id !== 'availabilityInfoIcon' && !document.getElementById('reserveBtn').classList.contains('deactivateBtn')) {
                document.getElementById('reserveForm').submit();
            }
        })
        


        name = '<?php echo $name ?>';
        author = '<?php echo $author ?>';

        name = name.replace(/ /g, "+");
        author = author.replace(/ /g, "+");

        // author
        var commaIndex = author.indexOf(',');

        if (commaIndex !== -1) {
            author = author.substring(0, commaIndex);
        }

        console.log(author);

        //author = 'Aeschylus';  nekde je autor anglicky a kdyz se hleda ceskym tak tam nejsou informace



        let url = "https://www.googleapis.com/books/v1/volumes?q=" + name /*+ "+inauthor:" + author*/;

        console.log(url);

        fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Chyba při komunikaci s Google Books API: ' + response.status);
            }

            return response.json();
        })
        .then(data => {
            console.log(data);

            let description = data.items[0].volumeInfo.description;
            console.log(description);

            var book = data.items[0];
            var sourceLink = book.volumeInfo.infoLink;
            // new link
            var bookId = book.id;
            var bookTitle = book.volumeInfo.title.replace(/\s/g, "_");
            var googleBooksUrl = "https://www.google.cz/books/edition/" + bookTitle + "/" + bookId + "?hl=cs&gbpv=0";
            console.log(googleBooksUrl);


            let bookInfoElement = document.getElementById('bookInfo');
            let sourceLinkElement = document.getElementById('bookSourceLink');

            bookInfoElement.textContent = description;
            sourceLinkElement.href = googleBooksUrl;

            if (description == undefined) {
                let moreInfoSourceEl = document.getElementsByClassName('moreInfoSource')[0]
                console.log('no description' + moreInfoSourceEl.textContent)
                moreInfoSourceEl.innerHTML = moreInfoSourceEl.innerHTML.replace('Zdroj: ', '')
            }

        }).catch(error => {
            console.error('Chyba: ', error)
        })
    </script>
</body>
</html>