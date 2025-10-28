<?php
include("connection.php");

// Optional date filter
$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$toDate   = isset($_GET['to_date']) ? $_GET['to_date'] : '';

// Base SQL: select all outgoing records
$sql = "SELECT * FROM outgoing";

// Add date filter if provided
if (!empty($fromDate) && !empty($toDate)) {
    $sql .= " WHERE exit_datetime BETWEEN ? AND ?";
}

$sql .= " ORDER BY exit_datetime DESC";

$stmt = $conn->prepare($sql);

// Bind dates if filter applied
if (!empty($fromDate) && !empty($toDate)) {
    $stmt->bind_param("ss", $fromDate, $toDate);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="report.css">
<title>HOD Outgoing Report</title>
</head>
<body>
<h1>Outgoing Students Full Report</h1>

<form method="get">
    From: <input type="date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>">
    To: <input type="date" name="to_date" value="<?= htmlspecialchars($toDate) ?>">
    <input type="submit" value="Filter">
    <button type="button" onclick="window.print()">Print Report</button>
</form>

<table border="1" cellpadding="5" cellspacing="0">
<tr>
    <th>Roll No</th>
    <th>Name</th>
    <th>Department</th>
    <th>Processed By</th>
    <th>Exit DateTime</th>
    <th>Status</th>
    <th>Date</th>
    <th>Time</th>
</tr>

<?php if ($result && $result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['s_rollno']); ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['d_name']); ?></td>
            <!--<td><?= htmlspecialchars($row['approved_by']); ?></td>-->
            <td><?= htmlspecialchars($row['processed_by']); ?></td>
            <td><?= htmlspecialchars($row['exit_datetime']); ?></td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <td><?= htmlspecialchars($row['date']); ?></td>
            <td><?= htmlspecialchars($row['time']); ?></td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="9">No outgoing records found.</td></tr>
<?php endif; ?>
</table>
</body>
</html>
