<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $currentUser = $_SESSION['username'];

    // Update user's account information in the database
    $sql = "UPDATE tbl_admin SET username=?, password=? WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $password, $currentUser);

    if ($stmt->execute()) {
        // Update successful, set the new username in the session
        $_SESSION['username'] = $username;

        // Redirect to the edit account page with success message
        $_SESSION['success_message'] = "Account updated successfully.";
        header('Location: admin-profile.php');
        exit();
    } else {
        // Handle the case where the update fails
        $_SESSION['error_message'] = "Error updating account. Please try again.";
        header('Location: admin-profile.php');
        exit();
    }
}
?>
