<?php

namespace App\Services\Images;

use GdImage;
use InvalidArgumentException;

/**
 * Re-encodes photos as WebP light enough for the storefront: downscaled to a
 * maximum width and compressed until the file fits under MAX_BYTES.
 */
class WebpConverter
{
    public const MAX_BYTES = 200 * 1024;

    public const DEFAULT_MAX_WIDTH = 1200;

    private const QUALITY_STEPS = [80, 72, 64, 56];

    public function convert(string $contents, int $maxWidth = self::DEFAULT_MAX_WIDTH): string
    {
        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            throw new InvalidArgumentException('The file is not a supported image.');
        }

        $image = $this->downscale($this->applyExifOrientation($image, $contents), $maxWidth);

        foreach (self::QUALITY_STEPS as $quality) {
            $webp = $this->encode($image, $quality);

            if (strlen($webp) <= self::MAX_BYTES) {
                break;
            }
        }

        return $webp;
    }

    /**
     * Phone photos are often stored sideways with an EXIF rotation flag, which GD ignores
     * and re-encoding drops — bake the rotation into the pixels instead.
     */
    private function applyExifOrientation(GdImage $image, string $contents): GdImage
    {
        if (! str_starts_with($contents, "\xFF\xD8") || ! function_exists('exif_read_data')) {
            return $image;
        }

        $stream = fopen('php://memory', 'w+b');
        fwrite($stream, $contents);
        rewind($stream);
        $exif = @exif_read_data($stream);
        fclose($stream);

        $angle = match ($exif['Orientation'] ?? null) {
            3 => 180,
            6 => 270,
            8 => 90,
            default => 0,
        };

        return $angle === 0 ? $image : imagerotate($image, $angle, 0);
    }

    private function downscale(GdImage $image, int $maxWidth): GdImage
    {
        imagepalettetotruecolor($image);

        if (imagesx($image) > $maxWidth) {
            $image = imagescale($image, $maxWidth, -1, IMG_BICUBIC);
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    private function encode(GdImage $image, int $quality): string
    {
        $stream = fopen('php://memory', 'w+b');
        imagewebp($image, $stream, $quality);
        rewind($stream);
        $webp = stream_get_contents($stream);
        fclose($stream);

        return $webp;
    }
}
