<?php
    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];



    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    $searchInput = isset($_GET['searchInput']) ? mysqli_real_escape_string($conn, $_GET['searchInput']) : '';

    $rows = 9;

    if (!$conn) {
        echo 'chyba pripojeni'.mysqli_connect_error();
    }


    if ($searchInput == '') {
        $sql = "SELECT id, author, name FROM books WHERE discarded=0 AND note='MATURITNÍ ČETBA' LIMIT $rows";
    }

    else {
        $sql = "SELECT id, author, name FROM books WHERE (name LIKE '%$searchInput%' OR author LIKE '%$searchInput%' OR isbn LIKE '%$searchInput%') AND discarded=0 LIMIT $rows";
    }


    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        echo 'Error: '.mysqli_error($conn);
    }


    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);



    for ($i = 0; $i < count($data); $i++) {
        $id = $data[$i]['id'];
        $author = $data[$i]['author'];
        $name = $data[$i]['name'];


        echo "
            <div class='result'>
                <span class='bookInfo'>
                    <p class='bookName'>$name</p>
                    <p class='bookAuthor'>$author</p>
                </span>
                
                <a href='moreInfo.php?id=$id'>
                    <span class='material-symbols-outlined moreIcon'>add</span>
                </a>
            </div>
        ";
    }