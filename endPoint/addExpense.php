<?php
require("Connection.php");


$expenseName = '';
$price = '';
$description = '';
$createdAt = date('Y-m-d');
$id = 1; 


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
       
        $expenseName = $_POST['expenseName'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $createdAt = $_POST['createdAt'];

        $sql = "INSERT INTO addexpense (expenseName, price, description, createdAt, id) 
                VALUES ('$expenseName', '$price', '$description', '$createdAt', '$id')";

        if ($conn->query($sql) === TRUE) {
            header("Location: addExpense.php"); 
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['update'])) {
      
        $edit_id = $_POST['edit_id'];
        $expenseName = $_POST['expenseName'];
        $price = $_POST['price'];
        $description = $_POST['description'];
        $createdAt = $_POST['createdAt'];

        $sql = "UPDATE addexpense 
                SET expenseName='$expenseName', price='$price', description='$description', createdAt='$createdAt' 
                WHERE SN='$edit_id'";

        if ($conn->query($sql) === TRUE) {
            header("Location: addExpense.php"); 
            exit();
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
}


if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $sql = "DELETE FROM addexpense WHERE SN='$delete_id'";

    if ($conn->query($sql) === TRUE) {
        header("Location: addExpense.php"); 
        exit();
    } else {
        echo "Error deleting record: " . $con->error;
    }
}


$sql = "SELECT * FROM addexpense WHERE id='$id'";
$result = $conn->query($sql);

$expenses = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
}

$conn->close();
?>
