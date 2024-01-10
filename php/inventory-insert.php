<?php
// Include your database connection file here
require 'db.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $book_id = $_POST['book_id'];
    $booktitle = $_POST['booktitle'];
    $author = $_POST['author'];
    $bookshelf = $_POST['bookshelf'];
    $quantity = $_POST['quantity'];
    $available = $_POST['quantity'];


    // Prepare an insert statement
    $sql = "INSERT INTO tbl_inventory (book_id, booktitle, author, bookshelf, quantity, available) VALUES (?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("ssssii", $book_id, $booktitle, $author, $bookshelf, $quantity, $available);

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            // Records created successfully. Redirect to landing page
            header("location: inventory.php");
            exit();
        } else {
            echo "Something went wrong. Please try again later.";
        }
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
