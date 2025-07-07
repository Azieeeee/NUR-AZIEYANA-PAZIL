<?php
include '../db.php';

// Initialize variables
$error_message = '';
$success_message = '';
$app = null;
$categories = null;

// Check if ID is provided and is numeric
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$id = (int)$_GET['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize inputs
        $category_id = (int)$_POST['category_id'];
        $posted_date = $_POST['posted_date'];
        $author = trim($_POST['author']);
        $title = trim($_POST['title']);
        $review = trim($_POST['review']);
        $status = $_POST['status'];
        
        // Validate required fields
        if (empty($author) || empty($title) || empty($review)) {
            throw new Exception("All fields are required.");
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        // Handle image upload if new image is provided
        $image_sql = "";
        if (!empty($_FILES['image']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];
            
            // Validate file type
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.");
            }
            
            // Validate file size (5MB max)
            if ($file_size > 5 * 1024 * 1024) {
                throw new Exception("File size too large. Maximum 5MB allowed.");
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '_' . time() . '.' . $file_extension;
            $image_dir = '../assets/images/' . $image_name;
            
            // Get old image path to delete later
            $old_image_query = $conn->prepare("SELECT image_dir FROM applications WHERE id = ?");
            $old_image_query->bind_param("i", $id);
            $old_image_query->execute();
            $old_image_result = $old_image_query->get_result();
            $old_image_data = $old_image_result->fetch_assoc();
            $old_image_path = $old_image_data ? $old_image_data['image_dir'] : '';
            $old_image_query->close();
            
            // Upload new image
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_dir)) {
                throw new Exception("Failed to upload image.");
            }
            
            $image_sql = ", image = ?, image_dir = ?";
            
            // Delete old image if it exists
            if (!empty($old_image_path) && file_exists($old_image_path)) {
                unlink($old_image_path);
            }
        }
        
        // Update application
        $sql = "UPDATE applications SET category_id = ?, posted_date = ?, author = ?, title = ?, review = ?, status = ?" . $image_sql . " WHERE id = ?";
        $stmt = $conn->prepare($sql);
        
        if ($image_sql) {
            $stmt->bind_param("issssssi", $category_id, $posted_date, $author, $title, $review, $status, $image_name, $image_dir, $id);
        } else {
            $stmt->bind_param("isssssi", $category_id, $posted_date, $author, $title, $review, $status, $id);
        }
        
        if ($stmt->execute()) {
            $conn->commit();
            $success_message = "Application updated successfully!";
            // Redirect after 2 seconds
            header("refresh:2;url=list.php");
        } else {
            throw new Exception("Failed to update application.");
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

// Fetch application data
try {
    $app_stmt = $conn->prepare("SELECT * FROM applications WHERE id = ?");
    $app_stmt->bind_param("i", $id);
    $app_stmt->execute();
    $app_result = $app_stmt->get_result();
    
    if ($app_result->num_rows === 0) {
        header("Location: list.php");
        exit;
    }
    
    $app = $app_result->fetch_assoc();
    $app_stmt->close();
    
    // Fetch categories
    $categories = $conn->query("SELECT * FROM categories WHERE status = 'active' ORDER BY title ASC");
    
} catch (Exception $e) {
    $error_message = "Error loading application data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Application Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin: 50px auto;
            max-width: 900px;
        }
        .form-title {
            color: #333;
            font-weight: 700;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }
        .form-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }
        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        .btn-secondary-custom {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
        }
        .btn-secondary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.6);
        }
        .icon-input {
            position: relative;
        }
        .icon-input i {
            position: absolute;
            top: 50%;
            left: 16px;
            transform: translateY(-50%);
            color: #667eea;
            z-index: 5;
        }
        .icon-input .form-control, .icon-input .form-select {
            padding-left: 45px;
        }
        .current-image {
            max-width: 150px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 10px;
            border: 3px solid #667eea;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .image-preview-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            border: 2px dashed #667eea;
            margin-bottom: 15px;
        }
        .upload-area {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: rgba(102, 126, 234, 0.05);
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            background: rgba(102, 126, 234, 0.1);
            border-color: #764ba2;
        }
        .upload-area i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
        }
        .alert-custom {
            border: none;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .alert-success-custom {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .alert-danger-custom {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            color: white;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .breadcrumb-custom {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 30px;
        }
        .breadcrumb-custom a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .breadcrumb-custom a:hover {
            color: #764ba2;
        }
        .countdown {
            font-size: 1.1rem;
            color: #28a745;
            font-weight: 600;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container animate__animated animate__fadeIn">
            <!-- Breadcrumb -->
            <nav class="breadcrumb-custom">
                <a href="list.php"><i class="fas fa-list me-2"></i>Application List</a>
                <span class="mx-2">/</span>
                <span>Edit Application</span>
            </nav>

            <!-- Success/Error Messages -->
            <?php if ($success_message): ?>
                <div class="alert alert-success-custom animate__animated animate__bounceIn">
                    <i class="fas fa-check-circle fa-2x mb-3"></i>
                    <h4><?php echo htmlspecialchars($success_message); ?></h4>
                    <p>Redirecting to application list...</p>
                    <div class="countdown" id="countdown">Redirecting in 2 seconds...</div>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger-custom animate__animated animate__shakeX">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($app && !$success_message): ?>
                <h2 class="form-title">
                    <i class="fas fa-edit me-2"></i>
                    Edit Application Review
                </h2>
                
                <form method="post" enctype="multipart/form-data" id="editForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="category_id" class="form-label">
                                    <i class="fas fa-folder me-2"></i>Category
                                </label>
                                <div class="icon-input">
                                    <i class="fas fa-tag"></i>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php if ($categories): ?>
                                            <?php while($cat = $categories->fetch_assoc()): ?>
                                                <option value="<?php echo $cat['id']; ?>" 
                                                        <?php echo $cat['id'] == $app['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['title']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="posted_date" class="form-label">
                                    <i class="fas fa-calendar-alt me-2"></i>Posted Date
                                </label>
                                <div class="icon-input">
                                    <i class="fas fa-clock"></i>
                                    <input type="datetime-local" name="posted_date" id="posted_date" 
                                           class="form-control" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($app['posted_date'])); ?>" 
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="author" class="form-label">
                                    <i class="fas fa-user me-2"></i>Author
                                </label>
                                <div class="icon-input">
                                    <i class="fas fa-user-edit"></i>
                                    <input type="text" name="author" id="author" class="form-control" 
                                           value="<?php echo htmlspecialchars($app['author']); ?>" 
                                           placeholder="Enter author name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="status" class="form-label">
                                    <i class="fas fa-toggle-on me-2"></i>Status
                                </label>
                                <div class="icon-input">
                                    <i class="fas fa-info-circle"></i>
                                    <select name="status" id="status" class="form-select" required>
                                        <option value="active" <?php echo $app['status'] == 'active' ? 'selected' : ''; ?>>
                                            Active
                                        </option>
                                        <option value="inactive" <?php echo $app['status'] == 'inactive' ? 'selected' : ''; ?>>
                                            Inactive
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="title" class="form-label">
                            <i class="fas fa-heading me-2"></i>Title
                        </label>
                        <div class="icon-input">
                            <i class="fas fa-text-height"></i>
                            <input type="text" name="title" id="title" class="form-control" 
                                   value="<?php echo htmlspecialchars($app['title']); ?>" 
                                   placeholder="Enter review title" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="review" class="form-label">
                            <i class="fas fa-comment-alt me-2"></i>Review Content
                        </label>
                        <textarea name="review" id="review" class="form-control" rows="6" 
                                  placeholder="Write your detailed review here..." required><?php echo htmlspecialchars($app['review']); ?></textarea>
                        <div class="form-text">Provide a comprehensive review of the application</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-image me-2"></i>Application Image
                        </label>
                        
                        <?php if (!empty($app['image']) && file_exists($app['image_dir'])): ?>
                            <div class="image-preview-container">
                                <h6><i class="fas fa-eye me-2"></i>Current Image</h6>
                                <img src="<?php echo htmlspecialchars($app['image_dir']); ?>" 
                                     alt="Current Application Image" class="current-image mb-3">
                                <p class="text-muted mb-0">
                                    <small><?php echo htmlspecialchars($app['image']); ?></small>
                                </p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="upload-area">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h6>Upload New Image (Optional)</h6>
                            <p class="text-muted mb-3">Leave empty to keep current image</p>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Supported formats: JPG, PNG, GIF, WebP (Max 5MB)</small>
                        </div>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-update me-3">
                            <i class="fas fa-save me-2"></i>
                            Update Application
                        </button>
                        <a href="list.php" class="btn btn-secondary-custom">
                            <i class="fas fa-times me-2"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Countdown timer for redirect
        <?php if ($success_message): ?>
        let countdown = 2;
        const countdownElement = document.getElementById('countdown');
        const timer = setInterval(function() {
            countdown--;
            countdownElement.textContent = `Redirecting in ${countdown} seconds...`;
            if (countdown <= 0) {
                clearInterval(timer);
                countdownElement.textContent = 'Redirecting now...';
            }
        }, 1000);
        <?php endif; ?>

        // Form submission handling
        document.getElementById('editForm')?.addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-update');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
            submitBtn.disabled = true;
        });

        // File upload preview
        document.querySelector('input[type="file"]')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const uploadArea = document.querySelector('.upload-area');
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    uploadArea.innerHTML = `
                        <i class="fas fa-check-circle" style="color: #28a745;"></i>
                        <h6 style="color: #28a745;">New Image Selected</h6>
                        <img src="${e.target.result}" alt="Preview" style="max-width: 150px; max-height: 150px; object-fit: cover; border-radius: 10px; margin: 10px 0;">
                        <p class="text-muted mb-3">${file.name}</p>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">Click to change file</small>
                    `;
                };
                
                reader.readAsDataURL(file);
            }
        });

        // Auto-resize textarea
        document.getElementById('review')?.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 's') {
                e.preventDefault();
                document.getElementById('editForm')?.submit();
            }
            if (e.key === 'Escape') {
                window.location.href = 'list.php';
            }
        });
    </script>
</body>
</html>