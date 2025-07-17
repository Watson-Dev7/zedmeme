<?php
require 'includes/functions.php';
secure_session_start();

if (!is_logged_in()) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Please log in to upload memes.']);
    exit;
}

try {
    // 1. Verify Upload Directory
    $uploadDir = dirname(__DIR__) . '/uploads/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Server configuration error']);
            exit;
        }
    }

    // 2. Basic Validation
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
        exit;
    }

    if (!isset($_FILES['memeImage'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
        exit;
    }

    // 3. File Validation
    $file = $_FILES['memeImage'];
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
        UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
        UPLOAD_ERR_PARTIAL => 'Partial upload',
        UPLOAD_ERR_NO_FILE => 'No file selected',
        UPLOAD_ERR_NO_TMP_DIR => 'Server configuration error',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
        UPLOAD_ERR_EXTENSION => 'File type not allowed'
    ];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = $errorMessages[$file['error']] ?? 'Unknown upload error';
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }

    // 4. File Type Verification
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    if (!in_array($mime, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG, GIF, or WebP images allowed']);
        exit;
    }

    // 5. File Size Limit (5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'File too large (max 5MB)']);
        exit;
    }

    // 6. Generate Secure Filename
    $extension = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        default => strtolower(pathinfo($file['name'], PATHINFO_EXTENSION))
    };

    $filename = uniqid('meme_', true) . '.' . $extension;
    $targetPath = $uploadDir . $filename;

    // 7. Move Uploaded File
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to save file.');
    }

    // 8. Database Record
    $title = substr(trim($_POST['title'] ?? ''), 0, 100);
    $userId = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare('INSERT INTO memes (user_id, filename, title) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $filename, $title]);

        echo json_encode([
            'status' => 'success',
            'filename' => $filename,
            'title' => $title,
            'path' => 'uploads/' . $filename
        ]);
    } catch (PDOException $e) {
        // Clean up failed upload
        unlink($targetPath);

        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error'
            // 'debug' => $e->getMessage() // Uncomment for development only
        ]);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>