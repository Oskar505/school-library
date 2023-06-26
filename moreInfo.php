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
</head>
<body>
    <h1 class="moreInfoH1"><?php echo $name?></h1>
    <h2><?php echo $author?></h2>
    

    <p id="bookInfo">Popis</p>
    <p class="bookInfoSource">Popis knihy z <a href="">google books</a></p>

    <button class='btn' onclick="reserve()">Rezervovat</button>


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

        fetch(url).then(response => {
            if (!response.ok) {
                throw new Error('Chyba při komunikaci s Google Books API: ' + response.status);
            }

            return response.json();
        }).then(data => {
            console.log(data);

            let description = data.items[0].volumeInfo.description;
            console.log(description)

            let bookInfoElement = document.getElementById('bookInfo')

            bookInfoElement.textContent = description;

        }).catch(error => {
            console.error('Chyba: ', error)
        })
    </script>

    <script src="indexScript.js"></script>
</body>
</html>