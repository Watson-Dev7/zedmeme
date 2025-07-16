<?php
$file = 'test.txt';
if (file_put_contents($file, 'Test write')) {
    echo "Folder is writable!";
    unlink($file); // Delete test file
} else {
    echo "Failed to write. Check permissions!";
}