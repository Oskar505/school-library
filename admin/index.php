<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit;
    }


    if (isset($_POST['add'])) {
        $registration = $_POST['registration'];
        $isbn = $_POST['isbn'];
        $subject = $_POST['subject'];
        $class = $_POST['class'];
        $publisher = $_POST['publisher'];
        $author = $_POST['author'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $dateAdded = $_POST['dateAdded'];
        $lentTo = $_POST['lentTo'];
        $lendDate = $_POST['lendDate'];
        $returnDate = $_POST['returnDate'];
        $reservation = $_POST['reservation'];
        $note = $_POST['note'];
        $discarded = $_POST['discarded'];


        
        $allData = [$registration, $isbn, $subject, $class, $publisher, $author, $name, $price, $dateAdded, $lentTo, $lendDate, $returnDate, $reservation, $note, $discarded];




        $conn = mysqli_connect('localhost', 'test', 'Test22knih*', 'knihovna');

        if (!$conn) {
            echo 'Připojení k databázi se nezdařilo';
        }


        $allData = array_map(function($value) use ($conn) {
            return empty($value) ? NULL : mysqli_real_escape_string($conn, $value);
        }, $allData);
        

        // discarded
        if ($allData[14] == 'on') {
            $allData[14] = 1;
        }
        
        else {
            $allData[14] = 0;
        }


        
        $stmt = mysqli_prepare($conn, "INSERT INTO books (registration, isbn, subject, class, publisher, author, name, price, dateAdded, lentTo, lendDate, returnDate, reservation, note, discarded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        mysqli_stmt_bind_param($stmt, "ssssssssssssssi", $allData[0], $allData[1], $allData[2], $allData[3], $allData[4], $allData[5], $allData[6], $allData[7], $allData[8], $allData[9], $allData[10], $allData[11], $allData[12], $allData[13], $allData[14]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // send form only once
        header('Location: index.php');
        exit;
    }



    // edit

    if (isset($_POST['edit'])) {
        $id = $_POST['id'];
        $registration = $_POST['registration'];
        $isbn = $_POST['isbn'];
        $subject = $_POST['subject'];
        $class = $_POST['class'];
        $publisher = $_POST['publisher'];
        $author = $_POST['author'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $dateAdded = $_POST['dateAdded'];
        $lentTo = $_POST['lentTo'];
        $lendDate = $_POST['lendDate'];
        $returnDate = $_POST['returnDate'];
        $reservation = $_POST['reservation'];
        $note = $_POST['note'];
        $discarded = $_POST['discarded'];

        $allData = [$registration, $isbn, $subject, $class, $publisher, $author, $name, $price, $dateAdded, $lentTo, $lendDate, $returnDate, $reservation, $note, $discarded, $id];


        $conn = mysqli_connect('localhost', 'test', 'Test22knih*', 'knihovna');

        if (!$conn) {
            echo 'Připojení k databázi se nezdařilo';
        }


        $allData = array_map(function($value) use ($conn) {
            return empty($value) ? NULL : mysqli_real_escape_string($conn, $value);
        }, $allData);      

        // discarded
        if ($allData[14] == 'on') {
            $allData[14] = 1;
        }
        
        else {
            $allData[14] = 0;
        }

        $stmt = mysqli_prepare($conn, "UPDATE books SET registration = ?, isbn = ?, subject = ?, class = ?, publisher = ?, author = ?, name = ?, price = ?, dateAdded = ?, lentTo = ?, lendDate = ?, returnDate = ?, reservation = ?, note = ?, discarded = ? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssssssssssssssii", $allData[0], $allData[1], $allData[2], $allData[3], $allData[4], $allData[5], $allData[6], $allData[7], $allData[8], $allData[9], $allData[10], $allData[11], $allData[12], $allData[13], $allData[14], $allData[15]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // send form only once
        header('Location: index.php');
        exit;
    }


    //delete

    if (isset($_POST['delete'])) {
        $id = $_POST['id'];


        $conn = mysqli_connect('localhost', 'test', 'Test22knih*', 'knihovna');

        if (!$conn) {
            echo 'Připojení k databázi se nezdařilo';
        }


        $stmt = mysqli_prepare($conn, "DELETE FROM books WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // send form only once
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
    <link rel="stylesheet" href="/styles.css">
    <title>Administrace</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&family=Playfair+Display&family=Roboto&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    
    <?php
        $h1 = 'Administrace';
        include 'adminHeader.php'; 
    ?>


    <main class="container">
        <form action="addBook.php" method="post">
            <input class='addBtn btn' type="submit" value="Přidat knihu">
        </form>

        <div class='filters'>
            <input type="text" class='searchInput input' class='search' id='searchInput' placeholder='Vyhledat'>
            <label class='showDiscardedLabel label' for="showDiscarded">Zobrazit vyřazené</label>
            <input class='showDiscarded checkbox' type="checkbox" name="showDiscarded" id="showDiscarded">
        </div>
        

        <table class='dataTable'>
            <thead>
                <tr class='headerRow'>
                    <th></th>
                    <th>Evidenční číslo</th>
                    <th>Okruh</th>
                    <th>Autor</th>
                    <th>Název</th>
                    <th>Cena</th>
                    <th>Zapsáno</th>
                    <th>Půjčeno</th>
                    <th>Třída</th>
                    <th>Datum půjčení</th>
                    <th>Rezervace</th>
                    <th>Poznámka</th>
                </tr>
            </thead>

            <tbody id='tableBody'>
                <?php
                    $rows = 100;

                    $conn = mysqli_connect('localhost', 'test', 'Test22knih*', 'knihovna');

                    if (!$conn) {
                        echo 'chyba pripojeni'.mysqli_connect_error();
                    }


                    $sql = "SELECT * FROM books WHERE discarded=0 LIMIT $rows";
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
                        $returnDateObj = new DateTime($returnDate);
                        $todayDateObj = new DateTime();


                        if ($returnDateObj < $todayDateObj &&  $returnDate != '') {
                            $returnedInTime = false;
                            $returnedInTimeClass = 'notReturned';
                        }



                        echo "<tr class='dataRow " . ($i % 2 === 1 ? 'evenRow' : '') . ' ' . ($discarded == 1 ? 'discardedRow' : '') . ' ' . $returnedInTimeClass . " '>
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


                    echo "

                    "
                ?>
            </tbody>
        </table>

        <button class='expandTableBtn btn' onclick="loadMoreRows(100)">Další</button>

    </main>


    <!-- expandTableAJAX -->
    <script>
        var rowCount = <?php echo $rows; ?>;
        
        function loadMoreRows(count) {
            let searchInput = document.getElementById('searchInput');
            let showDiscarded = document.getElementById('showDiscarded');
            

            $.ajax({
                url: 'expandTable.php',
                type: 'GET',
                data: {
                    count: count,
                    rowCount: rowCount,
                    searchInput: searchInput.value,
                    showDiscarded: showDiscarded.checked
                },
                success: function(response) {
                    var tbody = $('#tableBody');
                    tbody.append(response);
                    rowCount += count;
                }
            });
        }


        // search

        let searchInput = document.getElementById('searchInput');
        let showDiscarded = document.getElementById('showDiscarded');

        searchInput.addEventListener('input', search);
        showDiscarded.addEventListener('input', search);


        //console.log(searchInput.value);
        //console.log(showDiscarded.checked);

        function search() {
            console.log('search')

            searchInput = document.getElementById('searchInput');
            showDiscarded = document.getElementById('showDiscarded');

            console.log(searchInput.value);
            console.log(showDiscarded.checked);

            $.ajax({
                url: 'getSearchedData.php',
                type: 'GET',
                data: {
                    searchInput: searchInput.value,
                    showDiscarded: showDiscarded.checked
                },
                success: function(response) {
                    var tbody = $('#tableBody');
                    tbody.html(response);
                }
            });
        }
    </script>


    <footer>

    </footer>
</body>
</html>


<script>
function openPopup() {
  // Vytvoříme nové okno
  var popup = window.open("popupForm.php", "Popup", "width=500,height=500");

  // Zamezíme přesměrování stránky
  return false;
}

function openEditPopup(id) {
    var popup = window.open("editPopup.php", "Popup", "width=500,height=500");
    return false;
}
</script>
