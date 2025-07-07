<?php
// File: applications/update.php
include '../db.php';

// Initialize variables
$success_message = '';
$error_message = '';
$validation_errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
        $posted_date = trim($_POST['posted_date']);
        $author = trim($_POST['author']);
        $title = trim($_POST['title']);
        $review = filter_var($_POST['review'], FILTER_VALIDATE_FLOAT);
        $status = trim($_POST['status']);

        // Validation
        if (!$id) {
            $validation_errors[] = "Invalid application ID.";
        }
        if (!$category_id) {
            $validation_errors[] = "Please select a valid category.";
        }
        if (empty($posted_date)) {
            $validation_errors[] = "Posted date is required.";
        }
        if (empty($author)) {
            $validation_errors[] = "Author name is required.";
        }
        if (empty($title)) {
            $validation_errors[] = "Application title is required.";
        }
        if ($review === false || $review < 0 || $review > 5) {
            $validation_errors[] = "Review must be a number between 0 and 5.";
        }
        if (!in_array($status, ['active', 'inactive'])) {
            $validation_errors[] = "Invalid status selected.";
        }

        // Handle image upload if provided
        $image_sql = "";
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $max_size = 5 * 1024 * 1024; // 5MB

            if (!in_array($_FILES['image']['type'], $allowed_types)) {
                $validation_errors[] = "Only JPEG, PNG, GIF, and WebP images are allowed.";
            }
            if ($_FILES['image']['size'] > $max_size) {
                $validation_errors[] = "Image size must be less than 5MB.";
            }

            if (empty($validation_errors)) {
                $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
                $image_dir = '../assets/images/' . $image_name;
                
                // Create directory if it doesn't exist
                if (!is_dir('../assets/images/')) {
                    mkdir('../assets/images/', 0755, true);
                }
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $image_dir)) {
                    $image_sql = ", image=?, image_dir=?";
                } else {
                    $validation_errors[] = "Failed to upload image.";
                }
            }
        }

        // If no validation errors, proceed with update
        if (empty($validation_errors)) {
            // Prepare SQL statement
            if ($image_sql) {
                $sql = "UPDATE applications SET category_id=?, posted_date=?, author=?, title=?, review=?, status=? $image_sql WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssds" . "ssi", $category_id, $posted_date, $author, $title, $review, $status, $image_name, $image_dir, $id);
            } else {
                $sql = "UPDATE applications SET category_id=?, posted_date=?, author=?, title=?, review=?, status=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssdsi", $category_id, $posted_date, $author, $title, $review, $status, $id);
            }

            if ($stmt->execute()) {
                $success_message = "Application updated successfully!";
                // Redirect after 2 seconds
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'list.php';
                    }, 2000);
                </script>";
            } else {
                $error_message = "Error updating application: " . $stmt->error;
            }
            $stmt->close();
        }

    } catch (Exception $e) {
        $error_message = "An error occurred: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .update-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px 20px 0 0 !important;
            padding: 2rem;
            text-align: center;
        }
        
        .card-title {
            color: white;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .card-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating > .form-control {
            border-radius: 15px;
            border: 2px solid #e9ecef;
            padding: 1rem 0.75rem;
            height: auto;
        }
        
        .form-floating > .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
        
        .form-floating > label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn-outline-gradient {
            background: transparent;
            border: 2px solid #667eea;
            color: #667eea;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-outline-gradient:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: transparent;
            color: white;
            transform: translateY(-2px);
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            margin-bottom: 2rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            margin: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .status-badge.active {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .status-badge.inactive {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
        }
        
        .file-upload-wrapper {
            position: relative;
            overflow: hidden;
            border: 2px dashed #667eea;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            background: rgba(102, 126, 234, 0.05);
            transition: all 0.3s ease;
        }
        
        .file-upload-wrapper:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: #764ba2;
        }
        
        .file-upload-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .rating-stars {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin: 1rem 0;
        }
        
        .rating-star {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .rating-star:hover,
        .rating-star.active {
            color: #ffc107;
        }
        
        .progress-bar {
            height: 8px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        @media (max-width: 768px) {
            .card-title {
                font-size: 1.5rem;
            }
            
            .btn-gradient,
            .btn-outline-gradient {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="update-container">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">
                        <i class="fas fa-edit me-3"></i>Update Application
                    </h1>
                    <p class="card-subtitle">
                        Modify your application details
                    </p>
                </div>
                <div class="card-body p-5">
                    <!-- Success Message -->
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Success!</strong> <?= htmlspecialchars($success_message) ?>
                            <div class="mt-2">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         role="progressbar" style="width: 100%"></div>
                                </div>
                                <small class="text-white mt-1 d-block">Redirecting to application list...</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Validation Errors -->
                    <?php if (!empty($validation_errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Validation Errors:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($validation_errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Update Form -->
                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($_POST['id'] ?? '') ?>">
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Choose category...</option>
                                        <?php
                                        $cat_sql = "SELECT * FROM categories ORDER BY title";
                                        $cat_result = $conn->query($cat_sql);
                                        while ($cat_row = $cat_result->fetch_assoc()) {
                                            $selected = (isset($_POST['category_id']) && $_POST['category_id'] == $cat_row['id']) ? 'selected' : '';
                                            echo "<option value='{$cat_row['id']}' $selected>{$cat_row['title']}</option>";
                                        }
                                        ?>
                                    </select>
                                    <label for="category_id">
                                        <i class="fas fa-folder me-2"></i>Category
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="posted_date" 
                                           name="posted_date" 
                                           value="<?= htmlspecialchars($_POST['posted_date'] ?? '') ?>" 
                                           required>
                                    <label for="posted_date">
                                        <i class="fas fa-calendar me-2"></i>Posted Date
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control" 
                                           id="author" 
                                           name="author" 
                                           value="<?= htmlspecialchars($_POST['author'] ?? '') ?>" 
                                           required>
                                    <label for="author">
                                        <i class="fas fa-user me-2"></i>Author
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control" 
                                           id="title" 
                                           name="title" 
                                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" 
                                           required>
                                    <label for="title">
                                        <i class="fas fa-mobile-alt me-2"></i>Application Title
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label mb-3">
                                        <i class="fas fa-star me-2"></i>Rating
                                    </label>
                                    <div class="rating-stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star rating-star" data-rating="<?= $i ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="review" id="review" value="<?= htmlspecialchars($_POST['review'] ?? '0') ?>">
                                    <div class="text-center">
                                        <small class="text-muted">Current Rating: <span id="current-rating">0</span>/5</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label mb-3">
                                        <i class="fas fa-toggle-on me-2"></i>Status
                                    </label>
                                    <div class="text-center">
                                        <div class="btn-group" role="group">
                                            <input type="radio" class="btn-check" name="status" id="status_active" value="active" 
                                                   <?= (isset($_POST['status']) && $_POST['status'] == 'active') ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-success" for="status_active">
                                                <i class="fas fa-check-circle me-1"></i>Active
                                            </label>

                                            <input type="radio" class="btn-check" name="status" id="status_inactive" value="inactive"
                                                   <?= (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-warning" for="status_inactive">
                                                <i class="fas fa-pause-circle me-1"></i>Inactive
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label mb-3">
                                <i class="fas fa-image me-2"></i>Application Image
                            </label>
                            <div class="file-upload-wrapper">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <h5>Drop your image here or click to browse</h5>
                                <p class="text-muted">Supported formats: JPEG, PNG, GIF, WebP (Max 5MB)</p>
                                <input type="file" name="image" id="image" accept="image/*">
                                <label for="image" class="btn btn-outline-gradient">
                                    <i class="fas fa-folder-open me-2"></i>Choose File
                                </label>
                            </div>
                            <div id="image-preview" class="mt-3 text-center" style="display: none;">
                                <img id="preview-img" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearImage()">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-gradient w-100">
                                    <i class="fas fa-save me-2"></i>Update Application
                                </button>
                            </div>
                            <div class="col-md-6">
                                <a href="list.php" class="btn btn-outline-gradient w-100">
                                    <i class="fas fa-arrow-left me-2"></i>Back to List
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Star rating functionality
        const stars = document.querySelectorAll('.rating-star');
        const reviewInput = document.getElementById('review');
        const currentRating = document.getElementById('current-rating');
        
        // Initialize rating
        const initialRating = parseInt(reviewInput.value) || 0;
        updateStars(initialRating);
        
        stars.forEach((star, index) => {
            star.addEventListener('click', function() {
                const rating = index + 1;
                reviewInput.value = rating;
                updateStars(rating);
            });
            
            star.addEventListener('mouseenter', function() {
                const rating = index + 1;
                highlightStars(rating);
            });
        });
        
        document.querySelector('.rating-stars').addEventListener('mouseleave', function() {
            const currentVal = parseInt(reviewInput.value) || 0;
            updateStars(currentVal);
        });
        
        function updateStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('active');
                } else {
                    star.classList.remove('active');
                }
            });
            currentRating.textContent = rating;
        }
        
        function highlightStars(rating) {
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.style.color = '#ffc107';
                } else {
                    star.style.color = '#ddd';
                }
            });
        }
        
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview-img').src = e.target.result;
                    document.getElementById('image-preview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
        
        function clearImage() {
            document.getElementById('image').value = '';
            document.getElementById('image-preview').style.display = 'none';
        }
        
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                const forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
    </script>
</body>
</html>