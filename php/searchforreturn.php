<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['error_message'] = "You must log in first.";
    header('Location: index.php');
    exit();
}

// Fetch user details
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

// Check if the search parameter is set
if (isset($_GET['search'])) {
    $searchTerm = '%' . $_GET['search'] . '%';
    // Modify the SQL query to include the search condition
    $sql = "SELECT b.*, i.quantity, i.borrowed, i.available FROM tbl_book b
            JOIN tbl_inventory i ON b.book_id = i.book_id
            WHERE b.status = 'Borrowed' AND b.student_id = ? AND (i.booktitle LIKE ? OR i.author LIKE ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("iss", $userDetails['student_id'], $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $books[] = $row;
            }
        } else {
            // Handle case where no books match the search
            $searchMessage = "No books found matching the search criteria.";
        }

        $stmt->close();
    } else {
        // Handle case where statement preparation fails
        echo "Error: " . $conn->error;
        exit();
    }
} else {
    // If no search parameter, fetch all borrowed books as before
    $sql = "SELECT b.*, i.quantity, i.borrowed, i.available FROM tbl_book b
            JOIN tbl_inventory i ON b.book_id = i.book_id
            WHERE b.status = 'Borrowed' AND b.student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userDetails['student_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $books = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $books[] = $row;
        }
    }
}

// Color schemes
$colorSchemes = ['#0C356A', '#0174BE', '#FFC436', '#E1AA74', '#29ADB2'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css" async>
    <title>Library Management System</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Adjust vertical alignment of image */
        .card-img {
            object-fit: cover;
            height: 100%;
        }

        .card-borrow {
            transition: background-color 0.3s ease; /* Add a smooth transition effect */
        }

        .card-borrow:hover {
            background-color: darken(<?php echo $color ?? ''; ?>, 20%); /* Adjust the percentage as needed */
        }
    </style>
</head>
<body>
    <?php 
    include 'announcement-bar.php';
    include 'navigation-bar-dashboard.php';
    ?>

    <div class="container text-center mt-5 mb-5">
        <h2>Return Books</h2>
        <form class="form-inline mt-3 mb-3" method="GET" action="searchforreturn.php">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search books..." name="search">
                <button class="btn btn-outline-primary" type="submit">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
        <div class="container-fluid">
            <!-- Use container-fluid to make the container full-width -->
            <div class="card" id="book-card-container">
                <div class="card-header">
                    All Borrowed Books
                </div>
                <div class="card-body mt-4 mb-4">
                    <div class="row">
                        <?php if (!empty($books)): ?>
                            <!-- Inside the loop where you display books -->
                            <?php foreach ($books as $index => $book): ?>
                                <?php
                                    // Calculate color index using modulo to loop through color schemes
                                    $colorIndex = $index % count($colorSchemes);
                                    $color = $colorSchemes[$colorIndex];
                                    // Calculate image index using modulo to loop through the three available images (0.png, 1.png, 2.png)
                                    $imageIndex = $index % 4;
                                    $imagePath = "../img/{$imageIndex}.png";
                                ?>
                                <div class="col-md-3 mb-4 card-col">
                                    <!-- Wrap the card content in an anchor tag -->
                                    <a href="#" class="card h-100 shadow card-borrow text-decoration-none"
                                        style="background-color: <?php echo $color; ?>; color: white; border: none;"
                                        data-bs-toggle="modal" data-bs-target="#returnModal<?php echo $index; ?>"
                                        data-book-id="<?php echo $book['book_id']; ?>"
                                        data-available="<?php echo $book['available']; ?>"
                                        data-borrowed="<?php echo $book['borrowed']; ?>"
                                    >
                                    <!-- Nested Row for Image and Text -->
                                    <div class="row h-100 align-items-center">
                                        <!-- Image Column -->
                                        <div class="col-md-5">
                                            <img src="<?php echo $imagePath; ?>" alt="Book Image" class="img-fluid card-img ms-4 card-img-borrow">
                                        </div>
                                        <!-- Text Column -->
                                        <div class="col-md-7">
                                            <div class="card-body">
                                                <h5 class="card-title text-end me-2 text-decoration-none" style="font-size: 1.2rem;"><?php echo $book['booktitle']; ?></h5>
                                                <p class="card-text text-end me-3 text-decoration-none" style="font-size: 0.9rem;">
                                                    <strong><i class="fas fa-pen"></i></strong> <?php echo $book['author']; ?><br>
                                                    <strong><i class="fas fa-book-open"></i></strong> <?php echo $book['bookshelf']; ?><br>
                                                    <strong><i class="fas fa-box"></i></strong> <?php echo $book['quantity']; ?><br>
                                                    <strong><i class="fas fa-users"></i></strong> <?php echo $book['borrowed']; ?><br>
                                                    <strong><i class="fas fa-check-circle"></i></strong> <?php echo $book['available']; ?><br>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </a>

                                     <!-- Modal for Return Confirmation -->
                                     <div class="modal fade" id="returnModal<?php echo $index; ?>" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="borrowModalLabel">Return Confirmation</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h3 class="mb-3">Do you want to return "<?php echo $book['booktitle']; ?>" from Bookshelf No. "<?php echo $book['bookshelf']; ?>" to the library?</h3>

                                                    <!-- Display user details -->
                                                    <p>User Details:</p>
                                                    <p><strong>Student ID:</strong> <?php echo $userDetails['student_id']; ?></p>
                                                    <p><strong>Course:</strong> <?php echo $userDetails['course']; ?></p>
                                                    <p><strong>Name:</strong> <?php echo $userDetails['firstname'] . ' ' . $userDetails['lastname']; ?></p>
                                                    <p><strong>Email:</strong> <?php echo $userDetails['email']; ?></p>

                                                    <form id="returnForm<?php echo $index; ?>" method="post" action="return-query.php">
                                                        <!-- Hidden input fields to store book details -->
                                                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                        <input type="hidden" name="available" value="<?php echo $book['available']; ?>">
                                                        <input type="hidden" name="borrowed" value="<?php echo $book['borrowed']; ?>">
                                                        
                                                        <!-- Additional hidden input fields for user details -->
                                                        <input type="hidden" name="user_id" value="<?php echo $userDetails['student_id']; ?>">
                                                        <input type="hidden" name="course" value="<?php echo $userDetails['course']; ?>">
                                                        <input type="hidden" name="firstname" value="<?php echo $userDetails['firstname']; ?>">
                                                        <input type="hidden" name="lastname" value="<?php echo $userDetails['lastname']; ?>">
                                                        <input type="hidden" name="email" value="<?php echo $userDetails['email']; ?>">

                                                        <!-- Additional hidden input fields for book details -->
                                                        <input type="hidden" name="booktitle" value="<?php echo $book['booktitle']; ?>">
                                                        <input type="hidden" name="author" value="<?php echo $book['author']; ?>">
                                                        <input type="hidden" name="bookshelf" value="<?php echo $book['bookshelf']; ?>">
                                                        <input type="hidden" name="quantity" value="<?php echo $book['quantity']; ?>">

                                                        <div class="form-check me-5">
                                                            <input class="form-check-input" type="checkbox" value="" id="termsCheckbox<?php echo $index; ?>" required>
                                                            <label class="form-check-label" for="termsCheckbox<?php echo $index; ?>">
                                                                <div class="d-flex align-items-center">
                                                                    <div>
                                                                        I agree to the
                                                                    </div>
                                                                    <a href="#" class="text-decoration-none ms-2" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions&nbsp;</a>
                                                                        of CHMSU Library
                                                                </div>
                                                            </label>
                                                        </div>
                                                        <!-- Additional form elements can be added here -->
                                                    </form>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-primary" id="confirmReturnBtn<?php echo $index; ?>">Yes</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <script>
                                    // Modify the script for the Confirm Return button to use correct field names
                                    document.getElementById('confirmReturnBtn<?php echo $index; ?>').addEventListener('click', function() {
                                        // Check if the checkbox is checked
                                        if (document.getElementById('termsCheckbox<?php echo $index; ?>').checked) {
                                            // Update the hidden input fields with the new values
                                            document.getElementById('returnForm<?php echo $index; ?>').querySelector('[name="available"]').value = <?php echo $book['available'] + 1; ?>;
                                            document.getElementById('returnForm<?php echo $index; ?>').querySelector('[name="borrowed"]').value = <?php echo $book['borrowed'] - 1; ?>;
                                            // If checked, submit the form
                                            document.getElementById('returnForm<?php echo $index; ?>').submit();
                                        } else {
                                            // If not checked, show an alert or handle as needed
                                            alert('Please agree to the terms and conditions.');
                                        }
                                    });
                                    </script>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No books in inventory.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Modal for Terms & Conditions -->
<div class="modal fade" id="termsModal" tabindex="-1" role="dialog" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms & Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Your terms and conditions content goes here -->
                <p>This is where you describe your terms and conditions.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        var bookCards = document.getElementById('book-card-container').getElementsByClassName('card-borrow');

        // New color schemes for hovering
        var newColorSchemes = ['#061d3b', '#025f9c', '#ab8324', '#8f6e4d', '#1e797d'];

        // Store the original color of each card
        var originalColors = [];

        for (let i = 0; i < bookCards.length; i++) {
            originalColors[i] = bookCards[i].style.backgroundColor;

            bookCards[i].addEventListener('mouseover', function() {
                // Calculate color index using modulo to loop through color schemes
                var colorIndex = i % newColorSchemes.length;
                this.style.backgroundColor = newColorSchemes[colorIndex];
            });

            bookCards[i].addEventListener('mouseout', function() {
                this.style.backgroundColor = originalColors[i];
            });
        }
    </script>

</body>
</html>
