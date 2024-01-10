<?php
include 'db.php';

// Function to calculate fines for overdue books
function calculateFine($bookId)
{
    global $conn;

    // Get the deadline and current date
    $deadlineQuery = "SELECT deadline FROM tbl_book WHERE book_id = ?";
    $stmtDeadline = $conn->prepare($deadlineQuery);
    $stmtDeadline->bind_param("i", $bookId);
    $stmtDeadline->execute();
    $stmtDeadline->bind_result($deadline);
    $stmtDeadline->fetch();
    $stmtDeadline->close();

    $currentDate = date('Y-m-d H:i:s');

    // Calculate fine if the book is overdue
    if ($deadline < $currentDate) {
        $daysOverdue = floor((strtotime($currentDate) - strtotime($deadline)) / (60 * 60 * 24));
        $fineAmount = $daysOverdue * 1; 

        // Update the fine column in tbl_book
        $updateFineSql = "UPDATE tbl_book SET fine = ? WHERE book_id = ?";
        $stmtUpdateFine = $conn->prepare($updateFineSql);
        $stmtUpdateFine->bind_param("ii", $fineAmount, $bookId);
        $stmtUpdateFine->execute();
        $stmtUpdateFine->close();
    }
}
?>
