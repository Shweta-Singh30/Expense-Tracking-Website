<?php require('endPoint/Connection.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="Assets/Style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
    <header>
    <nav class="navbar">
        <ul>
            <li class="ulLink"><a href="Home.html" class="Home">Home</a></li>
            <li class="ulLink"><a href="#" id="Contact">Contact</a></li>
            <li class="ulLink"><a href="#" id="aboutUs">About Us</a></li>
            <li class="ulLink"><button id="Logout"><a href="Register.php" class="Logout">Signup</a></button></li>
        </ul>
    </nav>
    </header>
    <div class="container1">
        <h2>Login form</h2>
        <form method="POST" action="endPoint/login.php">
            <div class="input-name">
                <i class="fa fa-user"></i>
                <input type="text" class="text-name" placeholder="Email/Username" name="email_username">
            </div>

            <div class="input-name">
                <i class="fa fa-lock lock"></i>
                <input type="password" class="text-name" placeholder="Password" name="Password">
            </div>

            <div class="input-name">
                <input type="submit" value="Login" class="button" name="login">
                <a href="addYourExpense.php"></a>
            </div>
        </form>
        <div class="already_account">
            <h4>Have not an account?<a href="Register.php">Register</a></h4>
        </div>
    </div>
    <footer></footer>
</body>
</html>
