<?php require('endPoint/Connection.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
                <li class="ulLink"><button id="Logout"><a href="Login_front.php" class="Logout">Login</a></button></li>
            </ul>
        </nav>
    </header>
    <div class="container">
        <h2>Registration Form</h2>
        <div class="form-container">
            <form method="POST" action="endPoint/Signup.php">
                <div class="input-name">
                    <i class="fa fa-user"></i>
                    <input type="text" class="text-name" placeholder="Name" name="name">
                </div>
                <div class="input-name">
                    <i class="fa fa-user"></i>
                    <input type="text" class="text-name" placeholder="Username" name="Username">
                </div>
                <div class="input-name">
                    <i class="fa fa-envelope email"></i>
                    <input type="text" placeholder="Email" class="text-name" name="email">
                </div>
                <div class="input-name">
                    <i class="fa fa-lock lock"></i>
                    <input type="password" placeholder="Password" class="text-name" name="Password" id="Password" required>
                </div>
                <div class="input-name">
                    <i class="fa fa-lock lock"></i>
                    <input type="password" placeholder="Confirm Password" class="text-name" id="repwd" name="Confirm_Password" required>
                </div>
                <div class="input-name">
                    <i class="fa fa-phone" aria-hidden="true"></i>
                    <input type="phone" placeholder="Contact Number" class="text-name" id="Contact_Number" name="Contact_Number" required>
                </div>
                <div class="input-name">
                    <input type="submit" class="button" value="Register" name="register">
                </div>
            </form>
        </div>
        <div class="already_account">
            <h4>Already have an account? <a href="Login_front.php">Login</a></h4>
        </div>
    </div>
    <footer></footer>
</body>
</html>
