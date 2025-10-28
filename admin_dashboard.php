<?php
include("connection.php");

// Get admin_id from URL or default to "Admin"
if (isset($_GET['id'])) {
    $adminId = intval($_GET['id']);
    $sql = "SELECT admin_name FROM admin WHERE a_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $adminName = $row['admin_name'];
    } else {
        $adminName = "Admin";
    }
    $stmt->close();
} else {
    $adminName = "Admin";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
  <div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($adminName); ?></h1>

    <div class="option">
      <button onclick="window.location.href='hod_list.php'">HOD Details</button>
      <button onclick="window.location.href='security_list.php'">Security Details</button>
      <button onclick="window.location.href='outgoing_report.php'">Outgoing Reports</button>
      <button onclick="window.location.href='main.html'">Sign Out</button>
    </div>
  </div>
</body>
</html>
