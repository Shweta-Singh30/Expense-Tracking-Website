<?php
session_start();
require('endPoint/Connection.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: Home.html');
    exit();
}

$username = $_SESSION['username'];

// Get the next sequence number
$query = "SELECT MAX(`S.N.`) as max_sn FROM addexpense WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$next_sn = $row['max_sn'] + 1;
$stmt->close();

// Add expense
if (isset($_POST['add'])) {
    $expenseName = $_POST['expenseName'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $createdAt = $_POST['createdAt'];

    $query = "INSERT INTO addexpense (`S.N.`, expenseName, price, description, date, id) VALUES (?, ?, ?, ?, ?,?)";
    $stmt = $con->prepare($query);
    $stmt->bind_param("isdsss", $next_sn, $expenseName, $price, $description, $createdAt, $username);

    if ($stmt->execute()) {
        header('Location: addYourExpense.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Update expense
if (isset($_POST['update'])) {
    $expenseName = $_POST['expenseName'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $createdAt = $_POST['createdAt'];
    $edit_id = $_POST['edit_id'];

    $query = "UPDATE addexpense SET expenseName = ?, price = ?, description = ?, date = ? WHERE `S.N.` = ? AND id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("sdssis", $expenseName, $price, $description, $createdAt, $edit_id, $username);

    if ($stmt->execute()) {
        header('Location: addYourExpense.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Delete expense
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    $query = "DELETE FROM addexpense WHERE `S.N.` = ? AND id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $delete_id, $username);

    if ($stmt->execute()) {
        header('Location: addYourExpense.php');
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch expense to edit
$expense_to_edit = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];

    $query = "SELECT * FROM addexpense WHERE `S.N.` = ? AND id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("is", $edit_id, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $expense_to_edit = $result->fetch_assoc();
    $stmt->close();
}

// Fetch expenses
$query = "SELECT * FROM addexpense WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="x-icon" href="Assets/icon.png">
    <title>Add Expense</title>
    <link rel="stylesheet" href="Assets/Style.css">
</head>
<body>
    
<header>
    <nav class="navbar">
        <ul>
            <li class="ulLink"><a href="Dashboard.html" class="Home">Home</a></li>
            <li class="ulLink"><a href="#" id="Contact">Contact</a></li>
            <li class="ulLink"><a href="#" id="aboutUs">About Us</a></li>
            <li class="ulLink"><button id="Logout"><a href="Home.html" class="Logout">Logout</a></button></li>
        </ul>
    </nav>
</header>

<center>
    <form action="addYourExpense.php" method="POST" class="eForm">
        <div>
            <div class="add">
                <label for="expenseName">Expense Name</label>
                <input type="text" id="expenseName" name="expenseName" value="<?php echo isset($expense_to_edit) ? $expense_to_edit['expenseName'] : ''; ?>" required><br>
            </div>
            <div class="add1">
                <label for="price" class="pr">Price</label>
                <input type="number" id="price" name="price" value="<?php echo isset($expense_to_edit) ? $expense_to_edit['price'] : ''; ?>" required><br>
            </div>
            <div class="add2">
                <label for="description" class="drs">Description</label>
                <textarea id="description" name="description"><?php echo isset($expense_to_edit) ? $expense_to_edit['description'] : ''; ?></textarea><br>
            </div>
            <div class="add3">
                <label for="createdAt" class="date">Date</label>
                <input type="date" id="createdAt" name="createdAt" value="<?php echo isset($expense_to_edit) ? $expense_to_edit['date'] : date('Y-m-d'); ?>" required><br>
            </div>
        </div>
        <div>
            <?php if (isset($expense_to_edit)): ?>
                <input type="hidden" name="edit_id" value="<?php echo $expense_to_edit['S.N.']; ?>">
                <input type="submit" class="addButton" value="Save" name="update">
            <?php else: ?>
                <input type="submit" class="addButton" value="Add" name="add">
            <?php endif; ?>
        </div>
    </form>
</center>

<center>
    <div class="createTable" width="100%">
        <table>
            <thead>
                <tr>
                    <td class="tHead"><h3>S.N.</h3></td>
                    <td class="tHead"><h3>Name</h3></td>
                    <td class="tHead"><h3>Price</h3></td>
                    <td class="tHead"><h3>Description</h3></td>
                    <td class="tHead"><h3>Created At</h3></td>
                    <td class="tHead" width="25%"><h3>Action</h3></td>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo $expense['S.N.']; ?></td>
                        <td><?php echo $expense['expenseName']; ?></td>
                        <td><?php echo $expense['price']; ?></td>
                        <td><?php echo $expense['description']; ?></td>
                        <td><?php echo $expense['date']; ?></td>
                        <td>
                            <a href="addYourExpense.php?edit=<?php echo $expense['S.N.']; ?>">Edit</a>
                            <a href="addYourExpense.php?delete=<?php echo $expense['S.N.']; ?>" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (empty($expenses)) : ?>
            <p style="text-align: center;">No expenses found!</p>
        <?php endif; ?>
    </div>
</center>

</body>
</html>
