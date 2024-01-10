<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve values from the form
    $bookId = $_POST['book_id'];
    $newAvailable = $_POST['available'];
    $newBorrowed = $_POST['borrowed'];

    echo $newAvailable;
    echo $newBorrowed;

    // Use prepared statements to update the database and handle SQL injection
    $updateInventorySql = "UPDATE tbl_inventory SET available = ?, borrowed = ? WHERE book_id = ?";

    // Prepare the statement for updating inventory
    $stmtUpdateInventory = $conn->prepare($updateInventorySql);

    if ($stmtUpdateInventory) {
        // Bind parameters
        $stmtUpdateInventory->bind_param("iii", $newAvailable, $newBorrowed, $bookId);

        // Execute the statement
        $resultUpdateInventory = $stmtUpdateInventory->execute();

        if ($resultUpdateInventory) {
            // Update the book status in tbl_book to mark it as returned
            $updateBookSql = "UPDATE tbl_book SET status = 'Returned', date_returned = CURRENT_TIMESTAMP(), remarks = ";

            // Check if the deadline is past the current date
            $updateBookSql .= "CASE WHEN deadline < CURRENT_TIMESTAMP() THEN 'Late' ELSE 'On Time' END ";

            $updateBookSql .= "WHERE book_id = ?";

            // Prepare the statement for updating book status
            $stmtUpdateBook = $conn->prepare($updateBookSql);

            if ($stmtUpdateBook) {
                // Bind parameters
                $stmtUpdateBook->bind_param("i", $bookId);

                // Execute the statement
                $resultUpdateBook = $stmtUpdateBook->execute();

                if ($resultUpdateBook) {
                    // Calculate and update fines
                    include 'calculate-fines.php';
                    calculateFine($bookId);

                    echo "Book successfully returned!";
                    header("location: return.php");
                    exit();
                } else {
                    // Handle error in updating book status
                    echo "Error updating book status in tbl_book: " . $stmtUpdateBook->error;
                }

                // Close the statement for updating book status
                $stmtUpdateBook->close();
            } else {
                // Handle error in preparing the update statement for book status
                echo "Error preparing update statement for book status: " . $conn->error;
            }
        } else {
            // Error handling for the execution of updating inventory
            echo "Error updating tbl_inventory: " . $stmtUpdateInventory->error;
        }

        // Close the statement for updating inventory
        $stmtUpdateInventory->close();
    } else {
        // Error handling for the prepared statement for updating inventory
        echo "Error preparing statement for updating inventory: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
} else {
    // Handle cases where the request method is not POST
    echo "Invalid request method.";
}
?>
