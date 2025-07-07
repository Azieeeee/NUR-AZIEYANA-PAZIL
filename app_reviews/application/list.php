<?php
include '../db.php';

// Ambil status dari query param
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// SQL ikut filter
$sql = "SELECT a.*, c.title AS category_title 
        FROM applications a 
        JOIN categories c ON a.category_id = c.id ";

if ($status_filter === 'active') {
    $sql .= "WHERE a.status = 'active' ";
} elseif ($status_filter === 'inactive') {
    $sql .= "WHERE a.status = 'inactive' ";
}

$sql .= "ORDER BY a.created DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Reviews</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="#">AppReview Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
      
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="create.php">➕ Create New</a>
                </li>
            </ul>
        </div>
    </div>
</nav>


<!-- Main Content -->
<div class="container py-5">
    <h2 class="mb-4 text-center">📲 Application Reviews</h2>

    <!-- Filter Dropdown -->
    <form method="get" class="mb-4 d-flex justify-content-between">
        <div>
            <select name="status" onchange="this.form.submit()" class="form-select w-auto d-inline-block">
                <option value="all" <?php if ($status_filter == 'all') echo 'selected'; ?>>All</option>
                <option value="active" <?php if ($status_filter == 'active') echo 'selected'; ?>>Active</option>
                <option value="inactive" <?php if ($status_filter == 'inactive') echo 'selected'; ?>>Inactive</option>
            </select>
        </div>
        <div>
            <a href="export.php?status=<?php echo $status_filter; ?>" class="btn btn-success">📄 Export PDF</a>
        </div>
    </form>

    <?php 
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            include 'app_card.php';
        }
    } else {
        echo '<p class="text-muted">No applications found for the selected filter.</p>';
    }
    ?>
</div>

</body>
</html>
