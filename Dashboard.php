<?php
  session_start();
  require('endPoint/Connection.php');
  
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="x-icon" href="Assets/icon.png">
    <title>Dashboard</title>
    <script src="Assets/tailwind.js"></script>
    <link rel="stylesheet" href="Assets/Style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    
    
    
</head>

<body class="bg-[url('Assets/image/eIMG.jpg')] object-fill bg-cover bg-no-repeat  h-screen "  >
   
    
    <nav class="flex justify-end" >
        
        <ul class="inline-flex">
            <li class="m-3 text-white font-serif">
                <a href="Home.html">Contact Us</a>
            </li>
            <li class="m-3 text-white font-serif">
                <button>
                    <a href="Home.html">Logout</a>
                </button>
            </li>
        </ul>
       
    </nav>
    

   
    
    
    
    <div class="dImg"> 

        <div id="welcome-container">
            <div id="welcome-message" class="bg-clip-text text-transparent bg-gradient-to-r from-rose-900 to-green-900 text-5xl  pl-[90px] mt-28  font-bold mg:mt-28"></div>

            <div class="text-[30px] font-extrabold mt-4 md:text-[40px] md:font-extrabold md:mt-4 lg:text-[40px] lg:font-extrabold lg:mt-4">
                <span class="bg-clip-text text-transparent  bg-gradient-to-r from-red-700 to-violet-500  pl-[90px] mb-1.5 hover:bg-gradient-to-tl from-red-700 to-violet-500">
                    Track your spending with ease 
                </span>
            </div>

           
            <h2 class="bg-clip-text text-transparent bg-gradient-to-r from-rose-800 to-green-800  text-2xl pl-[90px] mt-2 font-serif font-extrabold" > (Record your expense now)</h2>
        </div>

        <div class="pl-[100px] mt-6">

            <div class="border-2 border-purple-400 h-11 w-56 bg-purple-300 flex items-center  mr-16 shadow-lg">

                <div class="grid place-content-center flex-grow font-bold  bg-clip-text text-transparent  bg-gradient-to-r from-black to-red-700 hover:bg-gradient-to-tl from-black-700 to-red-500">Add Your Expenses</div>

                <div class="w-12 h-10 bg-gradient-to-r from-rose-600 to bg-purple-500 ml-auto flex items-center justify-center  hover:w-14 ">
                   <a <i class="fa solid fa-chevron-right text-3xl text-slate-200 hover:text-[38px]" href="addYourExpense.php"></i></a>
                </div>
                
            </div> 
            
            <div class="border-2 border-purple-400 h-11 w-56 bg-purple-300 flex items-center  shadow-2xl mt-6">
                
                <div class="grid place-content-center flex-grow font-bold  bg-clip-text text-transparent  bg-gradient-to-r from-black to-red-700 hover:bg-gradient-to-tl from-black-700 to-red-500">Expense History</div>
                
                <div class="w-12 h-10 bg-gradient-to-r from-rose-600 to bg-purple-500 ml-auto flex items-center justify-center hover:w-14  ">
                   <a <i class="fa solid fa-chevron-right text-3xl text-slate-200 hover:text-[38px]" href="expenseHistory.php"></i></a>
                </div>
                
            </div>  
      
        </div>
   
</div>
    
<script>
    
    const userName = "<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; ?>";
    document.getElementById("welcome-message").innerText = `Hello, ${userName}!`;
</script>
    
</body>

</html>
