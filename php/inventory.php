<?php
include 'db.php';
session_start();
if (!isset($_SESSION['username'])) {
    $_SESSION['error_message'] = "You must log in first.";
    header('Location: admin.php');
    exit();
}

// Set default values for filter and pagination
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'all';
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$itemsPerPage = 999; // Set the number of items to show per page

// Calculate the offset for pagination
$offset = ($page - 1) * $itemsPerPage;

// Modify your SQL query to include filtering and pagination
$sql = "SELECT * FROM tbl_inventory";

// Apply filter if it's not set to 'all'
if ($filter !== 'all') {
    $sql .= " WHERE bookshelf = ?";
}

$sql .= " ORDER BY date_borrowed DESC LIMIT ?, ?";

$stmt = $conn->prepare($sql);

// Check if the prepare statement was successful
if (!$stmt) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}

// Bind parameters based on the filter
if ($filter !== 'all') {
    $stmt->bind_param("sii", $filter, $offset, $itemsPerPage);
} else {
    $stmt->bind_param("ii", $offset, $itemsPerPage);
}

function getBookCount($bookshelf)
{
    global $conn;

    $countQuery = "SELECT SUM(quantity) as count FROM tbl_inventory WHERE bookshelf = ?";
    $countStmt = $conn->prepare($countQuery);

    // Check if the prepare statement was successful
    if (!$countStmt) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $countStmt->bind_param("s", $bookshelf);
    $countStmt->execute();
    $countResult = $countStmt->get_result();

    if ($countResult && $countResult->num_rows > 0) {
        $countRow = $countResult->fetch_assoc();
        return $countRow['count'];
    } else {
        return 0;
    }
}


function displayBookshelfCards($conn)
{
    $distinctBookshelvesQuery = "SELECT DISTINCT bookshelf FROM tbl_inventory";
    $distinctBookshelvesResult = $conn->query($distinctBookshelvesQuery);

    // Check if the query was successful
    if ($distinctBookshelvesResult && $distinctBookshelvesResult->num_rows > 0) {
        while ($row = $distinctBookshelvesResult->fetch_assoc()) {
            $bookshelfValue = $row['bookshelf'];

            // Calculate book count for the current bookshelf
            $bookCount = getBookCount($bookshelfValue);

            // Display the card for each bookshelf
            echo "<div class='col-md-6'>";
            echo "<div class='card mb-3'>";
            echo "<div class='card-body'>";
            echo "<h5 class='card-title'><i class='fas fa-book'></i> Bookshelf No. $bookshelfValue</h5>";
            echo "<p class='card-text'>$bookCount books</p>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
        }
    }
}
if (isset($_POST['custom_search'])) {
    $custom_search = '%' . $_POST['custom_search'] . '%';

    // Modify your SQL query to include the customized search condition
    $sql = "SELECT * FROM tbl_inventory WHERE booktitle LIKE ? OR author LIKE ? OR bookshelf LIKE ? ORDER BY date_borrowed DESC LIMIT ?, ?";

    $stmt = $conn->prepare($sql);

    // Check if the prepare statement was successful
    if (!$stmt) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Bind parameters for the search query
    $stmt->bind_param("sssii", $custom_search, $custom_search, $custom_search, $offset, $itemsPerPage);

    // Execute the search query
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    // Use the existing search handling code if the custom search is not triggered
    $stmt->execute();
    $result = $stmt->get_result();
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Library Management System</title>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php 
    include 'announcement-bar.php';
    include 'navigation-bar-dashboard-admin.php';
    ?>
    <div class="dashboard-content">
    <div class="container mt-4">
    <div class="modal fade" id="registrationModal" tabindex="-1" role="dialog" aria-labelledby="registrationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registrationModalLabel">Insert Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="registrationForm" method="post" action="inventory-insert.php">
                    <div class="mb-3">
                        <label for="book_id" class="form-label visually-hidden">Book ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" class="form-control" id="book_id" name="book_id" placeholder="Book ID">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="booktitle" class="form-label visually-hidden">Book Title</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-book"></i></span>
                            <input type="text" class="form-control" id="booktitle" name="booktitle" placeholder="Book Title">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="author" class="form-label visually-hidden">Author</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-pen"></i></span>
                            <input type="text" class="form-control" id="author" name="author" placeholder="Author">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="bookshelf" class="form-label visually-hidden">Bookshelf No.</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-book-open"></i></span>
                            <input type="text" class="form-control" id="bookshelf" name="bookshelf" placeholder="Bookshelf No.">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label visually-hidden">Quantity</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                            <input type="number" class="form-control" id="quantity" name="quantity" placeholder="Quantity">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="available" class="form-label visually-hidden">Available Books</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                            <input type="number" class="form-control" id="available" name="available" placeholder="Available Books" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="borrowed" class="form-label visually-hidden">Borrowed Books</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-exchange-alt"></i></span>
                            <input type="number" class="form-control" id="borrowed" name="borrowed" placeholder="Borrowed Books" disabled>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Insert Book</button>
                </form>
            </div>
        </div>
    </div>
</div>


    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm" method="post" action="admin-update-inventory.php" onsubmit="enableDisabledFields()">
                    <div class="mb-3">
                        <label for="update_book_id" class="form-label visually-hidden">Book ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                            <input type="text" class="form-control" id="update_book_id" name="book_id" placeholder="Book ID">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_booktitle" class="form-label visually-hidden">Book Title</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-book"></i></span>
                            <input type="text" class="form-control" id="update_booktitle" name="booktitle" placeholder="Book Title">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_author" class="form-label visually-hidden">Author</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-pen"></i></span>
                            <input type="text" class="form-control" id="update_author" name="author" placeholder="Author">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_bookshelf" class="form-label visually-hidden">Bookshelf No.</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-book-open"></i></span>
                            <input type="text" class="form-control" id="update_bookshelf" name="bookshelf" placeholder="Bookshelf No.">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_quantity" class="form-label visually-hidden">Quantity</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                            <input type="number" class="form-control" id="update_quantity" name="quantity" placeholder="Quantity">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_available" class="form-label visually-hidden">Available Books</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-check"></i></span>
                            <input type="number" class="form-control" id="update_available" name="available" placeholder="Available Books:" disabled>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_borrowed" class="form-label visually-hidden">Borrowed Books</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-exchange-alt"></i></span>
                            <input type="number" class="form-control" id="update_borrowed" name="borrowed" placeholder="Borrowed Books" disabled>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function enableDisabledFields() {
        // Enable the disabled fields before form submission
        document.getElementById('update_available').disabled = false;
        document.getElementById('update_borrowed').disabled = false;
    }
</script>
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                <h5 class="">Welcome! <b><?php echo $_SESSION['username']; ?></b><span style="padding: 0 10px 0 20px;"></span></h5>
                </div>
                <h2>Inventory</h2>
            </div>
        </div>
            <div class="container-fluid">
            <div class="row mb-3 mt-3 justify-content-center">
                <div class="col-md-4">
                    <div class="row">
                        <?php displayBookshelfCards($conn); ?>
                    </div>
                </div>
                <div class="col-md-8">
                    <canvas id="barChart" width="400" height="200"></canvas>
                </div>
            </div>
            <form method="post" action="inventory.php" class="mb-3">
                <div class="row g-3">
                    <div class="col-auto">
                        <label for="filter" class="form-label">Filter by Bookshelf:</label>
                    </div>
                    <div class="col-auto">
                    <select name="filter" id="filter" class="form-select">
                        <option value="all" <?php echo ($filter === 'all') ? 'selected' : ''; ?>>All</option>
                        <?php
                        $distinctBookshelvesQuery = "SELECT DISTINCT bookshelf FROM tbl_inventory";
                        $distinctBookshelvesResult = $conn->query($distinctBookshelvesQuery);

                        // Check if the query was successful
                        if ($distinctBookshelvesResult && $distinctBookshelvesResult->num_rows > 0) {
                            while ($row = $distinctBookshelvesResult->fetch_assoc()) {
                                $bookshelfValue = $row['bookshelf'];
                                echo "<option value=\"$bookshelfValue\" " . ($filter === $bookshelfValue ? 'selected' : '') . ">$bookshelfValue</option>";
                            }
                        }
                        ?>
                    </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </div>
            </form>
            <div class="row mb-3">
                <div class="col-md-12">
                <form method="post" action="inventory.php" class="mb-3">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search..." name="custom_search">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                </div>
            </div>
                <div class="row">
                    <div class="col-md-12">
                    <button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#registrationModal">
                        Insert Book
                    </button>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Book ID</th>
                                    <th scope="col">Bookshelf No.</th>
                                    <th scope="col">Book Title</th>
                                    <th scope="col">Author</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Available</th>
                                    <th scope="col">Borrowed</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row["book_id"] . "</td>";
                                        echo "<td>" . $row["bookshelf"] . "</td>";
                                        echo "<td>" . $row["booktitle"] . "</td>";
                                        echo "<td>" . $row["author"] . "</td>";
                                        echo "<td>" . $row["quantity"] . "</td>";
                                        echo "<td>" . $row["available"] . "</td>";
                                        echo "<td>" . $row["borrowed"] . "</td>";
                                        echo '<td width="200px">
                                                <button class="btn btn-primary update-btn" data-toggle="modal" data-target="#updateModal" data-book-id="' . $row["book_id"] . '">Update</button>&nbsp;
                                                <form method="post" action="admin-delete-inventory.php" style="display:inline;">
                                                    <input type="hidden" name="book_id" value="' . $row["book_id"] . '">
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this record?\')">Delete</button>
                                                </form>
                                            </td>';
                                        echo "</tr>";
                                    }
                                }
                                $stmt->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 mt-3">
                    <?php
                    // Display pagination links
                    $totalPages = ceil($result->num_rows / $itemsPerPage);

                    if ($totalPages > 1) {
                        echo "<p class='mb-1'>Page $page of $totalPages</p>";
                        echo "<div class='btn-group' role='group'>";

                        if ($page > 1) {
                            echo "<a href='dashboard.php?page=" . ($page - 1) . "&filter=$filter' class='btn btn-secondary'>Previous</a>";
                        }

                        if ($page < $totalPages) {
                            echo "<a href='dashboard.php?page=" . ($page + 1) . "&filter=$filter' class='btn btn-secondary'>Next</a>";
                        }

                        echo "</div>";
                    }
                    ?>
                    </div>
                </div>
            </div>
        </div>
</div>
    <?php 
    include 'footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
    <script src="../js/dashboard-chart-inventory.js"></script>
<script>
    $('.update-btn').click(function () {
        var bookId = $(this).data('book-id');
        var bookshelf = $(this).closest('tr').find('td:eq(1)').text();
        var bookTitle = $(this).closest('tr').find('td:eq(2)').text();
        var author = $(this).closest('tr').find('td:eq(3)').text();
        var quantity = $(this).closest('tr').find('td:eq(4)').text();
        var available = $(this).closest('tr').find('td:eq(5)').text();
        var borrowed = $(this).closest('tr').find('td:eq(6)').text();


        // Populate values in the update modal form
        $('#update_book_id').val(bookId);
        $('#update_bookshelf').val(bookshelf);
        $('#update_booktitle').val(bookTitle);
        $('#update_author').val(author);
        $('#update_quantity').val(quantity);
        $('#update_available').val(available);
        $('#update_borrowed').val(borrowed);

        // Show the update modal
        $('#updateModal').modal('show');
    });
</script>

</body>
</html>