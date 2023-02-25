# Exif Orientation test images

This repository contains a set of test images for the Exif Orientation tag together with a PHP script to generate them.
The images aren't pretty, but are designed to be small and easy to use for testing since they are made of a grid of 2 x 2 colored rectangles.
Each image also shows the Orientation tag value (a number from 1 to 8) written in its center.

In the correct orientation, the rectangles should be in the following order:

    1 2
    3 4

Where 1 is Red (#F00), 2 is Green (#0F0), 3 is Blue (#00F) and 4 is White (#FFF).

## Installation

```bash
git clone https://github.com/rasteiner/exif-orientation-test-images.git exif-test-images
cd exif-test-images
composer install
```
    
## Usage

```bash
php generate.php
```

Due to JPEG compression artifacts, the images may not be exactly as described above. When used for automated testing, it is recommended to use a tolerance when matching the colors (i.e. "mostly red / green / blue / white").

## License

MIT

This script accesses the [PHP Exif library (Pel)](https://github.com/pel/pel) which is licensed under the [GNU General Public License v2.0](https://github.com/pel/pel/blob/master/COPYING). Pel is distributed separately from this repository via Composer.