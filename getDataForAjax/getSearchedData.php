<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /login.php');
        exit;
    }

    require('/var/secrets.php');
    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];

    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    $searchInput = isset($_GET['searchInput']) ? mysqli_real_escape_string($conn, $_GET['searchInput']) : '';
    $showDiscarded = isset($_GET['showDiscarded']) ? mysqli_real_escape_string($conn, $_GET['showDiscarded']) : '';
    $columns = isset($_GET['columns']) ? $_GET['columns'] : '';
    $showNotReturned = isset($_GET['showNotReturned']) ? mysqli_real_escape_string($conn, $_GET['showNotReturned']) : '';
    


    // search by
    //default
    $searchBy = "id LIKE '%$searchInput%' OR name LIKE '%$searchInput%' OR author LIKE '%$searchInput%' OR publisher LIKE '%$searchInput%' OR isbn LIKE '%$searchInput%' OR note LIKE '%$searchInput%' OR lentTo LIKE '%$searchInput%' OR class LIKE '%$searchInput%'";
    
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


    // save to cookies
    setcookie('searchBy', $searchBy, 0, '/admin');
    setcookie('showDiscarded', $showDiscarded, 0, '/admin');


    $rows = 100;
    

    if (!$conn) {
        echo 'chyba pripojeni'.mysqli_connect_error();
    }


    if ($showNotReturned == 'true') {
        $sql = "SELECT * FROM books WHERE returnDate < CURDATE() AND returnDate != ''";
    }

    else {
        if ($showDiscarded == 'true') {
            if ($searchInput == '') {
                $sql = "SELECT * FROM books";
            }
    
            else {
                $sql = "SELECT * FROM books WHERE $searchBy";
            }
        }
    
        else {
            if ($searchInput == '') {
                $sql = "SELECT * FROM books WHERE discarded=0";
            }
    
            else {
                $sql = "SELECT * FROM books WHERE ($searchBy) AND discarded=0";
            }     
        }
    }


    $result = mysqli_query($conn, $sql . " LIMIT $rows"); // add limit

    if ($result === false) {
        echo 'Error: '.mysqli_error($conn);
    }


    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


    if (count($data) < $rows) {
        $rows = count($data);
    }


    // count results
    $wherePart = explode('WHERE', $sql)[1]; // get only where part from sql

    $wherePart = empty($wherePart) ? '' : 'WHERE ' . $wherePart; 

    $countSql = "SELECT COUNT(*) FROM books $wherePart"; // count

    $result = mysqli_query($conn, $countSql);

    if ($result === false) {
        echo 'Error: '.mysqli_error($conn);
    }

    $booksCount = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];

    echo $booksCount;
    echo '###datasplit###';

    for ($i = 0; $i < $rows; $i++) {
        
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


        // book wasnt returned in time
        $returnedInTime = true;
        $returnedInTimeClass = '';

        if (!empty($returnDate)) {
            $returnDateObj = new DateTime($returnDate);
            $todayDateObj = new DateTime();


            if ($returnDateObj < $todayDateObj &&  $returnDate != '') {
                $returnedInTime = false;
                $returnedInTimeClass = 'notReturned';
            }
        }
        


        echo "<tr class='dataRow " . ($i % 2 === 1 ? 'evenRow' : '') . ' ' . ($discarded == 1 ? 'discardedRow' : '') . ' ' . $returnedInTimeClass . " '>
            <td class='firstTd'>
                <form action='editBook.php' method='get'>
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