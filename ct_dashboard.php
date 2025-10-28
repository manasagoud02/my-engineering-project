<?php
session_start();

/* =============================
   DATABASE CONNECTION SECTION
   ============================= */
$servername = "localhost";   // XAMPP default
$username   = "root";        // default MySQL user
$password   = "";            // default has no password
$dbname     = "sm";  // 🔹 Change this to your actual DB name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

/* =============================
   MAIN LOGIC STARTS HERE
   ============================= */

// --- FETCH STUDENT DETAILS ---
$student = null;
$error = "";
$success = "";

// Show session messages after redirect
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

// --- FETCH STUDENT ---
if (isset($_POST['fetch'])) {
    $s_rollno = $conn->real_escape_string($_POST['s_rollno']);

    $sql = "SELECT 
            s.s_rollno, s.firstname, s.lastname,
            d.d_name,
            c.mother_name, c.father_name, c.student_number, c.parent_number
        FROM student s
        LEFT JOIN s_dept sd ON s.s_rollno = sd.s_rollno
        LEFT JOIN department d ON sd.d_id = d.d_id
        LEFT JOIN s_contact_details c ON s.s_rollno = c.s_rollno
        WHERE s.s_rollno = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $s_rollno);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $student = $result->fetch_assoc();
    } else {
        $error = "❌ No student found with Roll No: $s_rollno.";
    }
}

// --- SEND REQUEST WITH REASON ---
if (isset($_POST['send_request'])) {
    $s_rollno = $conn->real_escape_string($_POST['s_rollno']);
    $reason   = $conn->real_escape_string($_POST['reason']);

    if (empty(trim($reason))) {
        $_SESSION['error'] = "⚠ Please provide a reason for the request!";
        header("Location: ct_dashboard.php");
        exit;
    }

    // Check if student exists
    $checkSql = "SELECT * FROM student WHERE s_rollno=?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $s_rollno);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Check if pending request exists
        $checkExisting = "SELECT * FROM request WHERE s_rollno=? AND status=0";
        $existingStmt = $conn->prepare($checkExisting);
        $existingStmt->bind_param("s", $s_rollno);
        $existingStmt->execute();
        $existingResult = $existingStmt->get_result();

        if ($existingResult->num_rows > 0) {
            $_SESSION['error'] = "⚠ A pending request for this student already exists!";
        } else {
            $insertSql = "INSERT INTO request (s_rollno, status, reason) VALUES (?, 0, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ss", $s_rollno, $reason);

            if ($insertStmt->execute()) {
                $_SESSION['success'] = "✅ Request sent successfully!";
            } else {
                $_SESSION['error'] = "⚠ Failed to send request.";
            }
        }
    } else {
        $_SESSION['error'] = "❌ Student roll number not found!";
    }

    header("Location: ct_dashboard.php");
    exit;
}

// --- FETCH ALL REQUESTS ---
$allRequests = $conn->query("
    SELECT r.*, s.firstname, s.lastname 
    FROM request r
    JOIN student s ON r.s_rollno = s.s_rollno
    ORDER BY r.r_id DESC
");
if (!$allRequests) {
    die("Query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class Incharge Dashboard</title>
    <link rel="stylesheet" href="ct_dashboard.css">
</head>
<body>
    <div class="container">
        <h1>Class Incharge Dashboard</h1>

        <!-- Messages -->
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

        <!-- Student Roll No Form -->
        <form method="post" class="form-box">
            <label>Enter Student Roll No:</label>
            <input type="text" name="s_rollno" required>
            <button type="submit" name="fetch">Fetch</button>
        </form>

        <!-- Student Details -->
        <?php if ($student): ?>
            <h2>Student Details</h2>
            <table>
                <tr><th>Roll No</th><td><?= htmlspecialchars($student['s_rollno']); ?></td></tr>
                <tr><th>First Name</th><td><?= htmlspecialchars($student['firstname']); ?></td></tr>
                <tr><th>Last Name</th><td><?= htmlspecialchars($student['lastname']); ?></td></tr>
                <tr><th>Department</th><td><?= htmlspecialchars($student['d_name']); ?></td></tr>
                <tr><th>Father Name</th><td><?= htmlspecialchars($student['father_name']); ?></td></tr>
                <tr><th>Mother Name</th><td><?= htmlspecialchars($student['mother_name']); ?></td></tr>
                <tr><th>Student Phone</th><td><?= htmlspecialchars($student['student_number']); ?></td></tr>
                <tr><th>Parent Phone</th><td><?= htmlspecialchars($student['parent_number']); ?></td></tr>
            </table>

            <!-- Send Request Form with Reason -->
            <form method="post" class="form-box">
                <input type="hidden" name="s_rollno" value="<?= $student['s_rollno']; ?>">
                
                <label for="reason">Reason for Request:</label>
                <textarea name="reason" id="reason" rows="3" required placeholder="Enter the reason..."></textarea>
                
                <button type="submit" name="send_request">Send Request</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- All Requests Table -->
    <h2>All Requests</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>Request ID</th>
            <th>Roll No</th>
            <th>Student Name</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        <?php while($row = $allRequests->fetch_assoc()): ?>
        <tr>
            <td><?= $row['r_id'] ?></td>
            <td><?= $row['s_rollno'] ?></td>
            <td><?= $row['firstname'] . " " . $row['lastname'] ?></td>
            <td><?= htmlspecialchars($row['reason']); ?></td>
            <td>
                <?php
                    if ($row['status'] == 0) echo "⏳ Pending (HOD approval)";
                    elseif ($row['status'] == 1) echo "✅ Approved (Awaiting Security)";
                    else echo "❌ Rejected";
                ?>
            </td>
            <td>
                <?php if($row['status'] == 1): ?>
                    <a href="ct_proceed.php?r_id=<?= $row['r_id'] ?>" class="btn btn-primary">Proceed</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
