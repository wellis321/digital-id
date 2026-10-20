<?php
/**
 * QR Code Generator Class
 * Generates QR codes for digital ID verification, entirely on this server.
 *
 * The verification link in a QR code contains a live token. It must never be sent to a third-party QR
 * service (the old implementation did), so the code is drawn here with a bundled MIT-licensed library
 * (src/lib/qrcode-arase.php, Kazuhiko Arase) and returned as an inline SVG data URI.
 */

require_once __DIR__ . '/../lib/qrcode-arase.php';

class QRCodeGenerator {

    /**
     * Verification link (and token) for a card; what the QR code encodes.
     */
    public static function generate($data, $size = 200) {
        $verificationUrl = APP_URL . url('verify.php?token=' . urlencode($data));

        return [
            'url' => $verificationUrl,
            'data' => $data,
            'size' => $size
        ];
    }

    /**
     * Image source for the QR code of a card token: a data URI, so nothing is requested from any other site.
     * (Name kept from the old URL-based version so existing templates keep working.)
     */
    public static function generateImageUrl($data, $size = 200) {
        $verificationUrl = APP_URL . url('verify.php?token=' . urlencode($data));
        return self::dataUri($verificationUrl, $size);
    }

    /**
     * Any text or URL as an SVG data URI suitable for <img src="...">.
     */
    public static function dataUri($text, $size = 200) {
        return 'data:image/svg+xml;base64,' . base64_encode(self::svg($text, $size));
    }

    /**
     * Any text or URL as an SVG string. Medium error correction, 4-module quiet zone, black on white.
     */
    public static function svg($text, $size = 200) {
        $qr = QRCode::getMinimumQRCode((string) $text, QR_ERROR_CORRECT_LEVEL_M);
        $qr->make();
        $count = $qr->getModuleCount();
        $quiet = 4;
        $span = $count + 2 * $quiet;

        // One path with a square for every dark module keeps the file small.
        $path = '';
        for ($row = 0; $row < $count; $row++) {
            for ($col = 0; $col < $count; $col++) {
                if ($qr->isDark($row, $col)) {
                    $path .= 'M' . ($col + $quiet) . ',' . ($row + $quiet) . 'h1v1h-1z';
                }
            }
        }

        $size = (int) $size;
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size
            . '" viewBox="0 0 ' . $span . ' ' . $span . '" shape-rendering="crispEdges">'
            . '<rect width="' . $span . '" height="' . $span . '" fill="#ffffff"/>'
            . '<path d="' . $path . '" fill="#000000"/></svg>';
    }
}
