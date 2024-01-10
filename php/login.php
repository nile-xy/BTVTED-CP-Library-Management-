<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM tbl_user WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        session_start();
        $_SESSION['username'] = $user['username'];
        $_SESSION['student_id'] = $user['student_id'];
        header('Location: dashboard.php');
    } else {
        session_start();
        $_SESSION['error_message'] = "Invalid email or password.";
        header('Location: index.php');
    }
}
?>
