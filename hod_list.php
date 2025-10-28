<?php
include("connection.php");

// --- DELETE ---
if(isset($_GET['delete_id'])){
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM hod WHERE h_id=$id");
    header("Location: hod_list.php");
    exit;
}

// --- ADD ---
if(isset($_POST['add_hod'])){
    $username = $conn->real_escape_string($_POST['h_username']);
    $password = $conn->real_escape_string($_POST['password']);
    $name = $conn->real_escape_string($_POST['h_name']);
    $phno = $conn->real_escape_string($_POST['h_phno']);
    $mail = $conn->real_escape_string($_POST['h_mail']);

    $conn->query("INSERT INTO hod (h_username, password, h_name, h_phno, h_mail) 
                  VALUES ('$username', '$password', '$name', '$phno', '$mail')");
    header("Location: hod_list.php");
    exit;
}

// --- EDIT ---
if(isset($_POST['edit_hod'])){
    $id = intval($_POST['h_id']);
    $username = $conn->real_escape_string($_POST['h_username']);
    $password = $conn->real_escape_string($_POST['password']);
    $name = $conn->real_escape_string($_POST['h_name']);
    $phno = $conn->real_escape_string($_POST['h_phno']);
    $mail = $conn->real_escape_string($_POST['h_mail']);

    $conn->query("UPDATE hod 
                  SET h_username='$username', password='$password', h_name='$name', h_phno='$phno', h_mail='$mail' 
                  WHERE h_id=$id");
    header("Location: hod_list.php");
    exit;
}

// --- FETCH HOD FOR EDIT ---
$editHod = null;
if(isset($_GET['edit_id'])){
    $id = intval($_GET['edit_id']);
    $res = $conn->query("SELECT * FROM hod WHERE h_id=$id");
    $editHod = $res->fetch_assoc();
}

// --- FETCH ALL HODS ---
$hodResult = $conn->query("SELECT * FROM hod");
?>

<!DOCTYPE html>
<html>
<head>
    <title>HOD List</title>
    <style>
        body {font-family: Arial, sans-serif; margin: 20px;}
        table {border-collapse: collapse; width: 100%; margin-bottom: 20px;}
        th, td {border: 1px solid #ccc; padding: 10px; text-align: left;}
        th {background-color: #f2f2f2;}
        h2 {margin-top: 40px;}
        form {margin-top: 20px;}
        input[type="text"], input[type="email"], input[type="password"], input[type="number"] {padding: 5px; width: 250px;}
        input[type="submit"] {padding: 8px 16px; margin-top: 10px;}
        a {text-decoration: none; color: blue;}
    </style>
</head>
<body>

<h1>HOD Table</h1>

<!-- Display Table -->
<table>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Password</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Actions</th>
    </tr>
    <?php if($hodResult && $hodResult->num_rows > 0): ?>
        <?php while($row = $hodResult->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['h_id']; ?></td>
            <td><?php echo htmlspecialchars($row['h_username']); ?></td>
            <td><?php echo htmlspecialchars($row['password']); ?></td>
            <td><?php echo htmlspecialchars($row['h_name']); ?></td>
            <td><?php echo htmlspecialchars($row['h_phno']); ?></td>
            <td><?php echo htmlspecialchars($row['h_mail']); ?></td>
            <td>
                <a href="hod_list.php?edit_id=<?php echo $row['h_id']; ?>">Edit</a> | 
                <a href="hod_list.php?delete_id=<?php echo $row['h_id']; ?>" onclick="return confirm('Delete this HOD?');">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="7">No HODs found.</td></tr>
    <?php endif; ?>
</table>

<!-- Add/Edit Form -->
<h2><?php echo $editHod ? "Edit HOD" : "Add New HOD"; ?></h2>
<form method="post" action="hod_list.php">
    <?php if($editHod): ?>
        <input type="hidden" name="h_id" value="<?php echo $editHod['h_id']; ?>">
    <?php endif; ?>
    Username: <input type="text" name="h_username" value="<?php echo $editHod['h_username'] ?? ''; ?>" required><br><br>
    Password: <input type="password" name="password" value="<?php echo $editHod['password'] ?? ''; ?>" required><br><br>
    Name: <input type="text" name="h_name" value="<?php echo $editHod['h_name'] ?? ''; ?>" required><br><br>
    Phone: <input type="number" name="h_phno" value="<?php echo $editHod['h_phno'] ?? ''; ?>" required><br><br>
    Email: <input type="email" name="h_mail" value="<?php echo $editHod['h_mail'] ?? ''; ?>" required><br><br>
    <input type="submit" name="<?php echo $editHod ? 'edit_hod' : 'add_hod'; ?>" 
           value="<?php echo $editHod ? 'Update HOD' : 'Add HOD'; ?>">
</form>

</body>
</html>