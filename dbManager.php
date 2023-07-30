<?php
    // TODO: dodelat kontrolo rezervaci a pujceni mezi tabulkama, kdyz to neni stejny tak asi upozornit admina, asi nejakym mailem kterej by to mohl shrnout co tenhle skript upravoval nebo to zapsat do logu
    // TODO: udelat funkci ktera napise poznamku uzivateli kdyz nevrati knihu vcas
    // TODO: domluvit maily

    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);




    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    if (!$conn) {
        echo 'chyba pripojeni'.mysqli_connect_error();
    }


    // CANCEL OLD RESERVATIONS
    $sql = "SELECT id, reservation, reservationExpiration FROM books";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
            echo 'Error: '.mysqli_error($conn);
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo count($data);


    for ($i = 0; $i < count($data); $i++) {
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        $reservationExpiration = $data[$i]['reservationExpiration'];

        echo $reservationExpiration;
        echo '<br>';

        if ($reservationExpiration != '0' && $reservationExpiration != '') { // book is reserved
            $reservationExpirationDate = new DateTime($data[$i]['reservationExpiration']);
            $reservationExpirationDate->setTime(0, 0, 0);

            $id = mysqli_real_escape_string($conn, $data[$i]['id']);
            $userLogin = mysqli_real_escape_string($conn, $data[$i]['reservation']);


            if ($reservationExpirationDate < $today) { // cancel reservation
                // books table
                $sql = "UPDATE books SET reservation='', reservationExpiration='' WHERE id='$id'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                        echo 'Error: '.mysqli_error($conn);
                }


                // users table, update reserved list
                $sql = "SELECT reserved FROM users WHERE login='$userLogin'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                        echo 'Error: '.mysqli_error($conn);
                }

                $reservedBooks = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['reserved'];
                $reservedBooks = explode(',', $reservedBooks);

                $index = array_search($id, $reservedBooks, true); // get index
                if ($index !== false) {
                    unset($reservedBooks[$index]); // delete
                    $reservedBooks = implode(',', $reservedBooks);
                }

                $sql = "UPDATE users SET reserved='$reservedBooks' WHERE login='$userLogin'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    echo 'Error: '.mysqli_error($conn);
                }
            }
        }
    }


    $conn->close();
?>