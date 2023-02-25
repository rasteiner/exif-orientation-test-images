<?php

require_once 'vendor/autoload.php';

use lsolesen\pel\Pel;
use lsolesen\pel\PelEntryShort;
use lsolesen\pel\PelExif;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelTiff;

// generate a test image 
$im = imagecreatetruecolor(50, 100);

// allocate some colors
$red = imagecolorallocate($im, 255, 0, 0);
$green = imagecolorallocate($im, 0, 255, 0);
$blue = imagecolorallocate($im, 0, 0, 255);
$white = imagecolorallocate($im, 255, 255, 255);

// color the image with the allocated colors, subdivide the image into 4 parts (2x2)
imagefilledrectangle($im, 0, 0, 25, 50, $red);
imagefilledrectangle($im, 25, 0, 50, 50, $green);
imagefilledrectangle($im, 0, 50, 25, 100, $blue);
imagefilledrectangle($im, 25, 50, 50, 100, $white);

Pel::setJPEGQuality(100);

function generateOrientation(GdImage $base, int $orientation) {
    $copy = imagecreatetruecolor(50, 100);
    imagecopy($copy, $base, 0, 0, 0, 0, 50, 100);

    // write the orientation as text into the image
    $textColor = imagecolorallocate($copy, 0, 0, 0);
    imagestring($copy, 5, 20, 43, $orientation, $textColor);


    switch($orientation) {
        case 1:
            // do nothing
            break;
        case 2:
            // flip
            imageflip($copy, IMG_FLIP_HORIZONTAL);
            break;
        case 3:
            // rotate 180 degrees
            $copy = imagerotate($copy, 180, 0);
            break;
        case 4:
            // rotate 180 degrees, flip
            imageflip($copy, IMG_FLIP_HORIZONTAL);
            $copy = imagerotate($copy, 180, 0);
            break;
        case 5:
            // flip, then rotate 90 degrees counter-clockwise
            imageflip($copy, IMG_FLIP_HORIZONTAL);
            $copy = imagerotate($copy, 90, 0);
            break;
        case 6:
            // rotate 90 degrees counter-clockwise
            $copy = imagerotate($copy, 90, 0);
            break;
        case 7:
            // flip, then rotate 90 degrees clockwise
            imageflip($copy, IMG_FLIP_HORIZONTAL);
            $copy = imagerotate($copy, 270, 0);
            break;
        case 8:
            // rotate 90 degrees clockwise
            $copy = imagerotate($copy, 270, 0);
            break;
    }

    $jpeg = new PelJpeg($copy);
    $exif = new PelExif();
    $pelTiff = new PelTiff();
    $pelIfd0 = new PelIfd(PelIfd::IFD0);
    $pelEntry = new PelEntryShort(PelTag::ORIENTATION, $orientation);
    $pelIfd0->addEntry($pelEntry);
    $pelTiff->setIfd($pelIfd0);
    $exif->setTiff($pelTiff);
    $jpeg->setExif($exif);

    $jpeg->saveFile('test_' . $orientation . '.jpg');

    imagedestroy($copy);
}

for($i = 1; $i <= 8; $i++) {
    generateOrientation($im, $i);
}

imagedestroy($im);