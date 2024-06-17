<?php
require('Connection.php');

if (isset($_POST['add'])) {
    $expenseName = $_POST['expenseName'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $sql = "INSERT INTO addexpense (expenseName, price, description) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'sis', $expenseName, $price, $description);
    mysqli_stmt_execute($stmt);
} elseif (isset($_POST['update'])) {
    $edit_id = $_POST['edit_id'];
    $expenseName = $_POST['expenseName'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $sql = "UPDATE addexpense SET expenseName = ?, price = ?, description = ? WHERE `S.N.` = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'sisi', $expenseName, $price, $description, $edit_id);
    mysqli_stmt_execute($stmt);
}

header("Location: ../addYourExpense.php");
exit;
