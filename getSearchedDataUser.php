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
        $sql = "SELECT id, author, name, returnDate, reservation FROM books LIMIT $rows";
    }

    else {
        $sql = "SELECT id, author, name, returnDate, reservation FROM books WHERE name LIKE '%$searchInput%' OR author LIKE '%$searchInput%' OR isbn LIKE '%$searchInput%' OR note LIKE '%$searchInput%' OR lentTo LIKE '%$searchInput%' OR class LIKE '%$searchInput%' LIMIT $rows";
    }
    echo 'showDiscarded true';


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

        $state = '';

        if ($returnDate != '') {
            if ($reservation != '') {
                $availableDate = date('Y-m-d', strtotime($returnDate . ' +7 days'));

                $state = "Zarezervováno do $availableDate";
            }

            else {
                $state = "Půjčeno do $returnDate";
            } 
        }

        else {
            if ($reservation != '') {
                $state = "Zarezervováno do +7 dní";
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