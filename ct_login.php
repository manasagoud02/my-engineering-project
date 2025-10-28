<?php include("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $s_username = $_POST['username'];
    $s_password = $_POST['password'];

    $sql = "SELECT * FROM security WHERE s_username=? AND s_password=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $s_username, $s_password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        header("Location: ct_dashboard.php");
    } else {
        echo "Class Incharge Login failed..!";
    }
}
?>