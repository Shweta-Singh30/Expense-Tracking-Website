<?php
require('Connection.php');

if (isset($_POST['register'])) {
  if ($_POST['Password'] !== $_POST['Confirm_Password']) {
    echo "<script>alert('Passwords do not match. Please try again.'); window.location.href='../Register.php';</script>";
    exit();
  }

  if (strlen($_POST['Password']) < 7) {
    echo "<script>alert('Password must be at least 7 characters long.'); window.location.href='../Register.php';</script>";
    exit();
  }

  if (!preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^\da-zA-Z]).{7,}$/', $_POST['Password'])) {
    echo "<script>alert('Password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.'); window.location.href='../Register.php';</script>";
    exit();
  }

  
  if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Invalid email format. Please enter a valid email address.'); window.location.href='../Register.php';</script>";
    exit();
  }


  if (!preg_match('/^\d{10}$/', $_POST['Contact_Number'])) {
    echo "<script>alert('Invalid contact number format. Please enter a valid 10-digit contact number.'); window.location.href='../Register.php';</script>";
    exit();
  }

  
  $username = htmlspecialchars($_POST['Username']);
  $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);


  $stmt = mysqli_prepare($con, "SELECT * FROM user_register WHERE `Username` = ? OR `email` = ?");
  mysqli_stmt_bind_param($stmt, "ss", $username, $email);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  
  $existing_user = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt); 

  if ($existing_user) {
    $existing_field = $existing_user['Username'] == $username ? 'Username' : 'Email';
    echo "<script>alert('$existing_field already taken.'); window.location.href='../Register.php';</script>";
    exit();
  }

  $Password = password_hash($_POST['Password'], PASSWORD_BCRYPT);
  $stmt = mysqli_prepare($con, "INSERT INTO user_register (`name`, `Username`, `email`, `Password`, `Contact Number`) VALUES (?, ?, ?, ?, ?)");
  mysqli_stmt_bind_param($stmt, "sssss", $_POST['name'], $username, $email, $Password, $_POST['Contact_Number']);

  if (mysqli_stmt_execute($stmt)) {
    echo "<script>alert('Registration Successful'); window.location.href='../Dashboard.html';</script>";
  } else {
    error_log("Registration failed: " . mysqli_stmt_error($stmt));
    echo "<script>alert('Registration failed. Please try again.'); window.location.href='../Register.php';</script>";
  }
  mysqli_stmt_close($stmt);
}
?>
