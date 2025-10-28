<?php include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $h_username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM hod WHERE h_username=? AND password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $h_username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        header("Location: hod_dashboard.php");
    } else {
        echo "HOD Login failed..!";
    }
}
?>