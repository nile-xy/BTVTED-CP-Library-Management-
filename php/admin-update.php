<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $bookId = $_POST['book_id'];
    $bookTitle = $_POST['booktitle'];
    $author = $_POST['author'];
    $studentId = $_POST['student_id'];
    $firstName = $_POST['firstname'];
    $lastName = $_POST['lastname'];
    $email = $_POST['email'];
    $course = $_POST['course'];
    $dateBorrowed = $_POST['date_borrowed'];
    $dateReturned = $_POST['date_returned'];
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    // Select username from tbl_user based on student_id
    $sqlGetUsername = "SELECT username FROM tbl_user WHERE student_id=?";
    if ($stmtGetUsername = $conn->prepare($sqlGetUsername)) {
        // Bind variable to the prepared statement as parameter
        $stmtGetUsername->bind_param("s", $studentId);
        // Execute the prepared statement
        $stmtGetUsername->execute();
        // Bind the result variable
        $stmtGetUsername->bind_result($username);
        // Fetch the result
        $stmtGetUsername->fetch();
        // Close the statement
        $stmtGetUsername->close();

        // Update tbl_book
        $sqlBook = "UPDATE tbl_book SET
            booktitle=?, author=?, student_id=?, firstname=?, lastname=?,
            email=?, course=?, date_borrowed=?, date_returned=?, status=?, remarks=?
            WHERE book_id=?";

        if ($stmtBook = $conn->prepare($sqlBook)) {
            // Bind variables to the prepared statement as parameters
            $stmtBook->bind_param("ssssssssssss", $bookTitle, $author, $studentId, $firstName, $lastName,
                $email, $course, $dateBorrowed, $dateReturned, $status, $remarks, $bookId);

            // Attempt to execute the prepared statement for tbl_book
            if ($stmtBook->execute()) {
                // Update tbl_user
                $sqlUser = "UPDATE tbl_user SET
                    student_id=?, firstname=?, lastname=?, email=?, course=?
                    WHERE username=?";

                if ($stmtUser = $conn->prepare($sqlUser)) {
                    // Bind variables to the prepared statement as parameters
                    $stmtUser->bind_param("ssssss", $studentId, $firstName, $lastName, $email, $course, $username);

                    // Attempt to execute the prepared statement for tbl_user
                    if ($stmtUser->execute()) {
                    } else {
                        echo "Error updating records in tbl_user. Please try again later.";
                    }

                    // Close the statement
                    $stmtUser->close();
                } else {
                    echo "Error in the tbl_user update prepared statement.";
                }
            } else {
                echo "Error updating records in tbl_book. Please try again later.";
            }

            // Close the statement
            $stmtBook->close();
        } else {
            echo "Error in the tbl_book update prepared statement.";
        }

        // Update all rows in tbl_book with the same student_id but different book_id
        $sqlUpdateAll = "UPDATE tbl_book SET
            student_id=?, firstname=?, lastname=?, email=?, course=?
            WHERE student_id=? AND book_id<>?";

        if ($stmtUpdateAll = $conn->prepare($sqlUpdateAll)) {
            // Bind variables to the prepared statement as parameters
            $stmtUpdateAll->bind_param("sssssss", $studentId, $firstName, $lastName, $email, $course, $studentId, $bookId);

            // Attempt to execute the prepared statement
            if ($stmtUpdateAll->execute()) {
                // Records updated successfully
                include 'calculate-fines.php';
                echo "Records updated successfully in tbl_book for all rows with the same student_id.";
                // Records updated successfully for both tbl_book and tbl_user
                header("location: admin-dashboard.php");
                exit();
            } else {
                echo "Error updating records in tbl_book. Please try again later.";
            }

            // Close the statement
            $stmtUpdateAll->close();
        } else {
            echo "Error in the tbl_book update all prepared statement.";
        }
    } else {
        echo "Error in the retrieval of username from tbl_user.";
    }

    // Close connection
    $conn->close();
}
?>
