<?php
    // get secrets
    require('/var/secrets.php');
    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    session_start();

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


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

        $reserveBtnText = 'Rezervovat';


        // reserve book or cancel reservation
        if (isset($_POST['reserve'])) {
            if (isset($_SESSION['userLoggedIn'])) {
                $bookId = intval($_POST['id']);
                $userLogin = $_SESSION['login'];


                $reservedBooks = $_SESSION['reserved'];
                $reservedBooks = explode(',', $reservedBooks);

                if (!in_array($bookId, $reservedBooks)) { // reserve
                    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

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

                    
                    if ($reserved != 0) {
                        // reservation expiration date
                        $today = new DateTime();
                        $today->add(new DateInterval('P3D'));
                        $reservationExpiration =  $today->format('Y-m-d');

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

                            echo '<h1>Kniha byla zarezervována</h1>
                            <a href="index.php">Domů</a>';
                            exit;
                        }
                    }


                    else {
                        echo '<h1>Rezervace se nezdařila.</h1>
                        <p>Tato kniha není volná.</p>
                        <a href="index.php">Domů</a>';
                        exit;
                    }
                }




                else { // cancel reservation
                    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

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
                        echo '<h1>Zrušení rezervace se nezdařilo.</h1>
                        <p>Tato kniha není rezervována. V tabulce books není zapsaná rezervace.</p>
                        <a href="index.php">Domů</a>';
                        exit;
                    }
                }

                


                
            }


            else {
                echo '<h1>Rezervace se nezdařila.</h1>
                <a href="userLogin.php">Přihlaste se.</a>
                <p>Pokud jste přihlášeni jako administrátor, přihlašte se uživatelským účtem.</p>';
                exit;
            }
        }
    }


    

    // error
    else {
        echo '<h1>Nepodařilo se získat id knihy</h1>
        <p>Vraťte se na hlavní stránku a zkuste to znovu</p>';
        exit;
    }



 // reservation btn text and reservation info

    //default values
    $reserveBtnText = 'Rezervovat';
    $reservationInfo = '<span class="material-symbols-outlined bookAvailable">done</span>
    <p>V knihovně</p>';
    $reserveBtnDeactivate = '';


    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    if (!$conn) {
        echo 'chyba pripojeni'.mysqli_connect_error();
    }

    // if book is not reserved
    $sql = "SELECT COUNT(*) FROM books WHERE id = $bookId AND (reservation IS NULL OR reservation = '')";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        echo 'Error: '.mysqli_error($conn);
    }
    
    $reserved = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];


    if (isset($_SESSION['userLoggedIn'])) {
        $reservedBooks = $_SESSION['reserved'];
        $reservedBooks = explode(',', $reservedBooks);


        if (in_array($bookId, $reservedBooks) || $reservationOk) { // rezervovano uzivatelem
            $reserveBtnText = 'Zrušit rezervaci';
            $reservationInfo = '
            <span class="material-symbols-outlined bookAvailable">done</span>
            <p>Rezervováno</p>';
            echo 'ok';
            echo $reservationOk;
        }


        elseif ($reserved == 0) {
            $reserveBtnText = 'Rezervovat';
            $reservationInfo = '
            <span class="material-symbols-outlined bookNotAvailable">close</span>
            <p>Rezervováno</p>';
            echo 'not available';
            $reserveBtnDeactivate = 'deactivateBtn';
        }
    }

    else {
        if ($reserved == 0) {
            $reserveBtnText = 'Rezervovat';
            $reservationInfo = '
            <span class="material-symbols-outlined bookNotAvailable">close</span>
            <p>Rezervováno</p>';
            echo 'not available';
            $reserveBtnDeactivate = 'deactivateBtn';
        }
    }
?>


<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name?></title>

    <link rel="stylesheet" href="styles.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head>
<body>
    <header class="container moreInfoHeader">
        <h1 class="bookName"><?php echo $name?></h1>
        <h2 class="author"><?php echo $author?></h2>

        <nav>
            <a href="">lorem</a>
            <a href="">ipsum</a>
            <a href="">dolor</a>
            <a href="">sit</a>
            <a href="">amet</a>
        </nav>
    </header>
    


    <main class="container moreInfoMain">
        <div class="description">
            <div>
                <p id="bookInfo">Popis</p>
                <p class="bookInfoSource">Zdroj: <a id="bookSourceLink" href="" target="_blank">google books</a></p>
            </div>
    
            <img class="bookImg" id="thumbnail" src="" alt="Fotka knihy">
        </div>


        <form action="moreInfo.php?id=<?php echo $bookId ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $bookId ?>">
            <input type="submit" name="reserve" value="<?php echo $reserveBtnText ?>" class="btn reservationBtn <?php echo $reserveBtnDeactivate ?>">
        </form>

        <div class="reservationInfo">
            <?php echo $reservationInfo ?>
        </div>  
    </main>
    


    <script>
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



        let url = "https://www.googleapis.com/books/v1/volumes?q=" + name + "+inauthor:" + author;

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
            var thumbnailUrl = book.volumeInfo.imageLinks.thumbnail;
            // new link
            var bookId = book.id;
            var bookTitle = book.volumeInfo.title.replace(/\s/g, "_");
            var googleBooksUrl = "https://www.google.cz/books/edition/" + bookTitle + "/" + bookId + "?hl=cs&gbpv=0";
            console.log(googleBooksUrl);


            let bookInfoElement = document.getElementById('bookInfo');
            let sourceLinkElement = document.getElementById('bookSourceLink');
            let thumbnailElement = document.getElementById('thumbnail');

            bookInfoElement.textContent = description;
            sourceLinkElement.href = googleBooksUrl;
            thumbnailElement.src = thumbnailUrl;

        }).catch(error => {
            console.error('Chyba: ', error)
        })
    </script>
</body>
</html>