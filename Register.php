<?php require('Signup.php');?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regitser</title>
    <link rel="stylesheet" href="Style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>


<body>

    <div class="container">
        <h2>Registration Form</h2>
        <div class="form-container">
            <form method="POST" action="login.php" >
                    <div class="input-name">
                        <i class="fa fa-user"></i>
                        <input type="text" class="text-name" placeholder="Name" name="name">
                    </div>
            

                <div class="input-name">
                    <i class="fa fa-user"></i>
                    <input type="text" class="text-name" placeholder="Username" name="Username">
                </div>

                <div class="input-name" >
                    <i class="fa fa-envelope email"></i>
                    <input type="text"  placeholder="Email" class="text-name" name="email">
                </div>

                <div class="input-name">
                    <i class="fa fa-lock lock"></i>
                    <input type="password"  placeholder="Password" class="text-name" name="Password" id="Password" required>
       

                </div>  

                <div class="input-name">
                    <i class="fa fa-lock lock"></i>
                    <input type="password"  placeholder="Confirm Password" class="text-name" id="repwd" name="Confirm_Password" required>
                   
                </div>
                
                
                <div class="input-name">
                    <input type="submit"  class="button" value="Regitser" name="register">
                    
                </div>
                
            </form>
        </div>
        <div class="already_account">
            <h4>Already have an account?<a href="Loginn.php">Login</a></h4>
    
        </div>
    </div>
    
    
   
</body>
</html>