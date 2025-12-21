<?php
/**
 * QR Code Generator Class
 * Generates QR codes for digital ID verification
 */

class QRCodeGenerator {
    
    /**
     * Generate QR code image data URI
     * Note: This is a simple implementation. For production, consider using a library like endroid/qr-code
     */
    public static function generate($data, $size = 200) {
        // For now, return a placeholder URL that will be handled by the verification endpoint
        // In production, you would use a QR code library to generate the actual image
        $verificationUrl = APP_URL . url('verify.php?token=' . urlencode($data));
        
        // Return data that can be used to generate QR code
        return [
            'url' => $verificationUrl,
            'data' => $data,
            'size' => $size
        ];
    }
    
    /**
     * Generate QR code image using Google Charts API (fallback)
     * In production, use a proper QR code library
     */
    public static function generateImageUrl($data, $size = 200) {
        $verificationUrl = APP_URL . url('verify.php?token=' . urlencode($data));
        $encodedUrl = urlencode($verificationUrl);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedUrl}";
    }
}

