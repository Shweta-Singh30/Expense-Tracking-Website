<?php
require('endPoint/Connection.php');

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_sql = "DELETE FROM addexpense WHERE `S.N.` = ?";
    $stmt = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt, 'i', $delete_id);
    mysqli_stmt_execute($stmt); 
    header("Location: addYourExpense.php");
    exit;
}

$expense_to_edit = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $edit_sql = "SELECT * FROM addexpense WHERE `S.N.` = ?";
    $stmt = mysqli_prepare($con, $edit_sql);
    mysqli_stmt_bind_param($stmt, 'i', $edit_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $expense_to_edit = mysqli_fetch_assoc($result);
}

$sql = "SELECT * FROM addexpense ORDER BY `S.N.` ASC"; 
$expenses = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
    <link rel="stylesheet" href="Assets/Style.css">
</head>
<body>
    
    <header>
        <nav class="navbar">
            <ul>
                <li class="ulLink"><a href="Home.html" class="Home">Home</a></li>
                <li class="ulLink"><a href="#" id="Contact">Contact</a></li>
                <li class="ulLink"><a href="#" id="aboutUs">About Us</a></li>
                <li class="ulLink"><button id="Logout"><a href="Home.html" class="Logout">Logout</a></button></li>
            </ul>
        </nav>
    </header>

    <center>
        <form action="endPoint/addExpense.php" method="POST" class="eForm">
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
                    <tr >
                        <td class="tHead"><h3>S.N.</h3></td>
                        <td class="tHead"><h3>Name</h3></td>
                        <td class="tHead"><h3>Price</h3></td>
                        <td class="tHead"><h3>Description</h3></td>
                        <td class="tHead" width="25%"><h3>Action</h3></td>
                    </tr>
                </thead>
                <tbody id="expenseTable">
                    <?php if (mysqli_num_rows($expenses) > 0) :?>
                        <?php while ($expense = mysqli_fetch_assoc($expenses)) : ?>
                            <tr>
                                <td><?php echo $expense['S.N.']; ?></td>
                                <td><?php echo $expense['expenseName']; ?></td>
                                <td><?php echo $expense['price']; ?></td>
                                <td><?php echo $expense['description']; ?></td>
                                <td>
                                    <button class="edit"><a class="edit" href="addYourExpense.php?edit_id=<?php echo $expense['S.N.']; ?>">Edit</a></button>
                                   <button class="del"> <a class="del" href="addYourExpense.php?delete_id=<?php echo $expense['S.N.']; ?>" onclick="return confirm('Are you sure you want to delete this expense?')">Delete</a></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5" style="text-align: center">No expenses found!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </center>
</body>
</html>
