<?php

$con=mysqli_connect("localhost","root","","expense tracking");

if(mysqli_connect_error())
{
    echo"<script>alert('cannot connect to the databse');
    window.location.href='Register.php';
    </script>";
   
    exit();
}

?>