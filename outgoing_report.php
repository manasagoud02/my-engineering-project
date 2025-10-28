<?php
include("connection.php");

// Fetch all outgoing records (all columns)
$sql = "SELECT * FROM outgoing ORDER BY exit_datetime DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Outgoing Students Report</title>
   <link rel="stylesheet" href="report.css">
</head>
<body>
<h1>Approved Outgoing Students Report</h1>

<button class="btn" onclick="window.print()">🖨️ Print Report</button>

<table>
<tr>
    <th>ID</th>
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
            <td><?= htmlspecialchars($row['o_id']); ?></td>
            <td><?= htmlspecialchars($row['s_rollno']); ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['d_name']); ?></td>
            <td><?= htmlspecialchars($row['processed_by']); ?></td>
            <td><?= htmlspecialchars($row['exit_datetime']); ?></td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <td><?= htmlspecialchars($row['date']); ?></td>
            <td><?= htmlspecialchars($row['time']); ?></td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr><td colspan="10">No approved outgoing student records found.</td></tr>
<?php endif; ?>
</table>
</body>
</html>
