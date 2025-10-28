<?php
require_once __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;

include("connection.php");
session_start();

// --- Check session username ---
$ct_name = $_SESSION['username'] ?? 'Class Incharge';

// --- Twilio credentials ---
$account_sid = 'AC16fc106074a66069ffa14dc87f62eeee';
$auth_token  = '7f81a652c820abdaa29e2b832422b520';
$twilio_number = '+14143488559';
$client = new Client($account_sid, $auth_token);

if (isset($_GET['r_id'])) {
    $r_id = intval($_GET['r_id']);

    // --- Fetch request details including reason and status ---
    $sql = "SELECT r.s_rollno, s.firstname, s.lastname, c.parent_number, d.d_name, 
                   r.reason, r.status
            FROM request r
            JOIN student s ON r.s_rollno = s.s_rollno
            LEFT JOIN s_contact_details c ON s.s_rollno = c.s_rollno
            LEFT JOIN s_dept sd ON s.s_rollno = sd.s_rollno
            LEFT JOIN department d ON sd.d_id = d.d_id
            WHERE r.r_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("❌ Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $r_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // --- Check if record exists ---
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // --- Check approval status ---
        if ($row['status'] != 1) {
            echo "⚠ This request (ID: $r_id) was not approved by HOD yet. Please approve it first.";
            exit;
        }

        // --- Prepare outgoing entry ---
        $exit_datetime = date('Y-m-d H:i:s');
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $status = 'Completed';
        $name = $row['firstname'] . ' ' . $row['lastname'];

        $insert = $conn->prepare("INSERT INTO outgoing 
            (s_rollno, name, d_name, processed_by, exit_datetime, status, date, time, reason) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$insert) {
            die("❌ Prepare failed for outgoing insert: " . $conn->error);
        }

        $insert->bind_param("sssssssss",
            $row['s_rollno'],
            $name,
            $row['d_name'],
            $ct_name,
            $exit_datetime,
            $status,
            $date,
            $time,
            $row['reason']
        );

        if ($insert->execute()) {
            echo "✅ Outgoing record saved successfully.<br>";
        } else {
            echo "❌ Failed to insert outgoing record: " . $insert->error . "<br>";
        }

        // --- Send SMS to parent ---
        if (!empty($row['parent_number'])) {
            $to_number = $row['parent_number'];
            if (substr($to_number, 0, 1) !== '+') {
                $to_number = '+91' . $to_number; // Assuming Indian numbers
            }

            $message_body = "Hello! Your ward $name (Roll No: {$row['s_rollno']}) has left at $exit_datetime.\nReason: {$row['reason']}.";

            try {
                $message = $client->messages->create(
                    $to_number,
                    ['from' => $twilio_number, 'body' => $message_body]
                );
                echo "📨 SMS sent successfully to parent! SID: " . $message->sid . "<br>";
            } catch (\Twilio\Exceptions\RestException $e) {
                echo "❌ Twilio Error: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "⚠ No parent number found for this student.<br>";
        }

        // --- Delete from request table after success ---
        $delete = $conn->prepare("DELETE FROM request WHERE r_id = ?");
        if ($delete) {
            $delete->bind_param("i", $r_id);
            $delete->execute();
            echo "🗑️ Request record removed after processing.<br>";
        } else {
            echo "⚠ Failed to delete request: " . $conn->error . "<br>";
        }

        echo '<br><button onclick="window.location.href=\'ct_dashboard.php\'">⬅ Back to Dashboard</button>';

    } else {
        echo "⚠ No request found for ID: $r_id.<br>";
    }

} else {
    echo "⚠ Missing parameters (expected: r_id).";
}
?>
