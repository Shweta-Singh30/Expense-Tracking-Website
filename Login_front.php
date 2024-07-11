<?php require('endPoint/Connection.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="x-icon" href="Assets/icon.png">
    <title>Login Page</title>
    <link rel="stylesheet" href="Assets/Style.css">
    <script src="Assets/tailwind.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   
</head>
<body>

    <!-- Navbar Goes Here -->
    <header class="w-full  h-auto">

        <nav class="bg-purple-950 w-full  h-14 flex justify-between items-center  lg:items-center  ">

            <div class="bg-clip-text text-transparent bg-gradient-to-r from-cyan-200 to-white   ml-2 font-serif font-extrabold  text-2xl">Expense Tracker</div>
        
            <ul class="md:flex hidden font-semibold ">
                <li class="mx-[10px] curser-pointer">Home</li>
                <li class="mx-[10px] curser-pointer">Contact</li>
                <li class="mx-[10px] curser-pointer">About Us</li>
            </ul>
       
            <div class="hidden md:block px-4 py-2 mr-2 bg-green-500 text-white rounded font-bold curser-pointer">Signup</div>

            <div class="md:hidden">
                <a class="text-4xl" href="#">&#8801;</a>
            </div>

        </nav>

    </header>
    <!-- Navbar ends Here -->

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
    </div>
    <footer></footer>
</body>
</html>
