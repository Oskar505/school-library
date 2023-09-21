<?php
    session_start();

    // get error
    $error = isset($_SESSION["error"]) ? $_SESSION["error"] : "Chyba";
    $errorMessage = isset($_SESSION["errorMessage"]) ? $_SESSION["errorMessage"] : "Bohužel došlo k chybě. Zkuste to znovu později.";
    $link = isset($_SESSION["link"]) ? $_SESSION["link"] : "/index.php";
    $warning = isset($_SESSION["warning"]) ? $_SESSION["warning"] : false;


    $warning = $warning ? 'Varování' : 'Chyba';


    // delete error
    unset($_SESSION["error"]);
    unset($_SESSION["errorMessage"]);
    unset($_SESSION["link"]);
    unset($_SESSION["warning"]);
?>


<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $warning ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body class="errorBody">
    <div class="error-container">
        <div class="error-message">
            <h1><?php echo $error ?></h1>
            <p><?php echo $errorMessage ?></p>
            <a href=<?php echo $link ?>>Zpět</a>
        </div>
    </div>
</body>
</html>