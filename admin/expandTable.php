<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit;
    }


    require('/var/secrets.php');
    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];

    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    $count = isset($_GET['count']) ? intval($_GET['count']) : 0;
    $rowCount = isset($_GET['rowCount']) ? intval($_GET['rowCount']) : 0;
    $searchInput = isset($_GET['searchInput']) ? mysqli_real_escape_string($conn, $_GET['searchInput']) : '';
    $showDiscarded = isset($_GET['showDiscarded']) ? mysqli_real_escape_string($conn, $_GET['showDiscarded']) : '';
    $columns = isset($_GET['columns']) ? $_GET['columns'] : '';


    // search by
    $searchBy = "name LIKE '%$searchInput%' OR author LIKE '%$searchInput%' OR publisher LIKE '%$searchInput%' OR isbn LIKE '%$searchInput%' OR note LIKE '%$searchInput%' OR lentTo LIKE '%$searchInput%' OR class LIKE '%$searchInput%'";
    $columnClauses = ["registration LIKE '%$searchInput%'", "subject LIKE '%$searchInput%'", "author LIKE '%$searchInput%'", "name LIKE '%$searchInput%'", "price LIKE '%$searchInput%'", "dateAdded LIKE '%$searchInput%'", "lentTo LIKE '%$searchInput%'", "class LIKE '%$searchInput%'", "lendDate LIKE '%$searchInput%'", "reservation LIKE '%$searchInput%'", "note LIKE '%$searchInput%'"];
    $someColumnSelected = false;

    for ($i = 0; $i < count($columns); $i++) {
        if ($columns[$i] == 'true') {
            if (!$someColumnSelected) {
                $searchBy = '';
                $searchBy = $columnClauses[$i];
            } 
            
            else {
                $searchBy = $searchBy . ' OR ' . $columnClauses[$i];
            }

            $someColumnSelected = true;
        }
    }


    if (!$conn) {
        echo 'chyba pripojeni' . mysqli_connect_error();
    }


    if ($showDiscarded == 'true') {
        if ($searchInput == '') {
            $sql = "SELECT * FROM books LIMIT $rowCount, $count";
        }

        else {
            $sql = "SELECT * FROM books WHERE $searchBy LIMIT $rowCount, $count";
        }
    }

    else {
        if ($searchInput == '') {
            $sql = "SELECT * FROM books WHERE discarded=0 LIMIT $rowCount, $count";
        }

        else {
            $sql = "SELECT * FROM books WHERE ($searchBy) AND discarded=0 LIMIT $rowCount, $count";
        }     
    }

    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        echo 'Error: ' . mysqli_error($conn);
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

    if (count($data) < $count) {
        $count = count($data);
    }



    for ($i = 0; $i < $count; $i++) {

        $id = $data[$i]['id'];
        $registration = $data[$i]['registration'];
        $isbn = $data[$i]['isbn'];
        $subject = $data[$i]['subject'];
        $publisher = $data[$i]['publisher'];
        $author = $data[$i]['author'];
        $name = $data[$i]['name'];
        $price = $data[$i]['price'];
        $dateAdded = $data[$i]['dateAdded'];
        $lentTo = $data[$i]['lentTo'];
        $class = $data[$i]['class'];
        $lendDate = $data[$i]['lendDate'];
        $returnDate = $data[$i]['returnDate'];
        $reservation = $data[$i]['reservation'];
        $note = $data[$i]['note'];
        $discarded = $data[$i]['discarded'];

        if ($i % 2 === 0) {
            $evenNum = true;
        }

        else {
            $evenNum = false;
        }

        echo "<tr class='dataRow " . ($i % 2 === 1 ? 'evenRow' : '') . ' ' . ($discarded == 1 ? 'discardedRow' : '') . " '>
            <td class='firstTd'>
                <form action='editBook.php' method='post'>
                    <input type='hidden' name='id' value='$id'>
                    <button class='editBtn' type='submit'>Upravit</button>
                </form>
            </td>
            <td>$registration</td>
            <td>$subject</td>
            <td>$author</td>
            <td>$name</td>
            <td>$price</td>
            <td>$dateAdded</td>
            <td>$lentTo</td>
            <td>$class</td>
            <td>$lendDate</td>
            <td>$reservation</td>
            <td>$note</td>
        </tr>";
    }
?>