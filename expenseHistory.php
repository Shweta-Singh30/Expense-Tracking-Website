<?php
session_start();
require('endPoint/Connection.php');

if (!isset($_SESSION['username'])) {
    header('Location: Home.html');
    exit();
}

$username = $_SESSION['username'];

$filterOption = isset($_POST['filterOption']) ? $_POST['filterOption'] : '';
$sortOption = isset($_POST['sortOption']) ? $_POST['sortOption'] : 'date';

$filterDate = isset($_POST['filterDate']) ? $_POST['filterDate'] : '';
$filterWeek = isset($_POST['filterWeek']) ? $_POST['filterWeek'] : '';
$filterMonth = isset($_POST['filterMonth']) ? $_POST['filterMonth'] : '';
$filterYear = isset($_POST['filterYear']) ? $_POST['filterYear'] : '';

$query = "SELECT * FROM addexpense WHERE id = ?";

if ($filterOption == 'date' && !empty($filterDate)) {
    $query .= " AND date = ?";
} elseif ($filterOption == 'week' && !empty($filterWeek)) {
    $year = date('Y', strtotime($filterWeek));
    $week = date('W', strtotime($filterWeek));
    $query .= " AND YEARWEEK(date, 1) = YEARWEEK(?, 1)";
} elseif ($filterOption == 'month' && !empty($filterMonth)) {
    $query .= " AND DATE_FORMAT(date, '%Y-%m') = ?";
} elseif ($filterOption == 'year' && !empty($filterYear)) {
    $query .= " AND YEAR(date) = ?";
}

if ($sortOption == 'date') {
    $query .= " ORDER BY date DESC";
} elseif ($sortOption == 'expense') {
    $query .= " ORDER BY price DESC";
}

$stmt = $con->prepare($query);
$param = $username;

if ($filterOption == 'date' && !empty($filterDate)) {
    $stmt->bind_param("ss", $param, $filterDate);
} elseif ($filterOption == 'week' && !empty($filterWeek)) {
    $stmt->bind_param("ss", $param, $filterWeek);
} elseif ($filterOption == 'month' && !empty($filterMonth)) {
    $stmt->bind_param("ss", $param, $filterMonth);
} elseif ($filterOption == 'year' && !empty($filterYear)) {
    $stmt->bind_param("ss", $param, $filterYear);
} else {
    $stmt->bind_param("s", $param);
}

$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$con->close();

// Handle delete action
if (isset($_GET['delete'])) {
    $expenseId = $_GET['delete'];
    echo "<script>
        if (confirm('Are you sure you want to delete this expense?')) {
            window.location.href = 'expenseHistory.php?confirmDelete=$expenseId';
        }
    </script>";
}

if (isset($_GET['confirmDelete'])) {
    $expenseId = $_GET['confirmDelete'];
    $con = new mysqli('localhost', 'username', 'password', 'database');
    $stmt = $con->prepare("DELETE FROM addexpense WHERE `S.N.` = ? AND id = ?");
    $stmt->bind_param("ss", $expenseId, $username);
    $stmt->execute();
    $stmt->close();
    $con->close();
    header('Location: expenseHistory.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense History</title>
    <script src="Assets/tailwind.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
   
   
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const filterOptionSelect = document.getElementById('filterOption');
            const filterInputs = document.querySelectorAll('.filter-group');

            filterOptionSelect.addEventListener('change', (event) => {
                const selectedFilter = event.target.value;
                filterInputs.forEach(group => group.classList.add('hidden'));
                if (selectedFilter) {
                    document.getElementById(`${selectedFilter}Filter`).classList.remove('hidden');
                }
            });
        });
    </script>
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
                <a href="Home.html" class="hover:text-gray-400">Logout</a>
            </div>
        </div>
    </nav>
</header>

<main class="container mx-auto mt-10 px-4">
    <div class="flex justify-end mb-4">
        <form action="expenseHistory.php" method="POST" class="flex space-x-4">
            <div>
                <label for="filterOption" class="block text-sm font-medium text-gray-700">Filter By</label>
                <select id="filterOption" name="filterOption" class="block mt-1 p-2 border border-gray-300 rounded-md">
                    <option value="" <?php if ($filterOption == '') echo 'selected'; ?>>Select Filter</option>
                    <option value="date" <?php if ($filterOption == 'date') echo 'selected'; ?>>Date</option>
                    <option value="week" <?php if ($filterOption == 'week') echo 'selected'; ?>>Week</option>
                    <option value="month" <?php if ($filterOption == 'month') echo 'selected'; ?>>Month</option>
                    <option value="year" <?php if ($filterOption == 'year') echo 'selected'; ?>>Year</option>
                </select>
            </div>

            <div id="filterInputs" class="flex flex-col space-y-2">
                <!-- Date Filter -->
                <div id="dateFilter" class="filter-group <?php if ($filterOption != 'date') echo 'hidden'; ?>">
                    <label for="filterDate" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="filterDate" name="filterDate" value="<?php echo htmlspecialchars($filterDate); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <!-- Week Filter -->
                <div id="weekFilter" class="filter-group <?php if ($filterOption != 'week') echo 'hidden'; ?>">
                    <label for="filterWeek" class="block text-sm font-medium text-gray-700">Select a Date</label>
                    <input type="date" id="filterWeek" name="filterWeek" value="<?php echo htmlspecialchars($filterWeek); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <!-- Month Filter -->
                <div id="monthFilter" class="filter-group <?php if ($filterOption != 'month') echo 'hidden'; ?>">
                    <label for="filterMonth" class="block text-sm font-medium text-gray-700">Month</label>
                    <input type="month" id="filterMonth" name="filterMonth" value="<?php echo htmlspecialchars($filterMonth); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                </div>

                <!-- Year Filter -->
                <div id="yearFilter" class="filter-group <?php if ($filterOption != 'year') echo 'hidden'; ?>">
                    <label for="filterYear" class="block text-sm font-medium text-gray-700">Year</label>
                    <input type="number" id="filterYear" name="filterYear" value="<?php echo htmlspecialchars($filterYear); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md" min="2000" max="<?php echo date('Y'); ?>">
                </div>
            </div>

            <div>
                <label for="sortOption" class="block text-sm font-medium text-gray-700">Sort By</label>
                <select id="sortOption" name="sortOption" class="block mt-1 p-2 border border-gray-300 rounded-md">
                    <option value="date" <?php if ($sortOption == 'date') echo 'selected'; ?>>Date</option>
                    <option value="expense" <?php if ($sortOption == 'expense') echo 'selected'; ?>>Expense</option>
                </select>
            </div>

            <div class="flex items-center space-x-4">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">Apply</button>
                <a href="expenseHistory.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">Clear All</a>
            </div>
        </form>
    </div>

    <!-- Legend for Price Ranges -->
    <div class="flex justify-end mb-4">
        <div class="text-right">
            <div class="flex items-center mb-2">
                <span class="w-4 h-4 inline-block bg-blue-900 mr-2"></span>
                <span>Less than 1000</span>
            </div>
            <div class="flex items-center mb-2">
                <span class="w-4 h-4 inline-block bg-green-700 mr-2"></span>
                <span>1000 - 5000</span>
            </div>
            <div class="flex items-center mb-2">
                <span class="w-4 h-4 inline-block bg-pink-600 mr-2"></span>
                <span>5000 - 50000</span>
            </div>
            <div class="flex items-center mb-2">
                <span class="w-4 h-4 inline-block bg-orange-600 mr-2"></span>
                <span>50000 - 100000</span>
            </div>
            <div class="flex items-center mb-2">
                <span class="w-4 h-4 inline-block bg-rose-700 mr-2"></span>
                <span>Greater than 100000</span>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap justify-center gap-6">
        <?php foreach ($expenses as $expense): ?>
            <?php
                // Determine the icon color based on the price
                if ($expense['price'] < 1000) {
                    $iconColor = 'text-blue-900';
                } elseif ($expense['price'] >= 1000 && $expense['price'] < 5000) {
                    $iconColor = 'text-green-700';
                } elseif ($expense['price'] >= 5000 && $expense['price'] < 50000) {
                    $iconColor = 'text-pink-600';
                } elseif ($expense['price'] >= 50000 && $expense['price'] < 100000) {
                    $iconColor = 'text-orange-600';
                } else {
                    $iconColor = 'text-rose-700';
                }
            ?>
            <div class="border rounded bg-white shadow-lg shadow-indigo-300 flex flex-col justify-between w-52 h-80">
                <div class="h-36 bg-gradient-to-r from-cyan-300 to-cyan-600">
                    <div class="material-symbols-outlined font-medium text-[100px] weight grid place-content-center pt-4 <?php echo $iconColor; ?>">
                        paid
                    </div>
                </div>
                <div class="p-4">
                    <h2 class="text-lg font-bold"><?php echo htmlspecialchars($expense['expenseName']); ?></h2>
                    <p class="text-gray-600"><?php echo htmlspecialchars($expense['description']); ?></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($expense['date']); ?></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($expense['price']); ?> Rs.</p>
                </div>
                <div class="flex justify-end mt-4">
                    <a href="addYourExpense.php?edit=<?php echo $expense['S.N.']; ?>" class="text-blue-500 hover:underline mr-4">Edit</a>
                    <a href="expenseHistory.php?delete=<?php echo $expense['S.N.']; ?>" class="text-red-500 hover:underline" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
