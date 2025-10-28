<?php
include("connection.php");

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];

    $status = ($action === 'approve') ? 1 : 2; // 1 = Approved, 2 = Rejected

    $sql = "UPDATE request SET status=? WHERE r_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $status, $id);

    if ($stmt->execute()) {
        // Redirect back with success
        header("Location: hod_dashboard.php?msg=RequestUpdated");
    } else {
        echo "❌ Failed to update request.";
    }
}
?>