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
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    
    <?php
        $h1 = 'Administrace';
        include 'adminHeader.php'; 
    ?>


    <main class="container">
        <div class="adminButtons">
            <form class="addBtnForm" action="addBook.php" method="post">
                <input class='addBtn btn' type="submit" value="Přidat knihu">
            </form>

            <a href="downloadData.php" class="material-symbols-outlined downloadBtn">
                <img class="downloadImg" src="/img/download.svg" alt="Stáhnout">
            </a>
        </div>
        

        <div class='filters'>
            <input type="text" class='searchInput input' class='search' id='searchInput' placeholder='Vyhledat'>
            <label class='showDiscardedLabel label' for="showDiscarded">Zobrazit vyřazené</label>
            <input class='showDiscarded checkbox' type="checkbox" name="showDiscarded" id="showDiscarded">
        </div>

        <?php
            $conn = mysqli_connect('localhost', 'test', 'Test22knih*', 'knihovna');

            if (!$conn) {
                echo 'Připojení k databázi se nezdařilo';
            }

            // get count
            $sql = "SELECT COUNT(*) FROM books";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                    echo 'Error: '.mysqli_error($conn);
            }

            $resultCount = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];
            global $resultCount;


            // not returned
            $sql = "SELECT COUNT(*) AS count FROM books WHERE returnDate < CURDATE()";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                echo 'Error: ' . mysqli_error($conn);
            }

            else {
                $row = mysqli_fetch_assoc($result);
                $notReturnedCount = $row['count'];
            }

            echo "
            <div class='info'>
            <div class='info-block'>
                <h2>Počet výsledků:</h2>
                <p class='result-count'>$resultCount</p>
            </div>
            <div class='info-block' id='notReturned'>
                <h2>Nevrácené knihy:</h2>
                <p class='unreturned-books'>$notReturnedCount</p>
            </div>
            </div>
            ";

        ?>
        

        

        <table class='dataTable'>
            <thead>
                <tr class='headerRow'>
                    <th></th>
                    <th id='registration'>Evidenční číslo</th>
                    <th id='subject'>Okruh</th>
                    <th id='author'>Autor</th>
                    <th id='name'>Název</th>
                    <th id='price'>Cena</th>
                    <th id='dateAdded'>Zapsáno</th>
                    <th id='lentTo'>Půjčeno</th>
                    <th id='class'>Třída</th>
                    <th id='lendDate'>Datum půjčení</th>
                    <th id='reservation'>Rezervace</th>
                    <th id='note'>Poznámka</th>
                </tr>
            </thead>

            <tbody id='tableBody'>
                <?php
                    $rows = 50;

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


                    $notRetunedCount = 0;

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
        
        <button class='tablePagesControl btn' onclick="changePage('previous')">Předchozí</button>
        <button class='tablePagesControl btn' onclick="changePage('next')">Další</button>
        <select name='pageSelect' id='pageSelect'>
            <?php 
                $pageCount = $resultCount / 50;
                $pageCount = ceil($pageCount);

                for ($i = 1; $i <= $pageCount; $i++) {
                    //$page = $i++;
                    $page = $i;
                    echo "<option value='$i'>$i</option>";
                }
            ?>
        </select>
    </main>






    <script>

        let page = document.getElementById('pageSelect');
        let actualPageNum = null;

        //page.value = 1;

        
        function changePage(pageNum) {
            count = 50;

            console.log('changePage');

            actualPageNum == null ? actualPageNum = 1 : ''  ;

            pageNum == 'next' ? pageNum = actualPageNum ++ : '';
            pageNum == 'previous' ? pageNum = actualPageNum -- : '';

            console.log(page.value);

            page.selectedIndex = pageNum;

            let offset = actualPageNum * 50;


            let searchInput = document.getElementById('searchInput');
            let showDiscarded = document.getElementById('showDiscarded');

            columns = [registration, subject, author, name, price, dateAdded, lentTo, $class, lendDate, reservation, note];
            
            console.log(columns);

            actualPageNum = pageNum;
            

            $.ajax({
                url: 'expandTable.php',
                type: 'GET',
                data: {
                    count: count,
                    offset: offset,
                    searchInput: searchInput.value,
                    showDiscarded: showDiscarded.checked,
                    columns: columns.value
                },
                success: function(response) {
                    var tbody = $('#tableBody');
                    tbody.html(response);
                }
            });
        }


        // search

        let searchInput = document.getElementById('searchInput');
        let showDiscarded = document.getElementById('showDiscarded');
        searchInput.addEventListener('input', search);
        showDiscarded.addEventListener('input', search);

        // search by
        let registration = false;
        let subject = false;
        let author = false;
        let name = false;
        let price = false;
        let dateAdded = false;
        let lentTo = false;
        let $class = false;
        let lendDate = false;
        let reservation = false;
        let note = false;

        var columns = [registration, subject, author, name, price, dateAdded, lentTo, $class, lendDate, reservation, note];

        var registrationEl = document.getElementById('registration');
        let subjectEl = document.getElementById('subject');
        let authorEl = document.getElementById('author');
        let nameEl = document.getElementById('name');
        let priceEl = document.getElementById('price');
        let dateAddedEl = document.getElementById('dateAdded');
        let lentToEl = document.getElementById('lentTo');
        let $classEl = document.getElementById('class');
        let lendDateEl = document.getElementById('lendDate');
        let reservationEl = document.getElementById('reservation');
        let noteEl = document.getElementById('note');


        // event listeners

        //expand
        page.addEventListener('change', function () {
            changePage(page);
        });


        //search
        registrationEl.addEventListener('click', function() {
            registration = !registration;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            registration ? color = '#2667ff' : color = '#2693ff';
            registrationEl.style.backgroundColor = color;
        });
        document.getElementById('subject').addEventListener('click', function() {
            subject = !subject;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            subject ? color = '#2667ff' : '#2693ff';
            subjectEl.style.backgroundColor = color;
        });
        document.getElementById('author').addEventListener('click', function() {
            author = !author;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            author ? color = '#2667ff' : '#2693ff';
            authorEl.style.backgroundColor = color;
        });
        document.getElementById('name').addEventListener('click', function() {
            name = !name;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            name ? color = '#2667ff' : '#2693ff';
            nameEl.style.backgroundColor = color;
        });
        document.getElementById('price').addEventListener('click', function() {
            price = !price;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            price ? color = '#2667ff' : '#2693ff';
            priceEl.style.backgroundColor = color;
        });
        document.getElementById('dateAdded').addEventListener('click', function() {
            dateAdded = !dateAdded;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            dateAdded ? color = '#2667ff' : '#2693ff';
            dateAddedEl.style.backgroundColor = color;
        });
        document.getElementById('lentTo').addEventListener('click', function() {
            lentTo = !lentTo;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            lentTo ? color = '#2667ff' : '#2693ff';
            lentToEl.style.backgroundColor = color;
        });
        document.getElementById('class').addEventListener('click', function() {
            $class = !$class;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            $class ? color = '#2667ff' : '#2693ff';
            $classEl.style.backgroundColor = color;
        });
        document.getElementById('lendDate').addEventListener('click', function() {
            lendDate = !lendDate;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            lendDate ? color = '#2667ff' : '#2693ff';
            lendDateEl.style.backgroundColor = color;
        });
        document.getElementById('reservation').addEventListener('click', function() {
            reservation = !reservation;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            reservation ? color = '#2667ff' : '#2693ff';
            reservationEl.style.backgroundColor = color;
        });
        document.getElementById('note').addEventListener('click', function() {
            note = !note;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            note ? color = '#2667ff' : '#2693ff';
            noteEl.style.backgroundColor = color;
        });


        // not returned
        let showNotReturned = false;

        document.getElementById('notReturned').addEventListener('click', function() {
            showNotReturned = !showNotReturned;
            console.log(showNotReturned);
            search();
        })




        function search() {
            console.log('search')

            searchInput = document.getElementById('searchInput');
            showDiscarded = document.getElementById('showDiscarded');

            if (searchInput.value == '') {
                document.cookie = `searchBy=;`;
                console.log(getCookie('searchBy'));
            }

            else {
                console.log(getCookie('searchBy'))
            }

            columns = [registration, subject, author, name, price, dateAdded, lentTo, $class, lendDate, reservation, note];


            $.ajax({
                url: 'getSearchedData.php',
                type: 'GET',
                data: {
                    searchInput: searchInput.value,
                    showDiscarded: showDiscarded.checked,
                    columns: columns,
                    showNotReturned: showNotReturned
                },
                success: function(response) {
                    var tbody = $('#tableBody');
                    tbody.html(response);
                }
            });
        }

        // set cookies for downloading data
        searchInput.addEventListener('input', setDownloadCookies);
        showDiscarded.addEventListener('input', setDownloadCookies);

        document.cookie = `searchInput=''`;
        document.cookie = `showDiscarded=false`;


        function setDownloadCookies() {
            searchInput = document.getElementById('searchInput');
            showDiscarded = document.getElementById('showDiscarded');

            document.cookie = `searchInput=${searchInput}`;
            document.cookie = `showDiscarded=${showDiscarded}`;
        }


        // Funkce pro získání hodnoty cookie podle názvu
        function getCookie(name) {
            var cookieName = name + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var cookieArray = decodedCookie.split(";");

            for (var i = 0; i < cookieArray.length; i++) {
                var cookie = cookieArray[i].trim();
                if (cookie.indexOf(cookieName) === 0) {
                    return cookie.substring(cookieName.length, cookie.length);
                }
            }

            return "";
        }
    </script>


    <footer>

    </footer>
</body>
</html>