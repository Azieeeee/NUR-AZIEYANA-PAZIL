<div class="card mb-4 shadow-sm border-<?php echo $row['status'] === 'active' ? 'success' : 'secondary'; ?>">
    <div class="row g-0">
        <div class="col-md-4">
            <img src="../assets/images/<?php echo $row['image']; ?>" class="img-fluid rounded-start" alt="App Image">
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <h5 class="card-title">
                    <?php echo htmlspecialchars($row['title']); ?>
                    <span class="badge bg-<?php echo $row['status'] === 'active' ? 'success' : 'secondary'; ?>">
                        <?php echo ucfirst($row['status']); ?>
                    </span>
                </h5>
                <p class="card-text"><strong>Category:</strong> <?php echo htmlspecialchars($row['category_title']); ?></p>
                <p class="card-text"><strong>Review:</strong> <?php echo nl2br(htmlspecialchars($row['review'])); ?></p>

                <p class="card-text">
                    <small class="text-muted">
                        Created: <?php echo date("d M Y, h:i A", strtotime($row['created'])); ?> |
                        Modified: <?php echo date("d M Y, h:i A", strtotime($row['modified'])); ?>
                    </small>
                </p>

                <?php
                $app_id = $row['id'];
                $comment_sql = "SELECT * FROM comments WHERE application_id = $app_id ORDER BY created DESC";
                $comment_result = $conn->query($comment_sql);

                if ($comment_result && $comment_result->num_rows > 0) {
                    echo '<hr><h6>Comments:</h6>';
                    while ($comment = $comment_result->fetch_assoc()) {
                        echo '<div class="mb-2">';
                        echo '<strong>' . htmlspecialchars($comment['author']) . ':</strong> ';
                        echo htmlspecialchars($comment['content']) . '<br>';
                        echo '<small class="text-muted">' . date("d M Y, h:i A", strtotime($comment['created'])) . '</small>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-muted">No comments yet.</p>';
                }
                ?>

                <div class="mt-3">
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="update.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Update</a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Are you sure you want to delete this application?');">Delete</a>
                </div>
            </div>
        </div>
    </div>
</div>
