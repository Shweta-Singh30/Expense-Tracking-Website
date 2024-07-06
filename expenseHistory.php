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

// $selectedWeekStart = isset($_POST['selectedWeekStart']) ? $_POST['selectedWeekStart'] : '';
// $selectedMonthStart = isset($_POST['selectedMonthStart']) ? $_POST['selectedMonthStart'] : '';

$query = "SELECT * FROM addexpense WHERE id = ?";
$params = [$username];

if ($sortOption == 'date' && !empty($selectedDate)) {
    $query .= " AND DATE(date) = ?";
    $params[] = $selectedDate;
} elseif ($sortOption == 'week' && !empty($selectedWeek)) {
    $startOfWeek = date('Y-m-d', strtotime($selectedWeek));
    $query .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 WEEK)";
    $params[] = $selectedWeek;
    $params[] = $selectedWeek;
} elseif ($sortOption == 'month' && !empty($selectedMonthStart)) {
    $query .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 MONTH)";
    $params[] = $selectedMonth;
    $params[] = $selectedMonth;
}elseif ($sortOption == 'year' && !empty($selectedYear)) {
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


// if ($sortOption == 'date' && !empty($selectedDate)) {
//     $totalQuery .= " AND DATE(date) = ?";
//     $totalParams[] = $selectedDate;
// } elseif ($sortOption == 'week' && !empty($selectedWeekStart)) {
//     $totalQuery .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 WEEK)";
//     $totalParams[] = $selectedWeek;
//     $totalParams[] = $selectedWeek;
// } elseif ($sortOption == 'month' && !empty($selectedMonthStart)) {
//     $totalQuery .= " AND date >= ? AND date < DATE_ADD(?, INTERVAL 1 MONTH)";
//     $totalParams[] = $selectedMonth;
//     $totalParams[] = $selectedMonth;
// }
if ($sortOption == 'date' && !empty($selectedDate)) {
        $totalQuery .= " AND DATE(date) = ?";
        $totalParams[] = $selectedDate;
}elseif($sortOption == 'week' && !empty($selectedWeek)) {
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

$weekExpensesQuery = "SELECT DATE(date) as date, SUM(price) as total FROM addexpense WHERE id = ? AND date >= ? GROUP BY DATE(date)";
$weekExpensesParams = [$username, $selectedWeek];

$stmt = $con->prepare($weekExpensesQuery);
$stmt->bind_param('ss', $username, $selectedWeek);
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
          const sortOption = document.getElementById('sortOptions').value;
          document.getElementById('dateInput').style.display = sortOption === 'date' ? 'block' : 'none';
          document.getElementById('weekInput').style.display = sortOption === 'week' ? 'block' : 'none';
          document.getElementById('monthInput').style.display = sortOption === 'month' ? 'block' : 'none';
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
        <input type="text" id="searchQuery" name="searchQuery" class="search-name" placeholder="Search by name or description" value="<?php echo htmlspecialchars($searchQuery); ?>">

        <select id="sortOptions" name="sortOptions" class="select" onchange="toggleDateInputs()">
            <option value="">Sort By</option>
            <option value="date" <?php if ($sortOption == 'date') echo 'selected'; ?>>Date</option>
            <option value="week" <?php if ($sortOption == 'week') echo 'selected'; ?>>Week</option>
            <option value="month" <?php if ($sortOption == 'month') echo 'selected'; ?>>Month</option>
            <option value="year" <?php if ($sortOption == 'year') echo 'selected'; ?>>Year</option>
        </select>

        <div id="dateInput" style="display:none;" class="sInp">
            <label for="selectedDate">Choose Date:</label>
            <input type="date" id="selectedDate" name="selectedDate" value="<?php echo htmlspecialchars($selectedDate); ?>">
        </div>

        <div id="weekInput" style="display: none;" class="sInp">
            <label for="selectedWeekStart">Choose Week Start Date:</label>
            <input type="date" id="selectedWeek" name="selectedWeek" value="<?php echo htmlspecialchars($selectedWeekStart); ?>">
        </div>

        <div id="monthInput" style="display: none;" class="sInp">
            <label for="selectedMonthStart">Choose Month:</label>
            <input type="month" id="selectedMonth" name="selectedMonth" value="<?php echo htmlspecialchars($selectedMonthStart); ?>">
        </div>

        <div id="yearInput" style="display:none;" class="sInp">
            <label for="selectedYear">Choose Year:</label>
            <input type="number" id="selectedYear" name="selectedYear" min="2000" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($selectedYear); ?>">
        </div>

        <div>
            <button type="submit" name="apply" class="applyButton">Apply Changes</button>
            <button type="button" class="resetButton" onclick="resetFilters()">Reset</button>
            <!-- <a href="graph.php?sortOption=<?php echo $sortOption; ?>&selectedDate=<?php echo $selectedDate; ?>&selectedWeekStart=<?php echo $selectedWeekStart; ?>&selectedMonthStart=<?php echo $selectedMonthStart; ?>">See the graph</a> -->
        </div>
    </form>

    <div id="chartsContainer" style="display:flex;justify-content:space-around;">
        <div id="piechart" class="chart" style="width:600px;height:400px;"></div>
        <div id="columnchart" class="chart" style="width:600px;height:400px;"></div>
    </div>

    <center>
        <div class="createTable" width="100%">
            <table class="searchable sortable">
                <thead>
                    <tr>
                        <td class="tHead">
                            <h3>S.N.</h3>
                        </td>
                        <td class="tHead">
                            <h3>Name</h3>
                        </td>
                        <td class="tHead amount">
                            <h3>Price</h3>
                        </td>
                        <td class="tHead">
                            <h3>Description</h3>
                        </td>
                        <td class="tHead">
                            <h3>Created At</h3>
                        </td>
                    </tr>
                </thead>
                <tbody id="expenseTable">
                   
                    <?php if (!empty($expenses)) : ?>
                        <?php foreach ($expenses as $expense) : ?>
                            <tr>
                                <td><?php echo $expense['S.N.']; ?></td>
                                <td><?php echo $expense['expenseName']; ?></td>
                                <td class="amount"><?php echo $expense['price']; ?></td>
                                <td><?php echo $expense['description']; ?></td>
                                <td><?php echo $expense['date']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="5">No expenses found!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="expenseTotals">
            <h3 id="tExpense">Total Expenses: <?php echo htmlspecialchars($totalExpense); ?></h3>
        </div>
    </center>

    <div id="currentWeekExpensesChart"></div>

    <script>
        function resetFilters() {
            document.getElementById("searchQuery").value = '';
            document.getElementById("sortOptions").value = '';
            document.getElementById("selectedDate").value = '';
            document.getElementById("selectedWeek").value = '';
            document.getElementById("selectedMonth").value = '';
            toggleDateInputs(); // Hide date inputs
            document.getElementById("filterForm").submit();
        }

        // Initialize date inputs visibility on page load
        document.addEventListener("DOMContentLoaded", function () {
            toggleDateInputs();
        });
    </script>
</body>

</html>
