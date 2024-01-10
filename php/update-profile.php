<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $course = $_POST['course'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $currentUser = $_SESSION['username'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];

    // Update user's account information in the database
    $sql = "UPDATE tbl_user SET student_id=?, course=?, firstname=?, lastname=?, username=?, password=?, email=?, age=?, gender=? WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $student_id, $course, $firstname, $lastname, $username, $password, $email, $age, $gender, $currentUser);

    if ($stmt->execute()) {
        // Update successful, set the new username in the session
        $_SESSION['username'] = $username;

        // Redirect to the edit account page with success message
        $_SESSION['success_message'] = "Account updated successfully.";
        header('Location: profile.php');
        exit();
    } else {
        // Handle the case where the update fails
        $_SESSION['error_message'] = "Error updating account. Please try again.";
        header('Location: profile.php');
        exit();
    }
}
?>
