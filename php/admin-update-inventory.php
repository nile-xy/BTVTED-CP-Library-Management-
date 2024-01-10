<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $bookId = $_POST['book_id'];
    $bookTitle = $_POST['booktitle'];
    $author = $_POST['author'];
    $bookshelf = $_POST['bookshelf'];
    $quantity = $_POST['quantity'];
    $available = $_POST['quantity'] - $_POST['borrowed'];
    $borrowed = $_POST['borrowed'];

    // Prepare an update statement for tbl_inventory
    $sqlInventory = "UPDATE tbl_inventory SET
    booktitle=?, author=?, bookshelf=?, quantity=?, available=?, borrowed=?
    WHERE book_id=?";

    if ($stmtInventory = $conn->prepare($sqlInventory)) {
    // Bind variables to the prepared statement as parameters
    $stmtInventory->bind_param("ssssiii", $bookTitle, $author, $bookshelf, $quantity, $available, $borrowed, $bookId);

    // Attempt to execute the prepared statement for tbl_inventory
    if ($stmtInventory->execute()) {
        // Records updated successfully for tbl_inventory
        header("location: inventory.php");
        exit();
    } else {
        echo "Error updating records in tbl_inventory. Please try again later.";
    }

    // Close the statement
    $stmtInventory->close();
    } else {
    echo "Error in the tbl_inventory update prepared statement.";
    }


    // Close connection
    $conn->close();
}
?>
