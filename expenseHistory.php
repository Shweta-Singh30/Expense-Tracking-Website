<?php
require('endPoint/Connection.php');


$sortOption = isset($_POST['sortOptions']) ? $_POST['sortOptions'] : '';
$searchQuery = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';


$today = date('Y-m-d');
$thisWeekStart = date('Y-m-d', strtotime('monday this week'));
$thisMonthStart = date('Y-m-01');


$query = "SELECT * FROM addexpense WHERE 1";
if ($sortOption == 'today') {
    $query .= " AND DATE(createdAt) = '$today'";
} elseif ($sortOption == 'week') {
    $query .= " AND createdAt >= '$thisWeekStart'";
} elseif ($sortOption == 'month') {
    $query .= " AND createdAt >= '$thisMonthStart'";
}

if (!empty($searchQuery)) {
    $query .= " AND (expenseName LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%')";
}

$query .= " ORDER BY `S.N.` ASC";

$expenses = mysqli_query($con, $query);


$totalQuery = "SELECT SUM(price) AS totalExpense FROM addexpense WHERE 1";
if ($sortOption == 'today') {
    $totalQuery .= " AND DATE(createdAt) = '$today'";
} elseif ($sortOption == 'week') {
    $totalQuery .= " AND createdAt >= '$thisWeekStart'";
} elseif ($sortOption == 'month') {
    $totalQuery .= " AND createdAt >= '$thisMonthStart'";
}

if (!empty($searchQuery)) {
    $totalQuery .= " AND (expenseName LIKE '%$searchQuery%' OR description LIKE '%$searchQuery%')";
}

$totalResult = mysqli_query($con, $totalQuery);
$totalExpense = mysqli_fetch_assoc($totalResult)['totalExpense'] ?? 0;


$weekExpensesQuery = "SELECT DATE(createdAt) as date, SUM(price) as total FROM addexpense WHERE createdAt >= '$thisWeekStart' GROUP BY DATE(createdAt)";
$weekExpensesResult = mysqli_query($con, $weekExpensesQuery);
$weekExpenses = [];
while ($row = mysqli_fetch_assoc($weekExpensesResult)) {
    $weekExpenses[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense</title>
    <link rel="stylesheet" href="Assets/Style.css">
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
           
            var weekExpenses = <?php echo json_encode($weekExpenses); ?>;
            var currentWeekData = {
                x: weekExpenses.map(e => e.date),
                y: weekExpenses.map(e => e.total),
                type: 'scatter',
                mode: 'lines+markers',
                marker: {
                    color: 'blue'
                }
            };

           
            var currentWeekLayout = {
                xaxis: {
                    title: 'Date'
                },
                yaxis: {
                    title: 'Expenses ($)'
                }
            };

           
            Plotly.newPlot('currentWeekExpensesChart', [currentWeekData], currentWeekLayout);
        });
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
        <input type="text" id="searchQuery" name="searchQuery" class="search-name" placeholder="Search by name or description" value="<?php echo $searchQuery; ?>">

        <select id="sortOptions" name="sortOptions" class="select">
            <option value="">Sort By</option>
            <option value="today" <?php if ($sortOption == 'today') echo 'selected'; ?>>Current Date</option>
            <option value="week" <?php if ($sortOption == 'week') echo 'selected'; ?>>Current Week</option>
            <option value="month" <?php if ($sortOption == 'month') echo 'selected'; ?>>Current Month</option>
        </select>

        <button type="submit" name="apply" class="applyButton">Apply Changes</button>
        <button type="button" class="resetButton" onclick="resetFilters()">Reset</button>
    </form>

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
                    <?php if (mysqli_num_rows($expenses) > 0) : ?>
                        <?php while ($expense = mysqli_fetch_assoc($expenses)) : ?>
                            <tr>
                                <td><?php echo $expense['S.N.']; ?></td>
                                <td><?php echo $expense['expenseName']; ?></td>
                                <td class="amount"><?php echo $expense['price']; ?></td>
                                <td><?php echo $expense['description']; ?></td>
                                <td><?php echo $expense['createdAt']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="expenseTotals">
            <h3 id="tExpense">Total Expenses: <?php echo $totalExpense; ?></h3>
        </div>
    </center>

    
    <div id="currentWeekExpensesChart"></div>

    <script>
        function resetFilters() {
            document.getElementById("searchQuery").value = '';
            document.getElementById("sortOptions").value = '';
            document.getElementById("filterForm").submit();
        }
    </script>
</body>

</html>
