<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Lokasi mpdf autoload
include '../db.php';

use Mpdf\Mpdf;

$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Query ikut status
$sql = "SELECT a.*, c.title AS category_title 
        FROM applications a 
        JOIN categories c ON a.category_id = c.id ";

if ($status === 'active') {
    $sql .= "WHERE a.status = 'active' ";
} elseif ($status === 'inactive') {
    $sql .= "WHERE a.status = 'inactive' ";
}
$sql .= "ORDER BY a.created DESC";

$result = $conn->query($sql);

// Create PDF content
$html = "<h2 style='text-align:center;'>Application Reviews (" . ucfirst($status) . ")</h2><hr>";

while ($row = $result->fetch_assoc()) {
    $html .= "
        <h4>{$row['title']}</h4>
        <strong>Status:</strong> " . ucfirst($row['status']) . "<br>
        <strong>Category:</strong> {$row['category_title']}<br>
        <strong>Review:</strong> {$row['review']}<br>
        <small>Created: " . date("d M Y, h:i A", strtotime($row['created'])) . "</small><br><br>
        <hr>
    ";
}

// Generate PDF
$mpdf = new Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('application_reviews.pdf', 'I'); // Open in browser
