<?php 

namespace rasteiner\exiftest;

use GdImage;

/**
 * Provides helper functions to test if a given image has the correct orientation (ignoring EXIF data).
 * In this sense, it tests if the exif tag has been correctly removed from the image.
 * @package rasteiner\exiftest
 */
class Tester {
    const RESULT_OK = 0;
    const RESULT_WRONG_ORIENTATION = 1;
    const RESULT_WRONG_SIZE = 2;

    protected static function colorToRGB(bool|int $color): array {
        if($color === false) {
            throw new \InvalidArgumentException('Invalid color');
        }

        return [
            ($color >> 16) & 0xFF,
            ($color >> 8) & 0xFF,
            $color & 0xFF
        ];
    }

    function testFile(string $filename, int $expectWidth, int $expectHeight): int {
        $gd = imagecreatefromjpeg($filename);
        if($gd === false) {
            throw new \InvalidArgumentException('Could not open image');
        }

        $result = $this->testGd($gd, $expectWidth, $expectHeight);
        imagedestroy($gd);
        return $result;
    }

    function testGd(GdImage $gd, int $expectWidth, int $expectHeight): int {
        $width = imagesx($gd);
        $height = imagesy($gd);
        if($width !== $expectWidth && $height !== $expectHeight) {
            return self::RESULT_WRONG_SIZE;
        }

        // get pixel colors at the corners
        $topLeft = self::colorToRGB(imagecolorat($gd, 0, 0));
        $topRight = self::colorToRGB(imagecolorat($gd, $width - 1, 0));
        $bottomLeft = self::colorToRGB(imagecolorat($gd, 0, $height - 1));
        $bottomRight = self::colorToRGB(imagecolorat($gd, $width - 1, $height - 1));

        // check if the colors are as expected
        // top left should mostly be red
        if($topLeft[0] < 200 || $topLeft[1] > 50 || $topLeft[2] > 50) {
            return self::RESULT_WRONG_ORIENTATION;
        }
        // top right should mostly be green
        if($topRight[0] > 50 || $topRight[1] < 200 || $topRight[2] > 50) {
            return self::RESULT_WRONG_ORIENTATION;
        }
        // bottom left should mostly be blue
        if($bottomLeft[0] > 50 || $bottomLeft[1] > 50 || $bottomLeft[2] < 200) {
            return self::RESULT_WRONG_ORIENTATION;
        }
        // bottom right should mostly be white
        if($bottomRight[0] < 200 || $bottomRight[1] < 200 || $bottomRight[2] < 200) {
            return self::RESULT_WRONG_ORIENTATION;
        }

        // the image seems to be ok
        return self::RESULT_OK;
    }
}