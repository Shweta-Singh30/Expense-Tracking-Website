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
$selectedWeek = isset($_POST['selectedWeek']) ? $_POST['selectedWeek'] : '';
$selectedMonth = isset($_POST['selectedMonth']) ? $_POST['selectedMonth'] : '';
$selectedYear = isset($_POST['selectedYear']) ? $_POST['selectedYear'] : '';

$query = "SELECT * FROM addexpense WHERE id = ?";
$params = [$username];

if ($sortOption == 'date' && !empty($selectedWeek)) {
    $startOfWeek = date('Y-m-d', strtotime($selectedWeek));
    $endOfWeek = date('Y-m-d', strtotime($selectedWeek . ' +6 days'));
    $query .= " AND DATE(date) BETWEEN ? AND ?";
    $params[] = $startOfWeek;
    $params[] = $endOfWeek;
} elseif ($sortOption == 'month' && !empty($selectedMonth)) {
    $startOfMonth = date('Y-m-01', strtotime($selectedMonth));
    $endOfMonth = date('Y-m-t', strtotime($selectedMonth));
    $query .= " AND DATE(date) BETWEEN ? AND ?";
    $params[] = $startOfMonth;
    $params[] = $endOfMonth;
} elseif ($sortOption == 'year' && !empty($selectedYear)) {
    $query .= " AND YEAR(date) = ?";
    $params[] = $selectedYear;
}

$stmt = $con->prepare($query);
$types = str_repeat('s', count($params));
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$expenses = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$labels = [];
$data = [];
$dailyData = [];
$weeklyData = [];
$monthlyData = [];

// Initialize labels and data arrays based on the selected period
if ($sortOption == 'date' && !empty($selectedWeek)) {
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Assets/Style.css">
    <link rel="shortcut icon" type="x-icon" href="Assets/icon.png">
    <title>Graph History</title>
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
            // format: 'currency',
            currencySymbol: 'Rs.'
          },
          hAxis: {
            title: '<?php echo ($sortOption == "date" ? "Day" : ($sortOption == "month" ? "Date" : "Month")); ?>'
          }
        };
        

        var pieChart = new google.visualization.PieChart(document.getElementById('piechart'));
        var columnChart = new google.visualization.ColumnChart(document.getElementById('columnchart'));

        pieChart.draw(data, options);
        columnChart.draw(data, options);
      }

      function toggleDateInputs() {
        var sortOptions = document.getElementById('sortOptions').value;
        document.getElementById('weekInput').style.display = (sortOptions == 'date') ? 'block' : 'none';
        document.getElementById('monthInput').style.display = (sortOptions == 'month') ? 'block' : 'none';
        document.getElementById('yearInput').style.display = (sortOptions == 'year') ? 'block' : 'none';
      }

      function resetFilters() {
        document.getElementById('sortOptions').value = '';
        document.getElementById('selectedWeek').value = '';
        document.getElementById('selectedMonth').value = '';
        document.getElementById('selectedYear').value = '';
        toggleDateInputs();
      }

      window.onload = toggleDateInputs;
    </script>
</head>
<body>
<header>
    <nav class="navbar">
        <ul>
            <li class="ulLink"><a href="Dashboard.html" class="Home">Home</a></li>
            <li class="ulLink"><a href="#" id="Contact">Contact</a></li>
            <li class="ulLink"><a href="#" id="graph"></a></li>
            <li class="ulLink"><a href="#" id="aboutUs">About Us</a></li>
            <li class="ulLink"><button id="Logout"><a href="Home.html" class="Logout">Logout</a></button></li>
        </ul>
    </nav>
</header>

<form id="filterForm" class="filterForm" method="POST" action="">
    <select id="sortOptions" name="sortOptions" class="select" onchange="toggleDateInputs()">
        <option value="">Sort By</option>
        <option value="date" <?php if ($sortOption == 'date') echo 'selected'; ?>>Week</option>
        <option value="month" <?php if ($sortOption == 'month') echo 'selected'; ?>>Month</option>
        <option value="year" <?php if ($sortOption == 'year') echo 'selected'; ?>>Year</option>
    </select>

    <div id="weekInput" style="display:none;" class="sInp">
        <label for="selectedWeek">Choose Week:</label>
        <input type="week" id="selectedWeek" name="selectedWeek" value="<?php echo htmlspecialchars($selectedWeek); ?>">
    </div>

    <div id="monthInput" style="display:none;" class="sInp">
        <label for="selectedMonth">Choose Month:</label>
        <input type="month" id="selectedMonth" name="selectedMonth" value="<?php echo htmlspecialchars($selectedMonth); ?>">
    </div>

    <div id="yearInput" style="display:none;" class="sInp">
        <label for="selectedYear">Choose Year:</label>
        <input type="number" id="selectedYear" name="selectedYear" min="2000" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($selectedYear); ?>">
    </div>

    <div>
        <button type="submit" name="apply" class="applyButton">Apply Changes</button>
        <button type="button" class="resetButton" onclick="resetFilters()">Reset</button>
    </div>
</form>

<div id="chartsContainer" style="display:flex;justify-content:space-around;">
    <div id="piechart" class="chart" style="width:600px;height:400px;"></div>
    <div id="columnchart" class="chart" style="width:600px;height:400px;"></div>
</div>
<footer></footer>
</body>
</html>