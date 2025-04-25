<?php
// Set the content type to image/png
header('Content-Type: image/png');

// Create a 200x200 image
$image = imagecreatetruecolor(200, 200);

// Define colors
$bg = imagecolorallocate($image, 240, 240, 240);
$text_color = imagecolorallocate($image, 150, 150, 150);

// Fill the background
imagefill($image, 0, 0, $bg);

// Add text
$text = "No Image";
$font_size = 5;

// Get text dimensions
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);

// Calculate position to center the text
$x = (200 - $text_width) / 2;
$y = (200 - $text_height) / 2;

// Add the text to the image
imagestring($image, $font_size, $x, $y, $text, $text_color);

// Output the image
imagepng($image);

// Free memory
imagedestroy($image); 