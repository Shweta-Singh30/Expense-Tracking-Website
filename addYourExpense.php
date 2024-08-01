<?php
session_start();
require('endPoint/Connection.php');


if (!isset($_SESSION['username'])) {
    header('Location: Home.html');
    exit();
}

$username = $_SESSION['username'];


$query = "SELECT MAX(`S.N.`) as max_sn FROM addexpense WHERE id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$next_sn = $row['max_sn'] + 1;
$stmt->close();


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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-300">
<header>
    <nav class="bg-gray-800 text-white w-full">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center">
                <img src="Assets/icon.png" alt="ExpenseTracker Logo" class="h-10 w-10 mr-2">
                <h1 class="text-2xl font-bold">ExpenseTracker</h1>
            </div>
            <div class="flex space-x-4">
                <a href="Dashboard.php" class="hover:text-gray-400">Home</a>
                <a href="expenseHistory.php" class="hover:text-gray-400">ExpenseHistory</a>
                <a href="profile.php" class="hover:text-gray-400">Profile</a>
                <a href="Home.html" class="hover:text-gray-400">Logout</a>
            </div>
        </div>
    </nav>
</header>

<main class="container mx-auto mt-10 px-4">
    <div class=" rounded-lg shadow-lg shadow-slate-300 bg-gray-400 border-2 border-gray-500">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Add or Edit Expense</h2>
            <form action="addYourExpense.php" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="expenseName" class="block text-sm font-medium text-gray-700">Expense Name</label>
                        <input type="text" id="expenseName" name="expenseName" value="<?php echo isset($expense_to_edit) ? $expense_to_edit['expenseName'] : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" id="price" name="price" value="<?php echo isset($expense_to_edit) ? $expense_to_edit['price'] : ''; ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo isset($expense_to_edit) ? $expense_to_edit['description'] : ''; ?></textarea>
                    </div>
                    <div>
                        <label for="createdAt" class="block text-sm font-medium text-gray-700">Date</label>
                        <input type="date" id="createdAt" name="createdAt" value="<?php echo isset($expense_to_edit) ? $expense_to_edit['date'] : date('Y-m-d'); ?>" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                </div>
                <div class="mt-6">
                    <?php if (isset($expense_to_edit)): ?>
                        <input type="hidden" name="edit_id" value="<?php echo $expense_to_edit['S.N.']; ?>">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-md shadow-md hover:bg-blue-700 " name="update">Save</button>
                    <?php else: ?>
                        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-md shadow-md hover:bg-green-700 " name="add">Add</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- <div class="mt-8 bg-white border-2 border-gray-500 rounded-lg shadow-lg mb-4 ">
        <div class="p-6">
            <h2 class="text-2xl font-bold mb-6 grid place-content-center font-serif font-bold">Expenses List</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">S.N.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($expenses as $index => $expense): ?>
                            <tr class="<?php echo $index % 2 === 0 ? 'bg-gray-50' : 'bg-white'; ?>">
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $expense['S.N.']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $expense['expenseName']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $expense['price']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $expense['description']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo $expense['date']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <a href="addYourExpense.php?edit=<?php echo $expense['S.N.']; ?>" class="text-blue-600 hover:underline mr-4">Edit</a>
                                        <a href="addYourExpense.php?delete=<?php echo $expense['S.N.']; ?>" onclick="return confirm('Are you sure you want to delete this expense?');" class="text-red-600 hover:underline">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($expenses)): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No expenses found!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> -->
</main>

</body>
</html>



<!-- <script>
        document.addEventListener("DOMContentLoaded", function() {
            const welcomeMessage = "Welcome to Expense Tracker";
            let index = 0;
            const speed = 130; // typing speed in milliseconds

            function typeWriter() {
                if (index < welcomeMessage.length) {
                    document.getElementById("welcome-message").textContent += welcomeMessage.charAt(index);
                    index++;
                    setTimeout(typeWriter, speed);
                }
            }

            typeWriter();
        });
    </script> -->