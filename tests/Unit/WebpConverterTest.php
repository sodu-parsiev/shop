<?php

use App\Services\Images\WebpConverter;

function encodedImage(int $width, int $height, string $format): string
{
    $image = imagecreatetruecolor($width, $height);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 230, 0, 120, $format === 'png' ? 127 : 0));

    ob_start();
    $format === 'png' ? imagepng($image) : imagejpeg($image);

    return ob_get_clean();
}

/**
 * Inserts an EXIF block with the given Orientation tag right after the JPEG's SOI marker.
 */
function withExifOrientation(string $jpeg, int $orientation): string
{
    $payload = "Exif\0\0".'MM'.pack('nN', 0x2A, 8).pack('n', 1).pack('nnNnn', 0x0112, 3, 1, $orientation, 0).pack('N', 0);

    return substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($payload) + 2).$payload.substr($jpeg, 2);
}

test('it downscales wide photos to the max width and encodes them as webp', function () {
    $webp = (new WebpConverter)->convert(encodedImage(2400, 3000, 'jpeg'));

    expect(getimagesizefromstring($webp))->toMatchArray([0 => 1200, 1 => 1500, 'mime' => 'image/webp'])
        ->and(strlen($webp))->toBeLessThanOrEqual(WebpConverter::MAX_BYTES);
});

test('it never upscales smaller images', function () {
    $webp = (new WebpConverter)->convert(encodedImage(300, 200, 'jpeg'));

    expect(getimagesizefromstring($webp))->toMatchArray([0 => 300, 1 => 200]);
});

test('it honours a custom max width', function () {
    $webp = (new WebpConverter)->convert(encodedImage(822, 822, 'png'), 160);

    expect(getimagesizefromstring($webp))->toMatchArray([0 => 160, 1 => 160]);
});

test('it keeps png transparency', function () {
    $image = imagecreatefromstring((new WebpConverter)->convert(encodedImage(40, 40, 'png')));

    expect(imagecolorsforindex($image, imagecolorat($image, 20, 20))['alpha'])->toBe(127);
});

test('it turns sideways phone photos upright using the exif orientation', function () {
    $webp = (new WebpConverter)->convert(withExifOrientation(encodedImage(200, 100, 'jpeg'), 6));

    expect(getimagesizefromstring($webp))->toMatchArray([0 => 100, 1 => 200]);
});

test('it rejects files that are not images', function () {
    (new WebpConverter)->convert('not an image');
})->throws(InvalidArgumentException::class);
