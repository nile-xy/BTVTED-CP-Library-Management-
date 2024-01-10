<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username'])) {
    $_SESSION['error_message'] = "You must log in first.";
    header('Location: index.php');
    exit();
}

function getUserData($conn, $username) {
    $sql = "SELECT * FROM tbl_user WHERE username=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();

    return $userData;
}
    $currentUser = $_SESSION['username'];
    $userData = getUserData($conn, $currentUser);
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php 
    include 'announcement-bar.php';
    include 'navigation-bar-dashboard.php';
    ?>
    <div class="main-content text-center user-profile">
  <h2>Update Profile</h2>
  <form style="max-width: 400px; margin: 0 auto;" action="update-profile.php" method="post">
    <div class="mb-3">
        <label for="student_id" class="form-label visually-hidden">Student ID</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
            <input type="text" class="form-control" id="student_id" placeholder="Enter your Student ID" name="student_id" value="<?php echo $userData['student_id']; ?>">
        </div>
    </div>
    <div class="mb-3">
        <label for="course" class="form-label visually-hidden">Course</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-book"></i></span> 
            <select class="form-control" id="course" name="course" value="<?php echo $userData['course']; ?>">
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
        <div class="input-group">
            <label for="firstname" class="form-label visually-hidden">First name</label>
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control" id="firstname" placeholder="First name" name="firstname" value="<?php echo $userData['firstname']; ?>">

            <label for="lastname" class="form-label visually-hidden">Last name</label>
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control" id="lastname" placeholder="Last name" name="lastname" value="<?php echo $userData['lastname']; ?>">
        </div>
    </div>
    <div class="mb-3">
            <label for="age" class="form-label visually-hidden">Age</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="number" class="form-control" id="age" placeholder="Enter your age" name="age" value="<?php echo $userData['age']; ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="gender" class="form-label visually-hidden">Gender</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-venus-mars"></i></span>
                <select class="form-control" id="gender" name="gender">
                    <option value="Male" <?php if ($userData['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                    <option value="Female" <?php if ($userData['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                    <option value="Others" <?php if ($userData['gender'] === 'Others') echo 'selected'; ?>>Others</option>
                </select>
            </div>
        </div>
    <div class="mb-3">
        <label for="email" class="form-label visually-hidden">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" class="form-control" id="email" placeholder="Enter your email" name="email" value="<?php echo $userData['email']; ?>">
        </div>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label visually-hidden">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-lock"></i></span>
            <input type="password" class="form-control" id="password" placeholder="Enter your password" name="password" value="<?php echo $userData['password']; ?>">
        </div>
    </div>
    <div class="mb-3">
        <label for="username" class="form-label visually-hidden">Username</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-user"></i></span>
            <input type="text" class="form-control" id="username" placeholder="Enter your username" name="username" value="<?php echo $userData['username']; ?>">
        </div>
    </div>
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>
  <?php
      if (isset($_SESSION['success_message'])) {
        echo "<p class='success-message'>".$_SESSION['success_message']."</p>";
        unset($_SESSION['success_message']);
    }  
      if (isset($_SESSION['error_message'])) {
          echo "<p class='error-message'>".$_SESSION['error_message']."</p>";
          unset($_SESSION['error_message']);
      }
  ?>
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
</body>
</html>