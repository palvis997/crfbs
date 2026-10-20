<?php
session_start();
require_once 'db.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle booking submission (Create operation)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_resource'])) {
    $user_id = $_SESSION['user_id'];
    $resource_id = $_POST['resource_id'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if (empty($resource_id) || empty($start_time) || empty($end_time)) {
        $message = "<div class='alert alert-danger'>All booking fields are required.</div>";
    } else {
        $sql = "INSERT INTO bookings (user_id, resource_id, start_time, end_time, status) VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiss", $user_id, $resource_id, $start_time, $end_time);

        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>Booking request submitted successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Error submitting booking.</div>";
        }
        $stmt->close();
    }
}

// Fetch resources for selection
$resources_result = $conn->query("SELECT * FROM resources WHERE status = 'available'");

// Fetch user's bookings (Read operation)
$user_id = $_SESSION['user_id'];
$bookings_sql = "SELECT b.id, r.name AS resource_name, b.start_time, b.end_time, b.status 
                 FROM bookings b 
                 JOIN resources r ON b.resource_id = r.id 
                 WHERE b.user_id = ?";
$stmt_bookings = $conn->prepare($bookings_sql);
$stmt_bookings->bind_param("i", $user_id);
$stmt_bookings->execute();
$bookings_result = $stmt_bookings->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Campus Resource Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">CRFBS Portal</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php echo $message; ?>

        <div class="row">
            <!-- Book a Resource Form -->
            <div class="col-md-5 mb-4">
                <div class="card shadow-sm p-4">
                    <h4 class="mb-3">Book a Resource</h4>
                    <form action="dashboard.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Select Resource/Facility</label>
                            <select name="resource_id" class="form-select" required>
                                <option value="">Choose...</option>
                                <?php while ($res = $resources_result->fetch_assoc()): ?>
                                    <option value="<?php echo $res['id']; ?>">
                                        <?php echo htmlspecialchars($res['name'] . " (" . $res['category'] . ")"); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="datetime-local" name="start_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">End Time</label>
                            <input type="datetime-local" name="end_time" class="form-control" required>
                        </div>
                        <button type="submit" name="book_resource" class="btn btn-primary w-100">Submit Booking</button>
                    </form>
                </div>
            </div>

            <!-- My Bookings Table -->
            <div class="col-md-7">
                <div class="card shadow-sm p-4">
                    <h4 class="mb-3">My Booking History</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Resource</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($bookings_result->num_rows > 0): ?>
                                    <?php while ($row = $bookings_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['resource_name']); ?></td>
                                            <td><?php echo $row['start_time']; ?></td>
                                            <td><?php echo $row['end_time']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $row['status'] == 'approved' ? 'success' : ($row['status'] == 'rejected' ? 'danger' : 'warning'); 
                                                ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No bookings found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>