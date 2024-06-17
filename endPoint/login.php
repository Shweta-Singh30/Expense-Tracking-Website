<?php
require('Connection.php');

if (isset($_POST['login'])) {
    $login_field = $_POST['email_username']; 

    if (!filter_var($login_field, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-zA-Z0-9_]+$/', $login_field)) {
      
        echo "<script>alert('Invalid input.'); window.location.href = '../login_front.php';</script>";
        exit();
    }

    $query = "SELECT Username, email, Password FROM user_register WHERE ";
    if (filter_var($login_field, FILTER_VALIDATE_EMAIL)) {
        $query .= "email=?";
    } else {
        $query .= "Username=?";
    }

    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "s", $login_field);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) == 1) { 
        $user = mysqli_fetch_assoc($result);

        if (password_verify($_POST['Password'], $user['Password'])) {

            session_start();

  
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['email'] = $user['email'];


            echo "<script>window.location.href = '../Dashboard.html';</script>";
            exit();
        } else {
            echo "<script>alert('Incorrect password'); window.location.href = '../login_front.php';</script>";
        }
    } else {
        echo "<script>alert('Username/Email not found'); window.location.href = '../login_front.php';</script>";
    }


    mysqli_stmt_close($stmt);
}


mysqli_close($con);
?>
