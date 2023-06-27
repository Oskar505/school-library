<?php
    // get secrets
    require('/var/secrets.php');
    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);


        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

        if (!$conn) {
            echo 'chyba pripojeni'.mysqli_connect_error();
        }


        $sql = "SELECT author, name, returnDate, reservation FROM books WHERE id=$id";
        $result = mysqli_query($conn, $sql);

        if ($result === false) {
                echo 'Error: '.mysqli_error($conn);
        }


        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $name = $data[0]['name'];
        $author = $data[0]['author'];
    }

    else {
        echo '<h1>Nepodařilo se získat id knihy</h1>
        <p>Vraťte se na hlavní stránku a zkuste to znovu</p>';
        exit;
    }
?>


<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name?></title>

    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header class="container moreInfoHeader">
        <h1 class="bookName"><?php echo $name?></h1>
        <h2 class="author"><?php echo $author?></h2>
    </header>
    


    <main class="container moreInfoMain">
        <div class="description">
            <p id="bookInfo">Popis</p>
            <p class="bookInfoSource">Zdroj: <a id="bookSourceLink" href="" target="_blank">google books</a></p>
        </div>
        
        <img class="bookImg" id="thumbnail" src="" alt="Fotka knihy">

        <button class='btn reservationBtn' onclick="reserve()">Rezervovat</button>
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



        let url = "https://www.googleapis.com/books/v1/volumes?q=" + name /*+ '+inauthor:' + author*/;

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