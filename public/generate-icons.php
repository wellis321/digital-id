<?php
/**
 * Simple Icon Generator for PWA
 * Generates basic placeholder icons for PWA installation
 * Run this once: php public/generate-icons.php
 */

require_once dirname(__DIR__) . '/config/config.php';

$iconSizes = [72, 96, 128, 144, 152, 192, 384, 512];
$iconDir = dirname(__DIR__) . '/public/assets/icons';

// Create icons directory if it doesn't exist
if (!is_dir($iconDir)) {
    mkdir($iconDir, 0755, true);
}

// Check if ImageMagick or GD is available
$hasGD = extension_loaded('gd');
$hasImagick = extension_loaded('imagick');

if (!$hasGD && !$hasImagick) {
    die("Error: Neither GD nor ImageMagick is available. Please install one of these PHP extensions.\n");
}

echo "Generating PWA icons...\n";

foreach ($iconSizes as $size) {
    $filename = $iconDir . "/icon-{$size}x{$size}.png";
    
    if (file_exists($filename)) {
        echo "Skipping {$size}x{$size} (already exists)\n";
        continue;
    }
    
    if ($hasGD) {
        // Create image using GD
        $img = imagecreatetruecolor($size, $size);
        
        // Set background color (blue theme)
        $bgColor = imagecolorallocate($img, 37, 99, 235); // #2563eb
        imagefill($img, 0, 0, $bgColor);
        
        // Add text "ID" in white
        $textColor = imagecolorallocate($img, 255, 255, 255);
        $fontSize = $size * 0.4; // 40% of icon size
        $font = 5; // Built-in font (you can use imageloadfont for custom fonts)
        
        // Calculate text position (centered)
        $text = "ID";
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        $x = (int)(($size - $textWidth) / 2);
        $y = (int)(($size - $textHeight) / 2);
        
        // Draw text
        imagestring($img, $font, $x, $y, $text, $textColor);
        
        // Save as PNG
        imagepng($img, $filename);
        // imagedestroy is deprecated in PHP 8.5+ but harmless to keep for compatibility
        
        echo "Generated {$size}x{$size}\n";
    } elseif ($hasImagick) {
        // Create image using ImageMagick
        $img = new Imagick();
        $img->newImage($size, $size, new ImagickPixel('#2563eb'));
        
        // Add text
        $draw = new ImagickDraw();
        $draw->setFillColor('white');
        $draw->setFontSize($size * 0.4);
        $draw->setFontWeight(700);
        $draw->setTextAlignment(Imagick::ALIGN_CENTER);
        $draw->annotation($size / 2, $size / 2 + ($size * 0.15), 'ID');
        
        $img->drawImage($draw);
        $img->setImageFormat('png');
        $img->writeImage($filename);
        $img->clear();
        $img->destroy();
        
        echo "Generated {$size}x{$size}\n";
    }
}

echo "\nDone! Icons generated in: {$iconDir}\n";
echo "You can replace these with custom branded icons later.\n";

