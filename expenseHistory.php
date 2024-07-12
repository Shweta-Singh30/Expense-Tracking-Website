<?php
session_start();
require('endPoint/Connection.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: Home.html');
    exit();
}

$username = $_SESSION['username'];

$sortOption = isset($_POST['sortOptions']) ? $_POST['sortOptions'] : '';
$filterOption = isset($_POST['filterOptions']) ? $_POST['filterOptions'] : '';
$searchQuery = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';
$selectedDate = isset($_POST['selectedDate']) ? $_POST['selectedDate'] : '';
$selectedWeek = isset($_POST['selectedWeek']) ? $_POST['selectedWeek'] : '';
$selectedMonth = isset($_POST['selectedMonth']) ? $_POST['selectedMonth'] : '';
$selectedYear = isset($_POST['selectedYear']) ? $_POST['selectedYear'] : '';
$resetFlag = isset($_POST['resetFlag']) ? $_POST['resetFlag'] : '';

if ($resetFlag == 'true') {
    $sortOption = '';
    $filterOption = '';
    $searchQuery = '';
    $selectedDate = '';
    $selectedWeek = '';
    $selectedMonth = '';
    $selectedYear = '';
}

$query = "SELECT * FROM addexpense WHERE id = ?";
$params = [$username];
$startOfWeek = date('Y-m-d', strtotime($selectedWeek));

if ($filterOption == 'date' && !empty($selectedDate)) {
    $query .= " AND DATE(date) = ?";
    $params[] = $selectedDate;
} elseif ($filterOption == 'week' && !empty($selectedWeek)) {
    $query .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 WEEK)";
    $params[] = $startOfWeek;
    $params[] = $startOfWeek;
} elseif ($filterOption == 'month' && !empty($selectedMonth)) {
    $startOfMonth = date('Y-m-01', strtotime($selectedMonth));
    $query .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 MONTH)";
    $params[] = $startOfMonth;
    $params[] = $startOfMonth;
} elseif ($filterOption == 'year' && !empty($selectedYear)) {
    $query .= " AND YEAR(date) = ?";
    $params[] = $selectedYear;
}

if (!empty($searchQuery)) {
    $query .= " AND (expenseName LIKE ? OR description LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($sortOption == 'expense') {
    $query .= " ORDER BY price DESC";
} else {
    $query .= " ORDER BY date ASC";
}

$stmt = $con->prepare($query);

// Dynamically bind parameters based on number of conditions
$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);

$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalQuery = "SELECT SUM(price) AS totalExpense FROM addexpense WHERE id = ?";
$totalParams = [$username];

$labels = [];
$data = [];
$dailyData = [];
$weeklyData = [];
$monthlyData = [];

if ($filterOption == 'date' && !empty($selectedDate)) {
    $totalQuery .= " AND DATE(date) = ?";
    $totalParams[] = $selectedDate;
} elseif ($filterOption == 'week' && !empty($selectedWeek)) {
    $totalQuery .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 WEEK)";
    $totalParams[] = $startOfWeek;
    $totalParams[] = $startOfWeek;

    for ($i = 0; $i < 7; $i++) {
        $day = date('l', strtotime($startOfWeek . " +$i days"));
        $labels[] = $day;
        $dailyData[$day] = 0;
    }
    foreach ($expenses as $expense) {
        $day = date('l', strtotime($expense['date']));
        $dailyData[$day] += $expense['price'];
    }
    $data = array_values($dailyData);
} elseif ($filterOption == 'month' && !empty($selectedMonth)) {
    $totalQuery .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 MONTH)";
    $totalParams[] = $startOfMonth;
    $totalParams[] = $startOfMonth;

    $daysInMonth = date('t', strtotime($selectedMonth));
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $date = date('j', strtotime($selectedMonth . '-' . $i));
        $labels[] = $date;
        $dailyData[$date] = 0;
    }
    foreach ($expenses as $expense) {
        $date = date('j', strtotime($expense['date']));
        $dailyData[$date] += $expense['price'];
    }
    $data = array_values($dailyData);
} elseif ($filterOption == 'year' && !empty($selectedYear)) {
    $totalQuery .= " AND YEAR(date) = ?";
    $totalParams[] = $selectedYear;

    $months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    foreach ($months as $month) {
        $labels[] = $month;
        $monthlyData[$month] = 0;
    }
    foreach ($expenses as $expense) {
        $month = date('F', strtotime($expense['date']));
        $monthlyData[$month] += $expense['price'];
    }
    $data = array_values($monthlyData);
}

if (!empty($searchQuery)) {
    $totalQuery .= " AND (expenseName LIKE ? OR description LIKE ?)";
    $totalParams[] = $searchParam;
    $totalParams[] = $searchParam;
}

$stmt = $con->prepare($totalQuery);

// Dynamically bind parameters based on number of conditions
$types = str_repeat('s', count($totalParams));
$stmt->bind_param($types, ...$totalParams);

$stmt->execute();
$result = $stmt->get_result();
$totalExpense = $result->fetch_assoc()['totalExpense'] ?? 0;
$stmt->close();

// Determine if filters or sorting are applied
$filtersApplied = !empty($filterOption) || !empty($sortOption) || !empty($searchQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="x-icon" href="Assets/icon.png">
    <title>Expense History</title>
    <script src="Assets/tailwind.js"></script>
    <link rel="stylesheet" href="Assets/Style.css">
   
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});

        <?php if ($filtersApplied): ?>
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            var data = google.visualization.arrayToDataTable([
                ['Label', 'Expense'],
                <?php
                foreach ($labels as $index => $label) {
                    echo "['$label', $data[$index]],";
                }
                ?>
            ]);

            var options = {
                title: 'Expenses for Selected Period',
                animation: {
                    startup: true,
                    duration: 1000,
                    easing: 'out'
                },
                vAxis: {
                    title: 'Expenses (Rs.)',
                    currencySymbol: 'Rs.'
                },
                hAxis: {
                    title: '<?php echo ($filterOption == "date" ? "Day" : ($filterOption == "month" ? "Date" : ($filterOption == "year" ? "Month" : ""))); ?>'
                }
            };

            var pieChart = new google.visualization.PieChart(document.getElementById('piechart'));
            var columnChart = new google.visualization.ColumnChart(document.getElementById('columnchart'));

            pieChart.draw(data, options);
            columnChart.draw(data, options);
        }
        <?php endif; ?>

        function toggleDateInputs() {
            var filterOption = document.getElementById('filterOptions').value;
            document.getElementById('dateInput').style.display = filterOption === 'date' ? 'block' : 'none';
            document.getElementById('weekInput').style.display = filterOption === 'week' ? 'block' : 'none';
            document.getElementById('monthInput').style.display = filterOption === 'month' ? 'block' : 'none';
            document.getElementById('yearInput').style.display = filterOption === 'year' ? 'block' : 'none';
        }

        function resetFilters() {
            document.getElementById('searchQuery').value = '';
            document.getElementById('sortOptions').value = '';
            document.getElementById('filterOptions').value = '';
            document.getElementById('selectedDate').value = '';
            document.getElementById('selectedWeek').value = '';
            document.getElementById('selectedMonth').value = '';
            document.getElementById('selectedYear').value = '';
            document.getElementById('resetFlag').value = 'true';
            document.getElementById('filterForm').submit();
        }
    </script>
</head>

<body class="p-6 flex flex-col justify-between h-screen bg-gray-200">
    <header>
        <nav class="flex justify-between items-center bg-gray-800 text-white p-4 rounded shadow-md">
            <div class="flex items-center">
                <img src="Assets/icon.png" alt="ExpenseTracker Logo" class="h-10 w-10 mr-2">
                <h1 class="text-2xl font-bold">ExpenseTracker</h1>
            </div>
            <div>
                <a href="Dashboard.php" class="hover:text-gray-400">Home</a>
                <a href="addYourExpense.php" class="ml-4 hover:text-gray-400">Add Expense</a>
                <a href="profile.php" class="ml-4 hover:text-gray-400">Profile</a>
                <a href="Home.html" class="ml-4 hover:text-gray-400">Logout</a>
            </div>
        </nav>
    </header>
    <main class="my-6">
        <h2 class="text-2xl font-bold mb-4">Expense History</h2>
        <form method="post" class="mb-6" id="filterForm">
            <div class="flex flex-wrap mb-4">
                <div class="w-full md:w-1/3 px-2">
                    <label for="searchQuery" class="block text-gray-700">Search</label>
                    <input type="text" id="searchQuery" name="searchQuery" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search by name or description" class="w-full p-2 border rounded">
                </div>
                <div class="w-full md:w-1/3 px-2">
                    <label for="sortOptions" class="block text-gray-700">Sort by</label>
                    <select id="sortOptions" name="sortOptions" class="w-full p-2 border rounded">
                        <option value="">Date (default)</option>
                        <option value="expense" <?php if ($sortOption == 'expense') echo 'selected'; ?>>Expense</option>
                    </select>
                </div>
                <div class="w-full md:w-1/3 px-2">
                    <label for="filterOptions" class="block text-gray-700">Filter by</label>
                    <select id="filterOptions" name="filterOptions" class="w-full p-2 border rounded" onchange="toggleDateInputs()">
                        <option value="">None</option>
                        <option value="date" <?php if ($filterOption == 'date') echo 'selected'; ?>>Date</option>
                        <option value="week" <?php if ($filterOption == 'week') echo 'selected'; ?>>Week</option>
                        <option value="month" <?php if ($filterOption == 'month') echo 'selected'; ?>>Month</option>
                        <option value="year" <?php if ($filterOption == 'year') echo 'selected'; ?>>Year</option>
                    </select>
                </div>
            </div>
            <div class="flex flex-wrap mb-4">
                <div id="dateInput" class="w-full md:w-1/3 px-2" style="display: <?php echo ($filterOption == 'date') ? 'block' : 'none'; ?>;">
                    <label for="selectedDate" class="block text-gray-700">Select Date</label>
                    <input type="date" id="selectedDate" name="selectedDate" value="<?php echo htmlspecialchars($selectedDate); ?>" class="w-full p-2 border rounded">
                </div>
                <div id="weekInput" class="w-full md:w-1/3 px-2" style="display: <?php echo ($filterOption == 'week') ? 'block' : 'none'; ?>;">
                    <label for="selectedWeek" class="block text-gray-700">Select Week</label>
                    <input type="week" id="selectedWeek" name="selectedWeek" value="<?php echo htmlspecialchars($selectedWeek); ?>" class="w-full p-2 border rounded">
                </div>
                <div id="monthInput" class="w-full md:w-1/3 px-2" style="display: <?php echo ($filterOption == 'month') ? 'block' : 'none'; ?>;">
                    <label for="selectedMonth" class="block text-gray-700">Select Month</label>
                    <input type="month" id="selectedMonth" name="selectedMonth" value="<?php echo htmlspecialchars($selectedMonth); ?>" class="w-full p-2 border rounded">
                </div>
                <div id="yearInput" class="w-full md:w-1/3 px-2" style="display: <?php echo ($filterOption == 'year') ? 'block' : 'none'; ?>;">
                    <label for="selectedYear" class="block text-gray-700">Select Year</label>
                    <input type="number" id="selectedYear" name="selectedYear" value="<?php echo htmlspecialchars($selectedYear); ?>" min="2000" max="<?php echo date('Y'); ?>" placeholder="YYYY" class="w-full p-2 border rounded">
                </div>
            </div>
            <input type="hidden" id="resetFlag" name="resetFlag" value="false">
            <div class="flex items-center">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Apply</button>
                <button type="button" onclick="resetFilters()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded">Reset</button>
            </div>
        </form>
        <?php if ($filtersApplied): ?>
        <div class="flex justify-around mb-6 border-2 border-white shadow-lg shadow-slate-400">
            <div id="piechart" class="w-1/2"></div>
            <div id="columnchart" class="w-1/2"></div>
        </div>
        <?php endif; ?>
        <div class="bg-white rounded shadow-md overflow-x-auto">
            <table class="min-w-full bg-white  shadow-lg shadow-slate-400 ">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border">Date</th>
                        <th class="px-4 py-2 border">Expense Name</th>
                        <th class="px-4 py-2 border">Description</th>
                        <th class="px-4 py-2 border">Price (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($expense['date']); ?></td>
                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($expense['expenseName']); ?></td>
                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($expense['description']); ?></td>
                        <td class="px-4 py-2 border"><?php echo htmlspecialchars($expense['price']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="mt-4">Total Expense: <span class="font-bold">Rs. <?php echo htmlspecialchars($totalExpense); ?></span></p>
    </main>
    <footer class="text-center mt-6">
        <p>&copy; <?php echo date("Y"); ?> ExpenseTracker. All rights reserved.</p>
    </footer>
</body>

</html>
