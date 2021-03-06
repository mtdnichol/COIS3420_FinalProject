<?php
require "./includes/library.php";

$errors = [];

if (isset($_POST['submit'])) {
    $valid = true;

    /* Process log-in request */
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_check = $_POST['password-check'];

    /* Connect to DB */
    $pdo = connectDB();

    /* Check the database for occurrences of $username */
    $query = "SELECT id, username FROM `bucket_users` WHERE username = ?";
    $statement = $pdo->prepare($query);
    $statement->execute([ $username ]);
    $results = $statement->fetch();

    //Second implementation, would display all current errors
    if (empty($results)) { //Database contains a user registered with the name
        array_push($errors, "Invalid username");
        $valid = false;
    }

    if ($password != $password_check) { //Checks if the passwords entered match
        array_push($errors, "Passwords Don't Match.");
        $valid = false;
    }

    // if valid up to this point, continue on
    if ($valid) {
        // hashing the password
        $options = ['cost' => 12];
        $password = password_hash($password, PASSWORD_DEFAULT, $options);

        // resetting the password in the db
        $query = "UPDATE `bucket_users` SET `password`=? WHERE username=?";
        $statement = $pdo->prepare($query);
        $statement->execute([ $password, $username ]);

        // setting session variables and logging them in
        $_SESSION['username'] = $username;
        $_SESSION['userID'] = $results['id'];

        // redirecting back to profile
        header("Location: Profile");
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="css/MainStyle.css">
    <link href="https://fonts.googleapis.com/css?family=Fredoka+One|Lato:300,400,700|Roboto:300,400,700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/1c8ee6a0f5.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/passtrength.css">
    <script
            src="https://code.jquery.com/jquery-3.5.0.min.js"
            integrity="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ="
            crossorigin="anonymous"></script>
</head>
<body>
    <div class="main-box">
        <h1>Account Recovery</h1>
        <div class="login-box">
            <form id="main-form" action="<?= $_SERVER['PHP_SELF'] ?>" method="POST"> <!-- Form where the user enters their username, and a new password -->
                <div class="icon-label">
                    <label for="username"><i class="fas fa-user"></i></i></label>
                    <input id="username" name="username" type="text" placeholder="Username"> <!-- User enters their username -->
                </div>

                <div class="icon-label">
                    <label for="username"><i class="fas fa-lock"></i></i></label>
                    <input id="password" name="password" type="password" placeholder="New Password" class="no-border no-margin"> <!-- User enters their password -->
                </div>

                <div class="icon-label">
                    <label for="username"><i class="fas fa-lock padding-right"></i></i></label> <!-- Re enter password -->
                    <input id="password-check" name="password-check" type="password" placeholder="Retype New Password" class="no-margin">
                </div>

                <button id="submit" name="submit" class="centered">Submit</button>
                <!-- print off any errors-->
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </form>
        </div>
    </div>
    <script type="text/javascript" src="scripts/jquery.passtrength.min.js"></script> <!-- This script is used to implement the password strength plugin on the users input -->
    <script>
        $('#password').passtrength({
            passwordToggle:false,
        });
    </script>
</body>
</html>