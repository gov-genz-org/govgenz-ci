<?php

declare(strict_types=1);

namespace App\Libraries;

use RuntimeException;

/**
 * QR code PNG avec logo Gov Gen Z centré (GD local, sinon QuickChart.io).
 */
class ProjectShareQrGenerator
{
    private const QR_API = 'https://api.qrserver.com/v1/create-qr-code/';

    private const QUICKCHART_API = 'https://quickchart.io/qr';

    /** @var list<string> */
    private const LOGO_RELATIVE_PATHS = [
        'assets/img/govgenz-logo-qr.png',
        'assets/logo-256.png',
    ];

    public static function generate(string $targetUrl, int $size = 512): string
    {
        $targetUrl = trim($targetUrl);
        if ($targetUrl === '') {
            throw new RuntimeException('Target URL is empty.');
        }

        $size = max(140, min(1024, $size));

        $qrUrl = self::QR_API . '?' . http_build_query([
            'size'   => $size . 'x' . $size,
            'ecc'    => 'H',
            'margin' => $size >= 256 ? 12 : 8,
            'data'   => $targetUrl,
        ]);

        $qrRaw = self::fetchUrl($qrUrl);
        if ($qrRaw === '') {
            throw new RuntimeException('Unable to fetch QR code image.');
        }

        $logoPath = self::resolveLogoPath();
        if ($logoPath !== null && extension_loaded('gd')) {
            $withLogo = self::compositeLogoWithGd($qrRaw, $logoPath, $size);
            if ($withLogo !== '') {
                return $withLogo;
            }
        }

        $logoUrl = self::logoPublicUrl();
        if ($logoUrl !== null) {
            $withLogo = self::generateViaQuickChart($targetUrl, $size, $logoUrl);
            if ($withLogo !== '') {
                return $withLogo;
            }
        }

        return $qrRaw;
    }

    /**
     * Racines où les assets publics peuvent se trouver (public/ ou racine FTP).
     *
     * @return list<string>
     */
    private static function publicAssetRoots(): array
    {
        $roots = [];

        $add = static function (string $root) use (&$roots): void {
            $root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if ($root !== DIRECTORY_SEPARATOR && ! in_array($root, $roots, true)) {
                $roots[] = $root;
            }
        };

        $add(FCPATH);

        if (is_dir(FCPATH . 'public')) {
            $add(FCPATH . 'public');
        }

        if (defined('ROOTPATH')) {
            $add(ROOTPATH . 'public');
        }

        return $roots;
    }

    private static function resolveLogoPath(): ?string
    {
        foreach (self::LOGO_RELATIVE_PATHS as $relative) {
            foreach (self::publicAssetRoots() as $root) {
                $path = $root . str_replace('/', DIRECTORY_SEPARATOR, $relative);
                if (is_file($path)) {
                    return $path;
                }
            }
        }

        return null;
    }

    private static function logoPublicUrl(): ?string
    {
        if (self::resolveLogoPath() === null) {
            return null;
        }

        helper('url');

        foreach (self::LOGO_RELATIVE_PATHS as $relative) {
            foreach (self::publicAssetRoots() as $root) {
                if (is_file($root . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
                    return base_url($relative);
                }
            }
        }

        return null;
    }

    private static function compositeLogoWithGd(string $qrRaw, string $logoPath, int $size): string
    {
        $qrImg = @imagecreatefromstring($qrRaw);
        if ($qrImg === false) {
            return '';
        }

        $logoImg = @imagecreatefrompng($logoPath);
        if ($logoImg === false) {
            imagedestroy($qrImg);

            return '';
        }

        self::drawLogoCenter($qrImg, $logoImg, $size);
        imagedestroy($logoImg);

        return self::encodePng($qrImg);
    }

    private static function generateViaQuickChart(string $targetUrl, int $size, string $logoUrl): string
    {
        $qrUrl = self::QUICKCHART_API . '?' . http_build_query([
            'text'                 => $targetUrl,
            'size'                 => $size,
            'ecLevel'              => 'H',
            'margin'               => 2,
            'centerImageUrl'       => $logoUrl,
            'centerImageSizeRatio' => 0.22,
            'centerImageWhiteBg'   => 'true',
        ]);

        $body = self::fetchUrl($qrUrl);
        if ($body === '' || ! str_starts_with($body, "\x89PNG\r\n\x1a\n")) {
            return '';
        }

        return $body;
    }

    private static function fetchUrl(string $url): string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_TIMEOUT        => 20,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_USERAGENT      => 'GovGenZ-ProjectShareQr/1.0',
                ]);
                $body = curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                if (is_string($body) && $body !== '' && $code >= 200 && $code < 300) {
                    return $body;
                }
            }
        }

        $allowUrlFopen = filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOL);
        if ($allowUrlFopen) {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 20,
                    'header'  => "User-Agent: GovGenZ-ProjectShareQr/1.0\r\n",
                ],
            ]);
            $body = @file_get_contents($url, false, $context);
            if (is_string($body) && $body !== '') {
                return $body;
            }
        }

        return '';
    }

    /**
     * @param \GdImage $qrImg
     * @param \GdImage $logoImg
     */
    private static function drawLogoCenter($qrImg, $logoImg, int $size): void
    {
        $logoSize = (int) round($size * 0.2);
        $pad      = (int) round($logoSize * 0.12);
        $box      = $logoSize + $pad * 2;
        $x        = (int) (($size - $box) / 2);
        $y        = (int) (($size - $box) / 2);

        $white = imagecolorallocate($qrImg, 255, 255, 255);
        imagefilledrectangle($qrImg, $x, $y, $x + $box, $y + $box, $white);

        $logoW = imagesx($logoImg);
        $logoH = imagesy($logoImg);
        imagecopyresampled(
            $qrImg,
            $logoImg,
            $x + $pad,
            $y + $pad,
            0,
            0,
            $logoSize,
            $logoSize,
            $logoW,
            $logoH
        );
    }

    /**
     * @param \GdImage $img
     */
    private static function encodePng($img): string
    {
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        if ($png === false || $png === '') {
            throw new RuntimeException('Unable to encode PNG.');
        }

        return $png;
    }
}
