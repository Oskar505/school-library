<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení</title>
</head>
<body>
    <h1>Přihlaste se</h1>

    <form action="authenticateUser" method="post">
        <label for="username">
            <i class="fas fa-user"></i>
        </label>
        <input type="text" name="username" placeholder="Email" id="username" required>
        <label for="password">
            <i class="fas fa-lock"></i>
        </label>
        <input type="password" name="password" placeholder="Heslo" id="password" required>
        <input type="submit" value="Přihlásit se">
    </form>

    <p>Přihlaste se pomocí školního emailu a hesla k bakalářům</p>
</body>
</html>