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

        function toggleDateInputs() {
            const filterOption = document.getElementById('filterOptions').value;
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
            toggleDateInputs();
            document.getElementById('filterForm').submit();
        }

        window.onload = function() {
            toggleDateInputs();
        }
    </script>
</head>

<body>
<header class="w-full  h-auto">

<nav class="bg-purple-950 w-full  h-[60px] md:h-[45px] flex justify-between items-center  lg:items-center  ">

    <div class="bg-clip-text text-transparent bg-white   ml-2 font-serif font-extrabold  text-2xl">Expense Tracker</div>

    <ul class="md:flex hidden font-semibold ml-2 ">
        <li class="mx-[10px] curser-pointer text-white">Home</li>
        <li class="mx-[10px] curser-pointer text-white">Contact</li>
        <li class="mx-[10px] curser-pointer text-white">About Us</li>
    </ul>

    <div class="hidden my-auto text-[15px] font-serif md:flex mr-2">
                <button>
                    <a href="Register.php" class="bg-rose-600 text-white px-[14px]  p-[5px] hover:text-[16px] hover:bg-pink-700 md:w-10 border-2 border-red-500 rounded-lg ">Signup</a>
                </button>
    </div>
    

    <div class="md:hidden m-2">
        <a class="text-4xl" href="#">&#8801;</a>
    </div>

</nav>

</header>
    <!-- <header>
        <nav>
            <ul>
                <li><a href="Dashboard.html">Home</a></li>
                <li><a href="Home.html">Log Out</a></li>
            </ul>
        </nav>
    </header> -->

    <main>
        <div class="container3">

            <div class="grid place-content-center">
                <h1 class="text-4xl font-semibold font-serim">Expense History</h1>
            </div>

            <form class="inline-flex flex items-center " id="filterForm" action="expenseHistory.php" method="POST">
                
                <input class="text-[13px] h-[30px] w-[370px] border-[1px] border-slate-500 m-5 rounded-full text-center bg-white" type="text" name="searchQuery" id="searchQuery" placeholder="Search Expense by Discription or Expense Name" value="<?php echo htmlspecialchars($searchQuery); ?>">
                <select name="filterOptions" id="filterOptions" onchange="toggleDateInputs()">
                    <option value="" disabled selected>Filter By</option>
                    <option value="date" <?php if ($filterOption == 'date') echo 'selected'; ?>>Date</option>
                    <option value="week" <?php if ($filterOption == 'week') echo 'selected'; ?>>Week</option>
                    <option value="month" <?php if ($filterOption == 'month') echo 'selected'; ?>>Month</option>
                    <option value="year" <?php if ($filterOption == 'year') echo 'selected'; ?>>Year</option>
                </select>
                <select name="sortOptions" id="sortOptions">
                    <option value="" disabled selected>Sort By</option>
                    <option value="expense" <?php if ($sortOption == 'expense') echo 'selected'; ?>>Expense</option>
                    <option value="date" <?php if ($sortOption == 'date') echo 'selected'; ?>>Date</option>
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
                <div id="piechart" class="chart" ></div>
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
