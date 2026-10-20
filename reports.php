<?php
session_start();
require_once 'db.php';

// Optional: restrict to admin only, or allow logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Report 1: Active & Pending Bookings Summary
$report1_sql = "SELECT b.id, u.username, r.name AS resource_name, b.start_time, b.end_time, b.status 
                FROM bookings b 
                JOIN users u ON b.user_id = u.id 
                JOIN resources r ON b.resource_id = r.id";
$report1_result = $conn->query($report1_sql);

// Report 2: Most Frequently Booked Resources (Usage Log)
$report2_sql = "SELECT r.name, r.category, COUNT(b.id) AS total_bookings 
                FROM resources r 
                LEFT JOIN bookings b ON r.id = b.resource_id 
                GROUP BY r.id";
$report2_result = $conn->query($report2_sql);

// Report 3: Inventory Status Report
$report3_sql = "SELECT name, category, status, created_at FROM resources";
$report3_result = $conn->query($report3_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Reports - CRFBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">CRFBS Portal</a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <h2 class="mb-4">System Administrative Reports</h2>

        <!-- Report 1 -->
        <div class="card shadow-sm p-4 mb-4">
            <h4>1. Active & Pending Bookings Summary Table</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Resource</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $report1_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                            <td><?php echo htmlspecialchars($row['resource_name']); ?></td>
                            <td><?php echo $row['start_time']; ?></td>
                            <td><?php echo $row['end_time']; ?></td>
                            <td><?php echo ucfirst($row['status']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Report 2 -->
        <div class="card shadow-sm p-4 mb-4">
            <h4>2. Resource Utilization & Popularity Log</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2">
                    <thead class="table-dark">
                        <tr>
                            <th>Resource Name</th>
                            <th>Category</th>
                            <th>Total Bookings Made</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $report2_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><?php echo $row['total_bookings']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Report 3 -->
        <div class="card shadow-sm p-4 mb-4">
            <h4>3. Overall Resource Inventory Status Report</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2">
                    <thead class="table-dark">
                        <tr>
                            <th>Resource Name</th>
                            <th>Category</th>
                            <th>Current Status</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $report3_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td><span class="badge bg-success"><?php echo ucfirst($row['status']); ?></span></td>
                            <td><?php echo $row['created_at']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>