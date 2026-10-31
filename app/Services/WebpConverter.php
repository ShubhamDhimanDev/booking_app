<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

/**
 * Converts uploaded JPEG/PNG/GIF/WebP images to WebP using PHP's built-in GD
 * extension (no packages). Returns null whenever conversion isn't possible or
 * sensible (GD without WebP, animated GIF, oversized image, decode failure) so
 * the caller can fall back to storing the original file.
 */
class WebpConverter
{
    /** Skip conversion above this many pixels to avoid exhausting memory. */
    private const MAX_PIXELS = 36_000_000;

    private const QUALITY = 82;

    public static function available(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatetruecolor');
    }

    /**
     * @return array{binary: string, width: int, height: int}|null
     */
    public function convert(UploadedFile $file): ?array
    {
        if (! static::available()) {
            return null;
        }

        $path = $file->getRealPath();
        $info = @getimagesize($path);
        if (! $info) {
            return null;
        }

        [$width, $height, $type] = $info;
        if ($width * $height > self::MAX_PIXELS) {
            return null;
        }

        $image = match ($type) {
            IMAGETYPE_JPEG => function_exists('imagecreatefromjpeg') ? @imagecreatefromjpeg($path) : false,
            IMAGETYPE_PNG => function_exists('imagecreatefrompng') ? @imagecreatefrompng($path) : false,
            IMAGETYPE_GIF => ! $this->isAnimatedGif($path) && function_exists('imagecreatefromgif') ? @imagecreatefromgif($path) : false,
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        if (! $image) {
            return null;
        }

        if ($type === IMAGETYPE_JPEG) {
            $image = $this->applyExifOrientation($image, $path);
        }

        // Palette images can't be encoded as WebP, and transparency must survive.
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        $ok = imagewebp($image, null, self::QUALITY);
        $binary = ob_get_clean();

        $width = imagesx($image);
        $height = imagesy($image);
        imagedestroy($image);

        if (! $ok || $binary === '' || $binary === false) {
            return null;
        }

        return compact('binary', 'width', 'height');
    }

    private function isAnimatedGif(string $path): bool
    {
        $fh = @fopen($path, 'rb');
        if (! $fh) {
            return true; // can't tell: keep the original untouched
        }

        $frames = 0;
        $tail = '';
        while (! feof($fh) && $frames < 2) {
            $chunk = $tail . fread($fh, 1024 * 100);
            $frames += preg_match_all('#\x00\x21\xF9\x04.{4}\x00[\x2C\x21]#s', $chunk);
            $tail = substr($chunk, -10);
        }
        fclose($fh);

        return $frames > 1;
    }

    private function applyExifOrientation($image, string $path)
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($path)['Orientation'] ?? 1;
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated) {
            imagedestroy($image);
            return $rotated;
        }

        return $image;
    }
}
