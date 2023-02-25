<?php 
namespace rasteiner\exiftest;

use GdImage;

require_once 'vendor/autoload.php';

// an example of a test that fails
$tester = new Tester();

echo "Not applying any EXIF rotation:\n";

for($i = 1; $i <= 8; $i++) {
    $filename = "test_$i.jpg";
    $result = $tester->testFile($filename, 50, 100);
    echo "Result for $filename: $result\n";
}

echo "\n\n";

// an example of a test that succeeds (copying sourcecode from SimpleImage.php [claviska/SimpleImage](https://github.com/claviska/SimpleImage))
echo "Applying EXIF rotation:\n";

function autoOrient(string $file): GdImage  {
    $exif = exif_read_data($file);
    $gd = imagecreatefromjpeg($file);

    switch($exif['Orientation']) {
    case 1: // Do nothing!
        break;
    case 2: 
        // $this->flip('x');
        imageflip($gd, IMG_FLIP_HORIZONTAL);
        break;
    case 3:
        // $this->rotate(180);
        $gd = imagerotate($gd, 180, 0);
        break;
    case 4:
        // $this->flip('y');
        imageflip($gd, IMG_FLIP_VERTICAL);
        break;
    case 5:
        // $this->flip('y')->rotate(90);
        imageflip($gd, IMG_FLIP_VERTICAL);
        $gd = imagerotate($gd, -90, 0);
        break;
    case 6:
        // $this->rotate(90);
        $gd = imagerotate($gd, -90, 0);
        break;
    case 7:
        // $this->flip('x')->rotate(90);
        imageflip($gd, IMG_FLIP_HORIZONTAL);
        $gd = imagerotate($gd, -90, 0);
        break;
    case 8:
        // $this->rotate(-90);
        $gd = imagerotate($gd, 90, 0);
        break;
    }

    return $gd;
}

for($i = 1; $i <= 8; $i++) {
    $filename = "test_$i.jpg";
    $gd = autoOrient($filename);
    $result = $tester->testGd($gd, 50, 100);
    imagedestroy($gd);
    echo "Result for $filename: $result\n";
}
