<?php
 require('Signup.php'); 
 
 if (isset($_POST['login'])) {
    $login_field = $_POST['email_username']; // Assuming the form field name is 'email_username'
    
    $query = "SELECT * FROM user_register WHERE ";
    if (filter_var($login_field, FILTER_VALIDATE_EMAIL)) {
        $query .= "email='$login_field'";
    } else {
        $query .= "Username='$login_field'";
    }
    $result = mysqli_query($con, $query);
    
    if ($result && mysqli_num_rows($result) == 1) { // Check if any rows are returned
        $result_fetch = mysqli_fetch_assoc($result);
        
        if (password_verify($_POST['Password'], $result_fetch['Password'])) {
            echo'Login successfully!';
        } else {
            echo "
            <script>
            alert('Incorrect password');
            window.location.href='Loginn.php';
            </script>";
        }
    } else {
        echo "
        <script>
        alert('Username/Email not found');
        window.location.href='Loginn.php';
        </script>";
    }
}

 
    ?>