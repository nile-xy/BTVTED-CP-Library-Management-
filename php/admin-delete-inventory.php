<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $book_id = $_POST['book_id'];

    // Prepare a delete statement
    $sql = "DELETE FROM tbl_inventory WHERE book_id=?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("s", $book_id);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records deleted successfully. Redirect to landing page
            header("location: inventory.php");
            exit();
        } else {
            echo "Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    } else {
        echo "Error in the prepared statement.";
    }
}

// Close connection
$conn->close();
?>
