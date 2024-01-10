<?php
include 'db.php';

$barChartData = array();

// Fetch distinct bookshelves and their total count of books
$distinctBookshelvesQuery = "SELECT bookshelf, SUM(quantity) AS total_books, SUM(available) AS total_available, SUM(borrowed) AS total_borrowed FROM tbl_inventory GROUP BY bookshelf";
$distinctBookshelvesResult = $conn->query($distinctBookshelvesQuery);

if ($distinctBookshelvesResult && $distinctBookshelvesResult->num_rows > 0) {
    while ($row = $distinctBookshelvesResult->fetch_assoc()) {
        $barChartData[] = array(
            'bookshelf' => $row['bookshelf'],
            'total_books' => $row['total_books'],
            'total_available' => $row['total_available'],
            'total_borrowed' => $row['total_borrowed']
        );
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($barChartData);
exit();
?>
