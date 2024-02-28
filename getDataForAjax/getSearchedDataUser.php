<?php
    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];



    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    $searchInput = isset($_GET['searchInput']) ? mysqli_real_escape_string($conn, $_GET['searchInput']) : '';

    $rows = 50;

    if (!$conn) {
        echo 'chyba pripojeni'.mysqli_connect_error();
    }


    if ($searchInput == '') {
        $sql = "SELECT id, author, name, returnDate, reservation, reservationExpiration FROM books WHERE discarded=0 LIMIT $rows";
    }

    else {
        $sql = "SELECT id, author, name, returnDate, reservation, reservationExpiration FROM books WHERE (name LIKE '%$searchInput%' OR author LIKE '%$searchInput%' OR isbn LIKE '%$searchInput%') AND discarded=0 LIMIT $rows";
    }


    $result = mysqli_query($conn, $sql);

    if ($result === false) {
            echo 'Error: '.mysqli_error($conn);
    }


    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


    if (count($data) < $rows) {
        $rows = count($data);
    }


    for ($i = 0; $i < $rows; $i++) {
        $id = $data[$i]['id'];
        $author = $data[$i]['author'];
        $name = $data[$i]['name'];
        $returnDate = $data[$i]['returnDate'];
        $reservation = $data[$i]['reservation'];
        $reservationExpiration = date('j. n.', strtotime($data[$i]['reservationExpiration']));

        $state = 'V knihovně';

        if ($returnDate != '') {
            if ($reservation != '') {
                $availableDate = date('Y-m-d', strtotime($returnDate . ' +3 days'));

                $state = "Zarezervováno do $availableDate";
            }

            else {
                $state = "Půjčeno do $returnDate";
            } 
        }

        else {
            if ($reservation != '') {
                $state = "Zarezervováno do $reservationExpiration";
            }
        }


        

        


        echo "
            <tr onclick=\"openMoreInfo('$id', '$name')\">
                <td>$author</td>
                <td>$name</td>
                <td>$state</td>
            </tr>
        ";
    }
?>