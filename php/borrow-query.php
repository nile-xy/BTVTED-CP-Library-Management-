<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve values from the form
    $bookId = $_POST['book_id'];
    $userId = $_POST['user_id'];
    $course = $_POST['course'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $newAvailable = $_POST['available'];
    $newBorrowed = $_POST['borrowed'];

    // Retrieve additional book details
    $booktitle = $_POST['booktitle'];
    $author = $_POST['author'];
    $bookshelf = $_POST['bookshelf'];
    $quantity = $_POST['quantity'];

    // Use prepared statements to update the database and handle SQL injection
    $updateSql = "UPDATE tbl_inventory SET available = ?, borrowed = ? WHERE book_id = ?";

    // Prepare the statement
    $stmt = $conn->prepare($updateSql);

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("iii", $newAvailable, $newBorrowed, $bookId);

        // Execute the statement
        $result = $stmt->execute();

        if ($result) {
            // Retrieve user details from the session
            session_start();
            if (isset($_SESSION['username'])) {
                $username = $_SESSION['username'];
                $userQuery = "SELECT student_id, course, firstname, lastname, email FROM tbl_user WHERE username = ?";
                $userStmt = $conn->prepare($userQuery);

                if ($userStmt) {
                    $userStmt->bind_param("s", $username);
                    $userStmt->execute();
                    $userResult = $userStmt->get_result();

                    if ($userResult->num_rows > 0) {
                        $userDetails = $userResult->fetch_assoc();
                    } else {
                        // Handle case where user details are not found
                        echo "User details not found.";
                        exit();
                    }

                    $userStmt->close();
                } else {
                    // Handle case where user statement preparation fails
                    echo "Error: " . $conn->error;
                    exit();
                }

                // Insert a new record into tbl_book
                $insertSql = "INSERT INTO tbl_book (student_id, book_id, course, firstname, lastname, email, booktitle, author, bookshelf, quantity, date_borrowed, status, deadline, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP(), 'Borrowed', DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 7 DAY), 'Pending')";

                $insertStmt = $conn->prepare($insertSql);

                if ($insertStmt) {
                    $insertStmt->bind_param("sssssssssi", $userId, $bookId, $course, $firstname, $lastname, $email, $booktitle, $author, $bookshelf, $quantity);
                    $insertResult = $insertStmt->execute();

                    if (!$insertResult) {
                        // Handle error in inserting a new record into tbl_book
                        echo "Error inserting record into tbl_book: " . $insertStmt->error;
                        exit();
                    }

                    $insertStmt->close();
                } else {
                    // Handle error in preparing the insert statement
                    echo "Error preparing insert statement: " . $conn->error;
                    exit();
                }
                echo "Book successfully borrowed!";
                header("location: borrow.php");
                exit();
            } else {
                echo "User not logged in.";
            }
        } else {
            // Error handling for the execution
            echo "Error updating tbl_inventory: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        // Error handling for the prepared statement
        echo "Error preparing statement: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
} else {
    // Handle cases where the request method is not POST
    echo "Invalid request method.";
}
?>
