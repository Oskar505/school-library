<?php
    if (!isset($_SESSION)) {
        session_start();
    }

    /*
    if (!isset($_SESSION['loggedin'])) {
        header('Location: /userLogin.php');
        exit;
    }
    */

    require '/usr/local/bin/vendor/autoload.php';

    /*
    require_once __DIR__ . '/usr/local/bin/vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/usr/local/bin/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/usr/local/bin/vendor/phpmailer/phpmailer/src/SMTP.php';
    */


    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;


    




    class SendMail {
        public $mail;
        public $secrets;
        public $gmailAddress;
        public $firstName;
        public $debug;
        public $login;



        function __construct($login, bool $debug=false) {
            // get secrets
            require('/var/secrets.php');
            $gmailPassword = $secrets['gmail-password'];
            $sqlUser = $secrets['sql-user'];
            $sqlPassword = $secrets['sql-password'];
            $database = $secrets['sql-database'];

            $this->secrets = $secrets;

            //TODO: debug
            //$login = 'knihovna';

            //GET USER DATA
            //connection
            $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
        
            if (!$conn) {
                echo 'chyba pripojeni'.mysqli_connect_error();
            }

            $sql = "SELECT * FROM users WHERE login='$login'";
            $result = mysqli_query($conn, $sql);
        
            if ($result === false) {
                echo 'Error: '.mysqli_error($conn);
            }
        
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];

            print_r($data);

            $this->firstName = $data['firstName'];
            $this->login = $login;


            $this->gmailAddress = $login . '@gykovy.cz';


            //MAIL
            $this->mail = new PHPMailer(true);
        }



        function send() {
            $mail = $this->mail;

            $gmailPassword = $this->secrets['gmail-password'];

            echo $gmailPassword;



            try {
                // Server settings
                $mail->SMTPDebug = SMTP::DEBUG_SERVER; // for detailed debug output
                $mail->CharSet = 'UTF-8';
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
    
                $mail->Username = 'knihovna@gykovy.cz'; // library mail
                $mail->Password = $gmailPassword; // password
    
                // Sender and recipient settings
                $mail->setFrom('knihovna@gykovy.cz', 'Knihovna GYKOVY'); // from
                $mail->addAddress($this->gmailAddress, $this->firstName); // send to
                $mail->addReplyTo('knihovna@gykovy.cz', 'Knihovna GYKOVY'); // reply
    
                // Setting the email content
                $mail->IsHTML(true);
                $mail->Subject = $this->subject;
                $mail->Body = $this->message;
                $mail->AltBody = $this->altMessage;
                $mail->send();

                echo "Email message sent.";
            }
            
            catch (Exception $e) {
                echo "Error in sending email. Mailer Error: {$mail->ErrorInfo}";
            }
        }
        


        // LENT

        function bookLent($bookName, $returnDate) {
            $this->subject = "Kniha půjčena";
            $this->message = "
            <!DOCTYPE html>
            <html lang='cs'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Oznámení o půjčce knihy</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #EDEDE3;
                        text-align: left;
                    }
                    .container {
                        background-color: #EDEDE3;
                        color: #294a70;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        margin: 0 auto;
                    }
                    h1 {
                        color: #f4a024;
                        font-size: 28px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 20px;
                        line-height: 1.5;
                        color: #294a70;
                    }
                    .highlight {
                        color: #f4a024;
                        font-weight: bold;
                    }
            
                    .small {
                        color: gray;
                        font-size: 15px;
                    }
                </style>
            </head>
            <body>
                <div class=container>
                    <h1>Oznámení o úspěšném půjčení knihy</h1>
                    <p>
                        Dobrý den, <br>
                        úspěšně jste si vypůjčili knihu <span class='highlight'>$bookName</span> ze školní knihovny.<br>
                        Knihu je potřeba vrátit do <span class='highlight'>$returnDate</span> Budeme vás o tom informovat.<br>
                        Děkujeme za vypůjčení!
                    </p>
            
                    <br>
            
                    <p class=small>Pokud máte nějaké otázky, nebo jste si tuto knihu nepůjčili, kontaktujte nás.</p>
                </div>
            </body> 
            </html>
            ";

            $this->altMessage = "Oznámení o úspěšném půjčení knihy. Dobrý den, Úspěšně jste si vypůjčili knihu $bookName ze školní knihovny. Knihu je potřeba vrátit do $returnDate Budeme vás o tom informovat. Pokud máte nějaké otázky, nebo jste si tuto knihu nepůjčili, kontaktujte nás.";



            $this->send();


            // mail for admin
            $this->mail = new PHPMailer(true);

            $this->subject = "Kniha půjčena";
            $this->message = "
            <!DOCTYPE html>
            <html lang='cs'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Oznámení o půjčce knihy</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #EDEDE3;
                        text-align: left;
                    }
                    .container {
                        background-color: #EDEDE3;
                        color: #294a70;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        margin: 0 auto;
                    }
                    h1 {
                        color: #f4a024;
                        font-size: 28px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 20px;
                        line-height: 1.5;
                        color: #294a70;
                    }
                    .highlight {
                        color: #f4a024;
                        font-weight: bold;
                    }
            
                    .small {
                        color: gray;
                        font-size: 15px;
                    }
                </style>
            </head>
            <body>
                <div class=container>
                    <h1>Uživatel $this->login si půjčil knihu $bookName.</h1>
                    <p>
                        Má ji vrátit do $returnDate
                    </p>
                </div>
            </body> 
            </html>
            ";

            $this->altMessage = "Uživatel $this->login si půjčil knihu $bookName. Má ji vrátit do $returnDate";

            $this->gmailAddress = 'knihovna@gykovy.cz';
            $this->firstName = 'Admin';



            $this->send();
        }



        // RESERVED

        function bookReserved($bookName, $reservationExpiration, bool $available = true) {
            if ($available) {
                $message = "
                    Dobrý den, <br>
                    úspěšně jste si rezervovali knihu <span class='highlight'>$bookName</span> ve školní knihovně.<br>
                    Knihu si můžete vyzvednout do <span class='highlight'>$reservationExpiration</span><br>
                ";
            }

            else {
                $message = "
                    Dobrý den, <br>
                    úspěšně jste si rezervovali knihu <span class='highlight'>$bookName</span> ve školní knihovně.<br>
                    Až bude volná, budeme vás informovat.<br>
                ";
            }


            $this->subject = "Kniha rezervována";
            $this->message = "
            <!DOCTYPE html>
            <html lang='cs'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Oznámení o půjčce knihy</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #EDEDE3;                
                        text-align: left;
                    }
                    .container {
                        background-color: #EDEDE3;
                        color: #294a70;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        margin: 0 auto;
                    }
                    h1 {
                        color: #f4a024;
                        font-size: 28px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 20px;
                        line-height: 1.5;
                        color: #294a70;
                    }
                    .highlight {
                        color: #f4a024;
                        font-weight: bold;
                    }
            
                    .small {
                        color: gray;
                        font-size: 15px;
                    }
                </style>
            </head>
            <body>
                <div class=container>
                    <h1>Oznámení o úspěšné rezervaci knihy</h1>
                    <p>
                        $message
                    </p>
            
                    <br>
            
                    <p class=small>Pokud máte nějaké otázky, nebo jste si tuto knihu nerezervovali, kontaktujte nás.</p>
                </div>
            </body> 
            </html>
            ";
            $this->altMessage = "Oznámení o úspěšném rezervování knihy. Dobrý den, Úspěšně jste si zarezervovali knihu $bookName ve školní knihovně. Knihu si můžete vyzvednout do $reservationExpiration. Pokud máte nějaké otázky, nebo jste si tuto knihu nepůjčili, kontaktujte nás.";

            $this->send();



            // mail for admin
            $this->mail = new PHPMailer(true);

            $this->subject = "Kniha rezervována";
            $this->message = "
            <!DOCTYPE html>
            <html lang='cs'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Kniha rezervována</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #EDEDE3;
                        text-align: left;
                    }
                    .container {
                        background-color: #EDEDE3;
                        color: #294a70;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        margin: 0 auto;
                    }
                    h1 {
                        color: #f4a024;
                        font-size: 28px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 20px;
                        line-height: 1.5;
                        color: #294a70;
                    }
                    .highlight {
                        color: #f4a024;
                        font-weight: bold;
                    }
            
                    .small {
                        color: gray;
                        font-size: 15px;
                    }
                </style>
            </head>
            <body>
                <div class=container>
                    <h1>Uživatel $this->login si rezervoval knihu $bookName.</h1>
                    <p>
                    </p>
                </div>
            </body> 
            </html>
            ";

            $this->altMessage = "Uživatel $this->login si půjčil knihu $bookName.";

            $this->gmailAddress = 'knihovna@gykovy.cz';
            $this->firstName = 'Admin';
        }



        // RETURN REMINDER

        function returnReminder($bookName, $returnDate, bool $late) {
            if (!$late) {
                $message = "
                    Dobrý den, <br>
                    upozorňujeme vás, že knihu <span class='highlight'>$bookName</span> je potřeba vrátit do <span class='highlight'>$returnDate</span><br>
                    Pokud jste ještě nedočetli, můžete si knihu prodloužit.
                ";
            }

            else {
                $message = "
                    Dobrý den, <br>
                    upozorňujeme vás, že jste měli vrátit knihu <span class='highlight'>$bookName</span> do <span class='highlight'>$returnDate</span> <br>
                    Vraťte ji prosím co nejdříve.
                ";
            }

            $this->subject = "Upozornění o vrácení";
            $this->message = "
            <!DOCTYPE html>
            <html lang='cs'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>upozornění</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #EDEDE3;
                        text-align: left;
                    }
                    .container {
                        background-color: #EDEDE3;
                        color: #294a70;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        margin: 0 auto;
                    }
                    h1 {
                        color: #f4a024;
                        font-size: 28px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 20px;
                        line-height: 1.5;
                        color: #294a70;
                    }
                    .highlight {
                        color: #f4a024;
                        font-weight: bold;
                    }
            
                    .small {
                        color: gray;
                        font-size: 15px;
                    }
                </style>
            </head>
            <body>
                <div class=container>
                    <h1>Upozornění o vrácení</h1>
                    <p>
                        $message
                    </p>
            
                    <br>
            
                    <p class=small>Pokud máte nějaké otázky, nebo tuto knihu nemáte půjčenou, kontaktujte nás.</p>
                </div>
            </body> 
            </html>
            ";
            $this->altMessage = "Dobrý den, upozorňujeme vás, že knihu $bookName je potřeba vrátit do $returnDate Pokud jste ještě nedočetli, není problém si knihu prodloužit. Pokud máte nějaké otázky, nebo tuto knihu nemáte půjčenou, kontaktujte nás.";

            $this->send();




            // mail for admin, ONLY IF LATE
            if ($late) {
                $this->mail = new PHPMailer(true);

                $this->subject = "Kniha nebyla vrácena";
                $this->message = "
                <!DOCTYPE html>
                <html lang='cs'>
                <head>
                    <meta charset='UTF-8'>
                    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                    <title>Oznámení o půjčce knihy</title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #EDEDE3;
                            text-align: left;
                        }
                        .container {
                            background-color: #EDEDE3;
                            color: #294a70;
                            padding: 20px;
                            border-radius: 10px;
                            width: 80%;
                            margin: 0 auto;
                        }
                        h1 {
                            color: #f4a024;
                            font-size: 28px;
                        }
                        p {
                            margin: 10px 0;
                            font-size: 20px;
                            line-height: 1.5;
                            color: #294a70;
                        }
                        .highlight {
                            color: #f4a024;
                            font-weight: bold;
                        }
                
                        .small {
                            color: gray;
                            font-size: 15px;
                        }
                    </style>
                </head>
                <body>
                    <div class=container>
                        <h1>Uživatel $this->login nevrátil knihu $bookName.</h1>
                        <p>
                            Měl ji vrátit do $returnDate
                        </p>
                    </div>
                </body> 
                </html>
                ";
    
                $this->altMessage = "Uživatel $this->login nevrátil knihu $bookName. Měl ji vrátit do $returnDate";
    
                $this->gmailAddress = 'knihovna@gykovy.cz';
                $this->firstName = 'Admin';
    
    
                $this->send();
            }
            
        }




        // RESERVATION REMINDER

        function reservationReminder($bookName, $reservationExpiration, $availableReminder = false) {
            if ($availableReminder) {
                $message = "
                Dobrý den, <br>
                kniha <span class='highlight'>$bookName</span> byla právě vrácena. Můžete si ji vyzvednout do <span class='highlight'>$reservationExpiration</span>
                ";

                $this->subject = "Kniha $bookName je volná";
                $this->altMessage = "Dobrý den, kniha $bookName byla vrácena. Můžete si ji vyzvednout do $reservationExpiration Pokud máte nějaké otázky, nebo tuto knihu nemáte rezervovanou, kontaktujte nás.";
            }

            else {
                $message = "
                Dobrý den, <br>
                rezervace na knihu <span class='highlight'>$bookName</span> vám brzy končí. Můžete si ji vyzvednout do $reservationExpiration
                ";
                $this->subject = 'Upozornění o rezervaci';
                $this->altMessage = "Dobrý den, rezervace na knihu $bookName vám brzy končí. Můžete si ji vyzvednout do $reservationExpiration Pokud máte nějaké otázky, nebo tuto knihu nemáte rezervovanou, kontaktujte nás.";
            }

            
            $this->message = "
            <!DOCTYPE html>
            <html lang='cs'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>upozornění</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #EDEDE3;
                        text-align: left;
                    }
                    .container {
                        background-color: #EDEDE3;
                        color: #294a70;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        margin: 0 auto;
                    }
                    h1 {
                        color: #f4a024;
                        font-size: 28px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 20px;
                        line-height: 1.5;
                        color: #294a70;
                    }
                    .highlight {
                        color: #f4a024;
                        font-weight: bold;
                    }
            
                    .small {
                        color: gray;
                        font-size: 15px;
                    }
                </style>
            </head>
            <body>
                <div class=container>
                    <h1>Upozornění o rezervaci</h1>
                    <p>
                        $message
                    </p>
            
                    <br>
            
                    <p class=small>Pokud máte nějaké otázky, nebo tuto knihu nemáte rezervovanou, kontaktujte nás.</p>
                </div>
            </body> 
            </html>
            ";
            

            $this->send();
        }




        // CANCEL RESERVATION

        function reservationCanceled($bookName) {
            $message = "
                Dobrý den, <br>
                rezervace knihy <span class='highlight'>$bookName</span> byla zrušena.
            ";


            $this->subject = "Rezervace zrušena";
            $this->message = "
            <!DOCTYPE html>
            <html lang='cs'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Oznámení o půjčce knihy</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #EDEDE3;                
                        text-align: left;
                    }
                    .container {
                        background-color: #EDEDE3;
                        color: #294a70;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        margin: 0 auto;
                    }
                    h1 {
                        color: #f4a024;
                        font-size: 28px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 20px;
                        line-height: 1.5;
                        color: #294a70;
                    }
                    .highlight {
                        color: #f4a024;
                        font-weight: bold;
                    }
            
                    .small {
                        color: gray;
                        font-size: 15px;
                    }
                </style>
            </head>
            <body>
                <div class=container>
                    <h1>Oznámení o zrušení rezervace knihy</h1>
                    <p>
                        $message
                    </p>
            
                    <br>
            
                    <p class=small>Pokud máte nějaké otázky, kontaktujte nás.</p>
                </div>
            </body> 
            </html>
            ";
            $this->altMessage = "Rezervace knihy $bookName byla zrušena. Pokud máte nějaké otázky, kontaktujte nás.";

            $this->send();


            // send mail to admin
            $this->mail = new PHPMailer(true);

            $this->subject = "Rezervace zrušena";
            $this->message = "
            <!DOCTYPE html>
            <html lang='cs'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Zrušení rezervace</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #EDEDE3;
                        text-align: left;
                    }
                    .container {
                        background-color: #EDEDE3;
                        color: #294a70;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        margin: 0 auto;
                    }
                    h1 {
                        color: #f4a024;
                        font-size: 28px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 20px;
                        line-height: 1.5;
                        color: #294a70;
                    }
                    .highlight {
                        color: #f4a024;
                        font-weight: bold;
                    }
            
                    .small {
                        color: gray;
                        font-size: 15px;
                    }
                </style>
            </head>
            <body>
                <div class=container>
                    <h1>Uživatel $this->login zrušil rezervaci knihy $bookName.</h1>
                    <p>
                    </p>
                </div>
            </body> 
            </html>
            ";

            $this->altMessage = "Uživatel $this->login zrušil rezervaci knihy $bookName.";

            $this->gmailAddress = 'knihovna@gykovy.cz';
            $this->firstName = 'Admin';



            $this->send();
        }


        // CRON JOB OUTPUT

        function cronJobOutput($script, $output=['Vše proběhlo vpořádku.']) {
            // process output
            $outputMsg = '';

            if (empty($output)) {
                $outputMsg = 'Vše proběhlo vpořádku.';
            }

            else {
                foreach ($output as $error) {
                    $outputMsg = $outputMsg . '<br>' . $error;
                }
            }
            


            $this->subject = "Skript $script spuštěn.";
            $this->message = "
            <!DOCTYPE html>
            <html lang='cs'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Skript $script byl spuštěn.</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #EDEDE3;
                        text-align: left;
                    }
                    .container {
                        background-color: #EDEDE3;
                        color: #294a70;
                        padding: 20px;
                        border-radius: 10px;
                        width: 80%;
                        margin: 0 auto;
                    }
                    h1 {
                        color: #f4a024;
                        font-size: 28px;
                    }
                    p {
                        margin: 10px 0;
                        font-size: 20px;
                        line-height: 1.5;
                        color: #294a70;
                    }
                    .highlight {
                        color: #f4a024;
                        font-weight: bold;
                    }
            
                    .small {
                        color: gray;
                        font-size: 15px;
                    }
                </style>
            </head>
            <body>
                <div class=container>
                    <h1>Spuštění skriptu $script</h1>
                    <p>
                        Dobrý den, <br>
                        právě se spustil skript $script. <br>
                        <strong>Output:</strong> <br>
                        $outputMsg
                    </p>
                </div>
            </body> 
            </html>
            ";

            $this->altMessage = "Script $script byl spuštěn. Output: $outputMsg";



            $this->send();
        }
    }




    

    /*
    $mail = new SendMail('x6utvrdoc');
    $mail->reservationReminder('Oresteia', '16. 12.', true);
    $mail->reservationReminder('Oresteia', '16. 12.');
    */
?>