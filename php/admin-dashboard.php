<?php
include 'db.php';
session_start();
if (!isset($_SESSION['username'])) {
    $_SESSION['error_message'] = "You must log in first.";
    header('Location: admin.php');
    exit();
}

// Set default values for filter and pagination
$itemsPerPage = isset($_POST['itemsPerPage']) ? $_POST['itemsPerPage'] : 20;
$filter = isset($_POST['filter']) ? $_POST['filter'] : 'all';
$page = isset($_GET['page']) ? $_GET['page'] : 1;

// Calculate the offset for pagination
$offset = ($page - 1) * $itemsPerPage;

// Check if the search form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['search'])) {
    $search = '%' . $_POST['search'] . '%';

    $sql = "SELECT * FROM tbl_book WHERE 
            book_id LIKE ? OR
            booktitle LIKE ? OR
            author LIKE ? OR
            student_id LIKE ? OR
            firstname LIKE ? OR
            lastname LIKE ? OR
            email LIKE ? OR
            course LIKE ? OR
            date_borrowed LIKE ? OR
            date_returned LIKE ? OR
            status LIKE ? OR
            remarks LIKE ?
            ORDER BY date_borrowed DESC LIMIT ?, ?";

    $stmt = $conn->prepare($sql);

    // Check if the prepare statement was successful
    if (!$stmt) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    // Bind parameters for search
    $stmt->bind_param(
        "ssssssssssssii",
        $search,
        $search,
        $search,
        $search,
        $search,
        $search,
        $search,
        $search,
        $search,
        $search,
        $search,
        $search,
        $offset,
        $itemsPerPage
    );
} else {
    // If search form is not submitted, use the existing query with filter
    $sql = "SELECT * FROM tbl_book";

    // Apply filter if it's not set to 'all'
    if ($filter !== 'all') {
        $sql .= " WHERE status = ?";
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
}

$stmt->execute();
$result = $stmt->get_result();

function getBookCount($status)
{
    global $conn;

    $countQuery = "SELECT COUNT(*) as count FROM tbl_book WHERE status = ?";
    $countStmt = $conn->prepare($countQuery);

    // Check if the prepare statement was successful
    if (!$countStmt) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $countStmt->bind_param("s", $status);
    $countStmt->execute();
    $countResult = $countStmt->get_result();

    if ($countResult && $countResult->num_rows > 0) {
        $countRow = $countResult->fetch_assoc();
        return $countRow['count'];
    } else {
        return 0;
    }
}

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
    <div class="modal fade" id="updateModal" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm" method="post" action="admin-update.php">
                    <div class="mb-3">
                        <label for="update_book_id" class="form-label visually-hidden">Book ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-book"></i></span>
                            <input type="text" class="form-control" id="update_book_id" name="book_id">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_booktitle" class="form-label visually-hidden">Book Title</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-book-open"></i></span>
                            <input type="text" class="form-control" id="update_booktitle" name="booktitle">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_author" class="form-label visually-hidden">Author</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-pen"></i></span>
                            <input type="text" class="form-control" id="update_author" name="author">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_student_id" class="form-label visually-hidden">Student ID</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                            <input type="text" class="form-control" id="update_student_id" name="student_id">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="input-group">
                            <label for="update_firstname" class="form-label visually-hidden">First name</label>
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="update_firstname" name="firstname">
        
                            <label for="update_lastname" class="form-label visually-hidden">Last name</label>
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" class="form-control" id="update_lastname" name="lastname">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_email" class="form-label visually-hidden">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="update_email" name="email">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_course" class="form-label visually-hidden">Course</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span> 
                            <select class="form-control" id="update_course" name="course">
                                <option value="NONE"></option>
                                <option value="Bachelor of Arts in English Language">Bachelor of Arts in English Language</option>
                                <option value="Bachelor of Arts in Social Science">Bachelor of Arts in Social Science</option>
                                <option value="Bachelor of Public Administration">Bachelor of Public Administration</option>
                                <option value="Bachelor of Science in Applied Mathematics">Bachelor of Science in Applied Mathematics</option>
                                <option value="Bachelor of Science in Psychology">Bachelor of Science in Psychology</option>
                                <option value="Bachelor of Science in Accountancy">Bachelor of Science in Accountancy</option>
                                <option value="Bachelor of Science in Business Administration major in Financial Management">Bachelor of Science in Business Administration major in Financial Management</option>
                                <option value="Bachelor of Science in Entrepreneurship">Bachelor of Science in Entrepreneurship</option>
                                <option value="Bachelor of Science in Hospitality Management">Bachelor of Science in Hospitality Management</option>
                                <option value="Bachelor of Science in Management Accounting">Bachelor of Science in Management Accounting</option>
                                <option value="Bachelor of Science in Office Administration">Bachelor of Science in Office Administration</option>
                                <option value="Bachelor of Science in Information Systems">Bachelor of Science in Information Systems</option>
                                <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                                <option value="Bachelor of Science in Criminology">Bachelor of Science in Criminology</option>
                                <option value="Bachelor of Early Childhood Education">Bachelor of Early Childhood Education</option>
                                <option value="Bachelor of Elementary Education">Bachelor of Elementary Education</option>
                                <option value="Bachelor of Physical Education">Bachelor of Physical Education</option>
                                <option value="Bachelor of Secondary Education with major in English">Bachelor of Secondary Education with major in English</option>
                                <option value="Bachelor of Secondary Education with major in Filipino">Bachelor of Secondary Education with major in Filipino</option>
                                <option value="Bachelor of Secondary Education with major in Mathematics">Bachelor of Secondary Education with major in Mathematics</option>
                                <option value="Bachelor of Secondary Education with major in Science">Bachelor of Secondary Education with major in Science</option>
                                <option value="Bachelor of Special Needs Education">Bachelor of Special Needs Education</option>
                                <option value="Bachelor of Technology and Livelihood Education major in Home Economics">Bachelor of Technology and Livelihood Education major in Home Economics</option>
                                <option value="Bachelor of Technology and Livelihood Education major in Home Industrial Arts">Bachelor of Technology and Livelihood Education major in Home Industrial Arts</option>
                                <option value="Bachelor of Technical Vocational Teacher Education major in Electrical Technology">Bachelor of Technical Vocational Teacher Education major in Electrical Technology</option>
                                <option value="Bachelor of Technical Vocational Teacher Education major in Electronics Technology">Bachelor of Technical Vocational Teacher Education major in Electronics Technology</option>
                                <option value="Bachelor of Science in Civil Engineering">Bachelor of Science in Civil Engineering</option>
                                <option value="Bachelor of Science in Computer Engineering">Bachelor of Science in Computer Engineering</option>
                                <option value="Bachelor of Science in Electronics Engineering">Bachelor of Science in Electronics Engineering</option>
                                <option value="Bachelor of Science in Industrial Technology major in Architectural Drafting Technology">Bachelor of Science in Industrial Technology major in Architectural Drafting Technology</option>
                                <option value="Bachelor of Science in Industrial Technology major in Architectural Automotive Technology">Bachelor of Science in Industrial Technology major in Architectural Automotive Technology</option>
                                <option value="Bachelor of Science in Industrial Technology major in Architectural Computer Technology">Bachelor of Science in Industrial Technology major in Architectural Computer Technology</option>
                                <option value="Bachelor of Science in Industrial Technology major in Architectural Electrical Technology">Bachelor of Science in Industrial Technology major in Architectural Electrical Technology</option>
                                <option value="Bachelor of Science in Industrial Technology major in Architectural Electronics Technology">Bachelor of Science in Industrial Technology major in Architectural Electronics Technology</option>
                                <option value="Bachelor of Science in Industrial Technology major in Architectural Fashion and Apparel Technology">Bachelor of Science in Industrial Technology major in Architectural Fashion and Apparel Technology</option>
                                <option value="Bachelor of Science in Industrial Technology major in Architectural Food Trades Technology">Bachelor of Science in Industrial Technology major in Architectural Food Trades Technology</option>
                                <option value="Bachelor of Science in Industrial Technology major in Architectural Mechanical Technology">Bachelor of Science in Industrial Technology major in Architectural Mechanical Technology</option>
                                <option value="Bachelor of Science in Industrial Technology major in Refrigeration and Air-conditioning Technology">Bachelor of Science in Industrial Technology major in Refrigeration and Air-conditioning Technology</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_date_borrowed" class="form-label visually-hidden">Date Borrowed</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            <input type="text" class="form-control" id="update_date_borrowed" name="date_borrowed">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_date_returned" class="form-label visually-hidden">Date Returned</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                            <input type="text" class="form-control" id="update_date_returned" name="date_returned">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_status" class="form-label visually-hidden">Status</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-circle"></i></span>
                            <select class="form-control" id="update_status" name="status">
                                <option value="Borrowed">Borrowed</option>
                                <option value="Returned">Returned</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="update_remarks" class="form-label visually-hidden">Remarks</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-comment"></i></span>
                            <select class="form-control" id="update_remarks" name="remarks">
                                <option value="On Time">On Time</option>
                                <option value="Late">Late</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-end">
                <h5 class="">Welcome! <b><?php echo $_SESSION['username']; ?></b><span style="padding: 0 10px 0 20px;"></span></h5>
                </div>
                <h2>Dashboard</h2>
            </div>
        </div>
            <div class="container-fluid">
            <div class="row mb-3 mt-3 justify-content-center">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-book"></i> Borrowed Books</h5>
                            <p class="card-text">
                                <?php echo getBookCount('borrowed'); ?>
                            </p>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-undo-alt"></i> Returned Books</h5>
                            <p class="card-text">
                                <?php echo getBookCount('returned'); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <canvas id="lineChart" width="400" height="200"></canvas>
                </div>
            </div>
            <form method="post" action="admin-dashboard.php" class="mb-3">
                <input type="hidden" name="itemsPerPage" value="<?php echo $itemsPerPage; ?>">
                <div class="row g-3">
                    <div class="col-auto">
                        <label for="filter" class="form-label">Filter by Status:</label>
                    </div>
                    <div class="col-auto">
                        <select name="filter" id="filter" class="form-select">
                            <option value="all" <?php echo ($filter === 'all') ? 'selected' : ''; ?>>All</option>
                            <option value="borrowed" <?php echo ($filter === 'borrowed') ? 'selected' : ''; ?>>Borrowed</option>
                            <option value="returned" <?php echo ($filter === 'returned') ? 'selected' : ''; ?>>Returned</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                    </div>
                </div>
            </form>
            <div class="row mb-3">
                <div class="col-md-12">
                    <form method="post" action="admin-dashboard.php" class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search..." name="search">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Book ID</th>
                                    <th scope="col">Book Title</th>
                                    <th scope="col">Author</th>
                                    <th scope="col">Student ID</th>
                                    <th scope="col">First Name</th>
                                    <th scope="col">Last Name</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Course</th>
                                    <th scope="col">Date Borrowed</th>
                                    <th scope="col">Date Returned</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Remarks</th>
                                    <th scope="col">Fines</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row["book_id"] . "</td>";
                                        echo "<td>" . $row["booktitle"] . "</td>";
                                        echo "<td>" . $row["author"] . "</td>";
                                        echo "<td>" . $row["student_id"] . "</td>";
                                        echo "<td>" . $row["firstname"] . "</td>";
                                        echo "<td>" . $row["lastname"] . "</td>";
                                        echo "<td>" . $row["email"] . "</td>";
                                        echo "<td>" . $row["course"] . "</td>";
                                        echo "<td>" . $row["date_borrowed"] . "</td>";
                                        echo "<td>" . $row["date_returned"] . "</td>";
                                        echo "<td>" . $row["status"] . "</td>";
                                        echo "<td>" . $row["remarks"] . "</td>";
                                        echo "<td>" . $row["fine"] . "</td>";
                                        echo '<td width="200px">
                                                <button class="btn btn-primary update-btn" data-toggle="modal" data-target="#updateModal" data-book-id="' . $row["book_id"] . '">Update</button>&nbsp;
                                                <form method="post" action="admin-delete.php" style="display:inline;">
                                                    <input type="hidden" name="book_id" value="' . $row["book_id"] . '">
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm(\'Are you sure you want to delete this record?\')">Delete</button>
                                                </form>
                                            </td>';
                                        echo "</tr>";
                                    }
                                }
                                $stmt->close();
                                ?>
                                <div class="row">
                                    <div class="col-md-12 mt-3">
                                    <?php
                                    $totalPages = ceil(getBookCount($filter) / $itemsPerPage);

                                    if ($totalPages > 1) {
                                        echo "<p class='mb-1'>Page $page of $totalPages</p>";
                                        echo "<div class='btn-group' role='group'>";
                                    
                                        if ($page > 1) {
                                            echo "<a href='admin-dashboard.php?page=" . ($page - 1) . "&filter=$filter' class='btn btn-secondary'>Previous</a>";
                                        }
                                    
                                        if ($page < $totalPages) {
                                            echo "<a href='admin-dashboard.php?page=" . ($page + 1) . "&filter=$filter' class='btn btn-secondary'>Next</a>";
                                        }
                                    
                                        echo "</div>";
                                    }
                                    ?>
                                    </div>
                                </div>
                            </tbody>
                        </table>
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
    <script src="../js/dashboard-chart-admin.js"></script>
<script>
    $('.update-btn').click(function () {
        var bookId = $(this).data('book-id');
        var bookTitle = $(this).closest('tr').find('td:eq(1)').text();
        var author = $(this).closest('tr').find('td:eq(2)').text();
        var studentId = $(this).closest('tr').find('td:eq(3)').text();
        var firstName = $(this).closest('tr').find('td:eq(4)').text();
        var lastName = $(this).closest('tr').find('td:eq(5)').text();
        var email = $(this).closest('tr').find('td:eq(6)').text();
        var course = $(this).closest('tr').find('td:eq(7)').text();
        var dateBorrowed = $(this).closest('tr').find('td:eq(8)').text();
        var dateReturned = $(this).closest('tr').find('td:eq(9)').text();
        var status = $(this).closest('tr').find('td:eq(10)').text();
        var remarks = $(this).closest('tr').find('td:eq(11)').text();

        // Populate values in the update modal form
        $('#update_book_id').val(bookId);
        $('#update_booktitle').val(bookTitle);
        $('#update_author').val(author);
        $('#update_student_id').val(studentId);
        $('#update_firstname').val(firstName);
        $('#update_lastname').val(lastName);
        $('#update_email').val(email);
        $('#update_course').val(course);
        $('#update_date_borrowed').val(dateBorrowed);
        $('#update_date_returned').val(dateReturned);
        $('#update_status').val(status);
        $('#update_remarks').val(remarks);

        // Show the update modal
        $('#updateModal').modal('show');
    });
</script>

</body>
</html>