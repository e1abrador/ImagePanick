<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

if (!isset($_FILES['svg']) || $_FILES['svg']['error'] !== UPLOAD_ERR_OK) {
    header('Location: /?error=No file uploaded');
    exit;
}

$file = $_FILES['svg'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext !== 'svg') {
    header('Location: /?error=Only SVG files are allowed');
    exit;
}

$uploadDir = '/tmp/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$inputPath = $uploadDir . basename($file['name']);
$outputPath = $uploadDir . pathinfo($file['name'], PATHINFO_FILENAME) . '.png';

move_uploaded_file($file['tmp_name'], $inputPath);

$cmd = 'magick ' . escapeshellarg($inputPath) . ' ' . escapeshellarg($outputPath) . ' 2>&1';
exec($cmd, $output, $returnCode);

header('Location: /?success=1');
