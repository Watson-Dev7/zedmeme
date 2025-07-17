<?php
header('Content-Type: image/png');

// Create a 200x200 transparent image
$width = 200;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// Set background to transparent
$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $transparent);
imagesavealpha($image, true);

// Add a border
$borderColor = imagecolorallocate($image, 200, 200, 200);
imagerectangle($image, 0, 0, $width-1, $height-1, $borderColor);

// Add text
$text = 'No Image';
$textColor = imagecolorallocate($image, 150, 150, 150);
$font = 5; // Built-in font (1-5)
$textWidth = imagefontwidth($font) * strlen($text);
$textX = ($width - $textWidth) / 2;
$textY = ($height - imagefontheight($font)) / 2;

imagestring($image, $font, $textX, $textY, $text, $textColor);

// Output the image
imagepng($image);
imagedestroy($image);
?>
