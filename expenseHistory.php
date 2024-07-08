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
$searchQuery = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';
$selectedDate = isset($_POST['selectedDate']) ? $_POST['selectedDate'] : '';
$selectedWeek = isset($_POST['selectedWeek']) ? $_POST['selectedWeek'] : '';
$selectedMonth = isset($_POST['selectedMonth']) ? $_POST['selectedMonth'] : '';
$selectedYear = isset($_POST['selectedYear']) ? $_POST['selectedYear'] : '';

$query = "SELECT * FROM addexpense WHERE id = ?";
$params = [$username];
$startOfWeek = date('Y-m-d', strtotime($selectedWeek));

if ($sortOption == 'date' && !empty($selectedDate)) {
    $query .= " AND DATE(date) = ?";
    $params[] = $selectedDate;
} elseif ($sortOption == 'week' && !empty($selectedWeek)) {
    $query .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 WEEK)";
    $params[] = $startOfWeek;
    $params[] = $startOfWeek;
} elseif ($sortOption == 'month' && !empty($selectedMonth)) {
    $startOfMonth = date('Y-m-01', strtotime($selectedMonth));
    $query .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 MONTH)";
    $params[] = $startOfMonth;
    $params[] = $startOfMonth;
} elseif ($sortOption == 'year' && !empty($selectedYear)) {
    $query .= " AND YEAR(date) = ?";
    $params[] = $selectedYear;
}

if (!empty($searchQuery)) {
    $query .= " AND (expenseName LIKE ? OR description LIKE ?)";
    $searchParam = "%$searchQuery%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY `S.N.` ASC";

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

if ($sortOption == 'date' && !empty($selectedDate)) {
    $totalQuery .= " AND DATE(date) = ?";
    $totalParams[] = $selectedDate;
} elseif ($sortOption == 'week' && !empty($selectedWeek)) {
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
} elseif ($sortOption == 'month' && !empty($selectedMonth)) {
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
} elseif ($sortOption == 'year' && !empty($selectedYear)) {
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

$weekExpensesQuery = "SELECT DATE(date) as date, SUM(price) as total FROM addexpense WHERE id = ? AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 WEEK) GROUP BY DATE(date)";
$weekExpensesParams = [$username, $startOfWeek, $startOfWeek];

$stmt = $con->prepare($weekExpensesQuery);
$stmt->bind_param('sss', $username, $startOfWeek, $startOfWeek);
$stmt->execute();
$result = $stmt->get_result();
$weekExpenses = [];
while ($row = $result->fetch_assoc()) {
    $weekExpenses[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="x-icon" href="Assets/icon.png">
    <title>Expense History</title>
    <link rel="stylesheet" href="Assets/Style.css">
   
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart']});
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
                    title: '<?php echo ($sortOption == "date" ? "Day" : ($sortOption == "month" ? "Date" : ($sortOption == "year" ? "Month" : ""))); ?>'
                }
            };

            var pieChart = new google.visualization.PieChart(document.getElementById('piechart'));
            var columnChart = new google.visualization.ColumnChart(document.getElementById('columnchart'));

            pieChart.draw(data, options);
            columnChart.draw(data, options);
        }

        function toggleDateInputs() {
            const sortOption = document.getElementById('sortOptions').value;
            document.getElementById('dateInput').style.display = sortOption === 'date' ? 'block' : 'none';
            document.getElementById('weekInput').style.display = sortOption === 'week' ? 'block' : 'none';
            document.getElementById('monthInput').style.display = sortOption === 'month' ? 'block' : 'none';
            document.getElementById('yearInput').style.display = sortOption === 'year' ? 'block' : 'none';
        }

        function resetFilters() {
            document.getElementById('searchQuery').value = '';
            document.getElementById('sortOptions').value = '';
            document.getElementById('selectedDate').value = '';
            document.getElementById('selectedWeek').value = '';
            document.getElementById('selectedMonth').value = '';
            document.getElementById('selectedYear').value = '';
            toggleDateInputs();
            document.getElementById('filterForm').submit();
        }

        window.onload = function() {
            toggleDateInputs();
        }
    </script>
</head>

<body>
    <header>
        <nav>
            <ul>
                <li><a href="Dashboard.html">Home</a></li>
                <li><a href="Home.html">log Out</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="container3">
            <h1>Expense History</h1>
            <form id="filterForm" action="expenseHistory.php" method="POST">
                <input type="text" name="searchQuery" id="searchQuery" placeholder="Search Expense" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <select name="sortOptions" id="sortOptions" onchange="toggleDateInputs()">
                    <option value="" disabled selected>Sort By</option>
                    <option value="date" <?php if ($sortOption == 'date') echo 'selected'; ?>>Date</option>
                    <option value="week" <?php if ($sortOption == 'week') echo 'selected'; ?>>Week</option>
                    <option value="month" <?php if ($sortOption == 'month') echo 'selected'; ?>>Month</option>
                    <option value="year" <?php if ($sortOption == 'year') echo 'selected'; ?>>Year</option>
                </select>
                <div id="dateInput" style="display:none;">
                    <input type="date" name="selectedDate" id="selectedDate" value="<?php echo htmlspecialchars($selectedDate); ?>">
                </div>
                <div id="weekInput" style="display:none;">
                    <input type="week" name="selectedWeek" id="selectedWeek" value="<?php echo htmlspecialchars($selectedWeek); ?>">
                </div>
                <div id="monthInput" style="display:none;">
                    <input type="month" name="selectedMonth" id="selectedMonth" value="<?php echo htmlspecialchars($selectedMonth); ?>">
                </div>
                <div id="yearInput" style="display:none;">
                    <input type="number" name="selectedYear" id="selectedYear" placeholder="YYYY" value="<?php echo htmlspecialchars($selectedYear); ?>">
                </div>
                <button type="submit">Apply</button>
                <button type="button" onclick="resetFilters()">Reset</button>
            </form>
            <h2>Total Expenses: Rs. <?php echo number_format($totalExpense, 2); ?></h2>
            <div id="chartContainer" class="chartContainer">
                <div id="piechart" class="chart"></div>
                <div id="columnchart" class="chart"></div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>S.N.</th>
                        <th>Expense Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($expenses) > 0) : ?>
                        <?php foreach ($expenses as $index => $expense) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($expense['S.N.']); ?></td>
                                <td><?php echo htmlspecialchars($expense['expenseName']); ?></td>
                                <td><?php echo htmlspecialchars($expense['description']); ?></td>
                                <td><?php echo 'Rs. ' . number_format($expense['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($expense['date']))); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No expenses found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Expense Tracker. All Rights Reserved.</p>
    </footer>
</body>

</html>
