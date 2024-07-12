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
<body class="bg-gradient-to-br from-cyan-100 to-cyan-200">

    <!-- Navbar Goes Here -->
    <header class="w-full  h-auto">

        <nav class="bg-purple-950 w-full  h-[60px] md:h-[45px] flex justify-between items-center  lg:items-center  ">

            <div class="bg-clip-text text-transparent bg-white   ml-2 font-serif font-extrabold  text-2xl">Expense Tracker</div>
        
            <ul class="md:flex hidden font-semibold ml-2 ">
                <li class="mx-[10px] curser-pointer text-white">Home</li>
                <li class="mx-[10px] curser-pointer text-white">Contact</li>
                <li class="mx-[10px] curser-pointer text-white">About Us</li>
            </ul>

            <div class="hidden my-auto text-[15px] font-serif md:flex mr-2">
                        <button>
                            <a href="Register.php" class="bg-rose-600 text-white px-[14px]  p-[5px] hover:text-[16px] hover:bg-pink-700 md:w-10 border-2 border-red-500 rounded-lg ">Signup</a>
                        </button>
            </div>
            

            <div class="md:hidden m-2">
                <a class="text-4xl" href="#">&#8801;</a>
            </div>

        </nav>

    </header>
    <!-- Navbar ends Here -->

    <div  class="grid place-content-center mx-auto h-screen md:w-[700px] ">
    <div class="container6 bg-white w-[400px] sm:w-[600px] md:w-[800px] p-8 border-2 border-white shadow-lg shadow-slate-500 border-t-fuchsia-900">
        <h2 class="text-[30px] sm:text-[35px] md:text-[40px] flex justify-center font-serif font-semibold ">Login form</h2>
        <form method="POST" action="endPoint/login.php">
            <div class="input-name border-slate-300 border-[1px] shadow-md">
                <i class="fa fa-user border-2 p-2"></i>
                <input type="text" class="text-name" placeholder="Email/Username" name="email_username">
            </div>

            <div class="input-name border-slate-300 border-[1px] shadow-md outline-none">
                <i class="fa fa-lock lock border-2 p-[8px]"></i>
                <input type="password" class="text-name outline-none" placeholder="Password" name="Password">
            </div>

            <div class="input-name bg-fuchsia-900 border-2 border-fuchsia-900 rounded-full flex items-center justify-center p-[6px] hover:bg-purple-900 hover:rounded-none hover:text-[21px]">
    <input type="submit" value="Login" class="text-white font-semibold text-[20px] hover:font-bold rounded-full cursor-pointer" name="login">
    <a href="addYourExpense.php" class="hidden"></a>
</div>

        </form>
        <div class="already_account">
            <h4 class="text-[15px] ">Have not an account?<a href="Register.php" class="font-semibold">Register</a></h4>
        </div>
    </div>
    </div>
    
    <footer></footer>
</body>
</html>
