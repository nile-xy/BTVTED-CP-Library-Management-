<?php
include 'db.php';  // Include your database connection file
session_start();

// Get the student_id from the session
$student_id = $_SESSION['student_id'];

// Query to fetch monthly counts for borrowed and returned books for a specific student_id
$query = "SELECT 
            DATE_FORMAT(date_borrowed, '%Y-%m') AS month,
            SUM(CASE WHEN status = 'Borrowed' THEN 1 ELSE 0 END) AS borrowed,
            SUM(CASE WHEN status = 'Returned' THEN 1 ELSE 0 END) AS returned
          FROM tbl_book
          WHERE student_id = ?
          GROUP BY month
          ORDER BY month";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();

// Fetch the results into an associative array
$data = [];
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Close the database connection
$stmt->close();
$conn->close();

// Set the response header to JSON
header('Content-Type: application/json');

// Output the data as JSON
echo json_encode($data);
?>
