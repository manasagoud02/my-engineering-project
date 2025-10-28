<?php
include("connection.php");

// --- Fetch latest pending requests per student ---
$sql = "SELECT r1.r_id, r1.s_rollno, r1.status, r1.reason, s.firstname, s.lastname
        FROM request r1
        INNER JOIN (
            SELECT s_rollno, MAX(r_id) AS latest_id
            FROM request
            GROUP BY s_rollno
        ) r2 
        ON r1.s_rollno = r2.s_rollno AND r1.r_id = r2.latest_id
        JOIN student s ON r1.s_rollno = s.s_rollno
        WHERE r1.status = 0
        ORDER BY r1.r_id DESC";

$result = $conn->query($sql);

// ✅ Define $hasRequest safely
$hasRequest = false;
if ($result) {
    $hasRequest = $result->num_rows > 0;
} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>HOD Dashboard</title>
  <!-- Base styling for all dashboards -->
  <link rel="stylesheet" href="style_base.css">
  <!-- HOD-specific colors -->
  <link rel="stylesheet" href="hod_dashboard.css">
</head>
<body>
  <div class="container">
    <h1>Welcome HOD</h1>

    <!-- Outgoing Report Button -->
    <div>
        <button class="btn" onclick="window.location.href='hod_report.php'">
            📊 View Outgoing Reports
        </button>
    </div>

    <?php if ($hasRequest) { ?>
        <button class="btn" onclick="document.getElementById('requests').style.display='block'">
          🔔 You have a new request!
        </button>
        
        <div id="requests" class="card" style="display:none;">
          <h2>Pending Requests</h2>
          <table>
            <tr>
              <th>Request ID</th>
              <th>Roll No</th>
              <th>Student Name</th>
              <th>Reason</th>
              <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
              <td><?= $row['r_id'] ?></td>
              <td><?= $row['s_rollno'] ?></td>
              <td><?= $row['firstname']." ".$row['lastname'] ?></td>
              <td><?= htmlspecialchars($row['reason']) ?></td>
              <td>
                <a class="btn" href="update_request.php?action=approve&id=<?= $row['r_id'] ?>">✅ Approve</a>
                <a class="btn" href="update_request.php?action=reject&id=<?= $row['r_id'] ?>">❌ Reject</a>
              </td>
            </tr>
            <?php } ?>
          </table>
        </div>
    <?php } else { ?>
        <p>No new requests ✅</p>
    <?php } ?>
  </div>
</body>
</html>
