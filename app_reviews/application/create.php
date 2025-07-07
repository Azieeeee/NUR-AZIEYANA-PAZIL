<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_name = $_POST['category_name'];
    $posted_date = $_POST['posted_date'];
    $author = $_POST['author'];
    $title = $_POST['title'];
    $review = $_POST['review'];
    $status = $_POST['status'];

    // Check if category already exists
    $check = $conn->query("SELECT id FROM categories WHERE title = '$category_name' LIMIT 1");

    if ($check->num_rows > 0) {
        $category = $check->fetch_assoc();
        $category_id = $category['id'];
    } else {
        $conn->query("INSERT INTO categories (title, status) VALUES ('$category_name', 'active')");
        $category_id = $conn->insert_id;
    }

    // Handle image upload
    $image_name = $_FILES['image']['name'];
    $image_tmp = $_FILES['image']['tmp_name'];
    $image_dir = '../assets/images/' . $image_name;
    move_uploaded_file($image_tmp, $image_dir);

    // Insert application data
    $sql = "INSERT INTO applications (category_id, posted_date, author, title, review, image, image_dir, status)
            VALUES ('$category_id', '$posted_date', '$author', '$title', '$review', '$image_name', '$image_dir', '$status')";
    $conn->query($sql);

    header("Location: list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Application Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            max-width: 800px;
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
        .btn-submit {
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
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        }
        .input-group-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px 0 0 10px;
        }
        .form-floating {
            margin-bottom: 20px;
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
        .icon-input .form-control {
            padding-left: 45px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">
                <i class="fas fa-plus-circle me-2"></i>
                Create Application Review
            </h2>
            
            <form method="post" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="category_name" class="form-label">
                                <i class="fas fa-folder me-2"></i>Category
                            </label>
                            <div class="icon-input">
                                <i class="fas fa-tag"></i>
                                <input type="text" name="category_name" id="category_name" class="form-control" 
                                       placeholder="Type new or existing category" required>
                            </div>
                            <div class="form-text">Enter a new category or use an existing one</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="posted_date" class="form-label">
                                <i class="fas fa-calendar-alt me-2"></i>Posted Date
                            </label>
                            <div class="icon-input">
                                <i class="fas fa-clock"></i>
                                <input type="datetime-local" name="posted_date" id="posted_date" class="form-control" required>
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
                                       placeholder="Enter author name" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label for="status" class="form-label">
                                <i class="fas fa-toggle-on me-2"></i>Status
                            </label>
                            <select name="status" id="status" class="form-select">
                                <option value="active">
                                    <i class="fas fa-check-circle"></i> Active
                                </option>
                                <option value="inactive">
                                    <i class="fas fa-times-circle"></i> Inactive
                                </option>
                            </select>
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
                               placeholder="Enter review title" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="review" class="form-label">
                        <i class="fas fa-comment-alt me-2"></i>Review Content
                    </label>
                    <textarea name="review" id="review" class="form-control" rows="6" 
                              placeholder="Write your detailed review here..." required></textarea>
                    <div class="form-text">Provide a comprehensive review of the application</div>
                </div>

                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-image me-2"></i>Upload Image
                    </label>
                    <div class="upload-area">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <h5>Choose Image File</h5>
                        <p class="text-muted mb-3">Drag and drop or click to select</p>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <small class="text-muted">Supported formats: JPG, PNG, GIF (Max 5MB)</small>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-paper-plane me-2"></i>
                        Create Review
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-set current date/time
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();
            const datetime = now.toISOString().slice(0, 16);
            document.getElementById('posted_date').value = datetime;
        });

        // File upload preview
        document.querySelector('input[type="file"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const uploadArea = document.querySelector('.upload-area');
                uploadArea.innerHTML = `
                    <i class="fas fa-check-circle" style="color: #28a745;"></i>
                    <h5 style="color: #28a745;">File Selected</h5>
                    <p class="text-muted mb-3">${file.name}</p>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                    <small class="text-muted">Click to change file</small>
                `;
            }
        });

        // Form validation feedback
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-submit');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>