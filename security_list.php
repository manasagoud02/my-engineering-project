<?php
include("connection.php");

// --- DELETE ---
if(isset($_GET['delete_username'])){
    $username = $conn->real_escape_string($_GET['delete_username']);
    $conn->query("DELETE FROM security WHERE s_username='$username'");
    header("Location: security_list.php");
    exit;
}

// --- ADD ---
if(isset($_POST['add_security'])){
    $username = $conn->real_escape_string($_POST['s_username']);
    $password = $conn->real_escape_string($_POST['s_password']);
    $conn->query("INSERT INTO security (s_username, s_password) VALUES ('$username', '$password')");
    header("Location: security_list.php");
    exit;
}

// --- EDIT ---
if(isset($_POST['edit_security'])){
    $old_username = $conn->real_escape_string($_POST['old_username']);
    $username = $conn->real_escape_string($_POST['s_username']);
    $password = $conn->real_escape_string($_POST['s_password']);
    $conn->query("UPDATE security SET s_username='$username', s_password='$password' WHERE s_username='$old_username'");
    header("Location: security_list.php");
    exit;
}

// --- FETCH SECURITY FOR EDIT ---
$editSec = null;
if(isset($_GET['edit_username'])){
    $username = $conn->real_escape_string($_GET['edit_username']);
    $res = $conn->query("SELECT * FROM security WHERE s_username='$username'");
    $editSec = $res->fetch_assoc();
}

// --- FETCH ALL SECURITY USERS ---
$secResult = $conn->query("SELECT * FROM security");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Security Table</title>
    <style>
        body {font-family: Arial, sans-serif; margin: 20px;}
        table {border-collapse: collapse; width: 100%; margin-bottom: 20px;}
        th, td {border: 1px solid #ccc; padding: 10px; text-align: left;}
        th {background-color: #f2f2f2;}
        h2 {margin-top: 40px;}
        form {margin-top: 20px;}
        input[type="text"], input[type="number"] {padding: 5px; width: 250px;}
        input[type="submit"] {padding: 8px 16px; margin-top: 10px;}
        a {text-decoration: none; color: blue;}
    </style>
</head>
<body>

<h1>Security Table</h1>

<!-- Display Table -->
<table>
    <tr>
        <th>Username</th>
        <th>Password</th>
        <th>Actions</th>
    </tr>
    <?php while($row = $secResult->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['s_username']); ?></td>
        <td><?php echo htmlspecialchars($row['s_password']); ?></td>
        <td>
            <a href="security_list.php?edit_username=<?php echo urlencode($row['s_username']); ?>">Edit</a> | 
            <a href="security_list.php?delete_username=<?php echo urlencode($row['s_username']); ?>" onclick="return confirm('Delete this Security User?');">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

<!-- Add/Edit Form -->
<h2><?php echo $editSec ? "Edit Security User" : "Add New Security User"; ?></h2>
<form method="post" action="security_list.php">
    <?php if($editSec): ?>
        <input type="hidden" name="old_username" value="<?php echo $editSec['s_username']; ?>">
    <?php endif; ?>
    Username: <input type="text" name="s_username" value="<?php echo $editSec['s_username'] ?? ''; ?>" required><br><br>
    Password: <input type="number" name="s_password" value="<?php echo $editSec['s_password'] ?? ''; ?>" required><br><br>
    <input type="submit" name="<?php echo $editSec ? 'edit_security' : 'add_security'; ?>" 
           value="<?php echo $editSec ? 'Update Security User' : 'Add Security User'; ?>">
</form>

</body>
</html>