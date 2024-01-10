<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $course = $_POST['course'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    $checkUserSQL = "SELECT * FROM tbl_user WHERE username = ? OR email = ?";
    $checkUserStmt = $conn->prepare($checkUserSQL);
    $checkUserStmt->bind_param("ss", $username, $email);
    $checkUserStmt->execute();
    $userResult = $checkUserStmt->get_result();

    if ($userResult->num_rows > 0) {
        session_start(); 
        $_SESSION['error_message'] = "Username or email already exists.";
        header('Location: registration.php'); 
    } else {
        // Insert the new user into the database
        $insertUserSQL = "INSERT INTO tbl_user (student_id, course, firstname, lastname, username, password, email, age, gender) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertUserStmt = $conn->prepare($insertUserSQL);
        $insertUserStmt->bind_param("sssssssis", $student_id, $course, $firstname, $lastname, $username, $password, $email, $age, $gender);

        if ($insertUserStmt->execute()) {
            session_start();
            $_SESSION['success_message'] = "Registration successful. You can now login.";
            header('Location: registration.php');
        } else {
            session_start();
            $_SESSION['error_message'] = "Error in registration. Please try again.";
            header('Location: registration.php');
        }
    }
}
?>
