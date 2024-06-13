<nav class="glass container" id="nav">
    <div class="navContent">
        <a href="/index.php" title="Domů"><h2>Knihovna GYKOVY</h2></a>

        <div class="navUserOptions">
            <a href="/myBooks.php" class="myBooksLink" id="myBooksIcon" title="Moje knihy">
                <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#FFFFFF">
                    <path d="M220-80q-24 0-42-18t-18-42v-680q0-24 18-42t42-18h520q24 0 42 18t18 42v680q0 24-18 42t-42 18H220Zm0-60h520v-680h-60v266l-97-56-97 56v-266H220v680Zm0 0v-680 680Zm266-414 97-56 97 56-97-56-97 56Z"/>
                </svg>
            </a>

            <div class="account" title="Účet">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40px" viewBox="0 -960 960 960" fill="#FFFFFF" class="accountCircle" id="accountCircle">
                        <path d="M222-255q63-44 125-67.5T480-346q71 0 133.5 23.5T739-255q44-54 62.5-109T820-480q0-145-97.5-242.5T480-820q-145 0-242.5 97.5T140-480q0 61 19 116t63 109Zm257.81-195q-57.81 0-97.31-39.69-39.5-39.68-39.5-97.5 0-57.81 39.69-97.31 39.68-39.5 97.5-39.5 57.81 0 97.31 39.69 39.5 39.68 39.5 97.5 0 57.81-39.69 97.31-39.68 39.5-97.5 39.5Zm.66 370Q398-80 325-111.5t-127.5-86q-54.5-54.5-86-127.27Q80-397.53 80-480.27 80-563 111.5-635.5q31.5-72.5 86-127t127.27-86q72.76-31.5 155.5-31.5 82.73 0 155.23 31.5 72.5 31.5 127 86t86 127.03q31.5 72.53 31.5 155T848.5-325q-31.5 73-86 127.5t-127.03 86Q562.94-80 480.47-80Zm-.47-60q55 0 107.5-16T691-212q-51-36-104-55t-107-19q-54 0-107 19t-104 55q51 40 103.5 56T480-140Zm0-370q34 0 55.5-21.5T557-587q0-34-21.5-55.5T480-664q-34 0-55.5 21.5T403-587q0 34 21.5 55.5T480-510Zm0-77Zm0 374Z"/>
                    </svg>

                <?php
                    session_start();

                    if (isset($_SESSION['userLoggedIn']) || isset($_SESSION['loggedin'])) {
                        $firstName = $_SESSION['firstName'];

                        $dropdown = "
                            <p class='dropdownOption' title='Jméno'>$firstName</p>
                            <hr class='dropdownDivider'>
                            <a class='dropdownOption' href='admin/logout.php' title=''>Odhlásit se</a>
                        ";
                    }
                    

                    else {
                        $dropdown = "
                            <a class='dropdownOption' href='/login.php' title=''>Přihlásit se</a>
                        ";
                    }
                ?>

                <div class="accountDropdown glass" id="accountDropdown">
                    <?php echo $dropdown ?>
                </div>
            </div>
        </div>
    </div>
</nav>



<script src="/scripts/accountDropdown.js"></script>