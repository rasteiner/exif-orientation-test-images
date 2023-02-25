<?php 

namespace rasteiner\exiftest;

use GdImage;
use InvalidArgumentException;
use lsolesen\pel\Pel;
use lsolesen\pel\PelEntryShort;
use lsolesen\pel\PelExif;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelTiff;

class Generator {
    protected GdImage $base;

    static function saveAll(int $width, int $height, string $outputDir = '.') {
        $generator = new Generator($width, $height, $outputDir);

        for($i = 1; $i <= 8; $i++) {
            $generator->saveOrientation($i);
        }
    }

    function __construct(protected int $width, protected int $height, public string $outputDir = '.') {
        // width and height must be at least 2x2
        if($width < 2 || $height < 2) {
            throw new InvalidArgumentException('Width and height must be at least 2x2');
        }

        // generate a test image 
        $im = imagecreatetruecolor($width, $height);

        // allocate some colors
        $red = imagecolorallocate($im, 255, 0, 0);
        $green = imagecolorallocate($im, 0, 255, 0);
        $blue = imagecolorallocate($im, 0, 0, 255);
        $white = imagecolorallocate($im, 255, 255, 255);

        // color the image with the allocated colors, subdivide the image into 4 parts (2x2)
        imagefilledrectangle($im, 0, 0, $width / 2,  $height / 2, $red); // top left
        imagefilledrectangle($im, $width / 2, 0, $width, $height / 2, $green); // top right
        imagefilledrectangle($im, 0, $height / 2, $width / 2, $height, $blue); // bottom left
        imagefilledrectangle($im, $width / 2, $height / 2, $width, $height, $white); // bottom right

        $this->base = $im;
    }

    function __destruct() {
        imagedestroy($this->base);
    }
    
    function saveOrientation(int $orientation, string $filename = null) {
        if(!is_dir($this->outputDir)) {
            // try to create the output directory
            if(!mkdir($this->outputDir, 0777, true)) {
                throw new InvalidArgumentException('Output directory does not exist and could not be created');
            }
        }

        $w = imagesx($this->base);
        $h = imagesy($this->base);

        $copy = imagecreatetruecolor($w, $h);
        imagecopy($copy, $this->base, 0, 0, 0, 0, $w, $h);

        if($w > 20 && $h > 25) {
            // write the orientation as text into the image
            $textColor = imagecolorallocate($copy, 0, 0, 0);
            imagestring($copy, 5, $w / 2 - 5, $h / 2 - 7, $orientation, $textColor);
        }

        if($orientation >= 5) {
            $copy = imagerotate($copy, 270, 0);
        }

        switch($orientation) {
            case 1: case 8:
                // do nothing
                break;
            case 2: case 5:
                imageflip($copy, IMG_FLIP_HORIZONTAL);
                break;
            case 3: case 6:
                imageflip($copy, IMG_FLIP_BOTH);
                break;
            case 4: case 7:
                imageflip($copy, IMG_FLIP_VERTICAL);
                break;
        }

        Pel::setJPEGQuality(100);

        $jpeg = new PelJpeg($copy);
        $exif = new PelExif();
        $pelTiff = new PelTiff();
        $pelIfd0 = new PelIfd(PelIfd::IFD0);
        $pelEntry = new PelEntryShort(PelTag::ORIENTATION, $orientation);
        $pelIfd0->addEntry($pelEntry);
        $pelTiff->setIfd($pelIfd0);
        $exif->setTiff($pelTiff);
        $jpeg->setExif($exif);

        if($filename === null) {
            $filename = "test_$orientation.jpg";
        }

        $jpeg->saveFile(rtrim($this->outputDir, '/') . "/$filename");

        imagedestroy($copy);
    }
}
