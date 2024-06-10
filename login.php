<?php
require('Signup.php');

if (isset($_POST['register'])) {
    if ($_POST['Password'] !== $_POST['Confirm_Password']) {
        echo "<script>alert('Passwords do not match'); window.location.href='Register.php';</script>";
        exit();
    }
    
    $user_exist_query = "SELECT * FROM user_register WHERE Username='$_POST[Username]' OR email='$_POST[email]'";
    $result = mysqli_query($con, $user_exist_query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $result_fetch = mysqli_fetch_assoc($result);
            if ($result_fetch['Username'] == $_POST['Username']) {
                echo "<script>alert('$result_fetch[Username] - Username already taken'); window.location.href='Register.php';</script>";
            } else {
                echo "<script>alert('$result_fetch[Email] - E-Mail already registered'); window.location.href='Register.php';</script>";
            }
        } else {
            $Password = password_hash($_POST['Password'],PASSWORD_BCRYPT);
            $query = "INSERT INTO user_register (name, Username, email, Password) VALUES ('$_POST[name]', '$_POST[Username]', '$_POST[email]', '$Password')";

            if (mysqli_query($con, $query)) {
                echo "<script>alert('Registration Successful'); window.location.href='Register.php';</script>";
            } else {
                echo "<script>alert('Cannot Run Query'); window.location.href='Register.php';</script>";
            }
        }
    } else {
        echo "<script>alert('Cannot Run Query'); window.location.href='Register.php';</script>";
    }
}
?>
