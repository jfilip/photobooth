<?php

/**
 * Library functions for handling output and image processing.
 *
 * @author Justin Filip <jfilip@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('DIR_IN',       dirname(__FILE__) . '/input');
define('DIR_OUT',      dirname(__FILE__) . '/output');
define('DIR_THUMB',    dirname(__FILE__) . '/thumbs');
define('THUMB_LONG',   1000);
define('THUMB_SHORT',  200);
define('STRIP_LONG',   400);
define('STRIP_SHORT',  300);
define('STRIP_BORDER', 20);


/**
 * Display generic HTML page header markup.
 *
 * @param sting $title The HTML page title attribute
 */
function page_header($title) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title><?php echo $title; ?></title>
  <script type="text/javascript" src="js/prototype.js"></script>
  <script type="text/javascript" src="js/scriptaculous.js?load=effects,builder"></script>
  <script type="text/javascript" src="js/lightbox.js"></script>
  <link type="text/css" rel="stylesheet" href="css/styles.css" />
  <link type="text/css" rel="stylesheet" href="css/lightbox.css" media="screen" />
</head>
<body>
<?php
}

/**
 * Display generic HTML page footer markup.
 */
function page_footer() {
?>
</body>
</html>
<?php
}

/**
 * Debugging function to output any type of variable/strcture wrapped in PRE tags or easy readability.
 *
 * @param mixed $obj The variable, object, array, etc. to displya in the HTML output.
 */
function print_object($obj) {
    echo '<pre>';
    print_r($obj);
    echo '</pre>';
}

/**
 * Display a simple error message and stop program execution.
 *
 * @param string $msg The error message to display.
 */
function error($msg) {
    echo '<h1>' . $msg . '</h1>';
    die;
}

/**
 * Create a thumbnail from a larger image.
 *
 * @param string $file A filename within the output directory to create a thumbnail for.
 */
function make_thumbnail($file) {
    $fpath = DIR_OUT . '/' . $file;
    $tpath = DIR_THUMB . '/' . $file;

    $img = imagecreatefromjpeg($fpath);

    $sizex = imagesx($img);
    $sizey = imagesy($img);
    $scalex = 0;
    $scaley = 0;

    // Adjust the scaling so that the longest side is always scaled to the same number of pixels.
    if ($sizex >= $sizey) {
        $scalex = THUMB_LONG;
        $scaley = THUMB_SHORT;
    } else {
        $scalex = THUMB_SHORT;
        $scaley = THUMB_LONG;
    }

    // Create the scaled copy of the image.
    $imgscale = imagecreatetruecolor($scalex, $scaley);
    imagecopyresampled($imgscale, $img, 0, 0, 0, 0, $scalex, $scaley, $sizex, $sizey);

    // Write out the scale image and free up memory.
    imagejpeg($imgscale, $tpath, 80);
    imagedestroy($img);
    imagedestroy($imgscale);
}

/**
 * Adjust the contrast of a given image file within memory.
 *
 * @param resource $img A reference to an image handler that we are going to modify.
 * @param int $percent The percentage to adjust the contrast of the image to (+/-).
 */
function image_contrast(&$img, $percent) {
    $x       = imagesx($img);
    $y       = imagesy($img);
    $percent = $percent / 100;

    // Sequentially move through the pixels of the image
    for ($i = 0; $i < $x; $i++){
        for ($j = 0; $j < $y; $j++){
            // Get the RGB value for the current pixel
            $rgb = imagecolorat($img, $i, $j);

            // Extract each value for R, G, B from the current pixel
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            // Adjust the contrast of the given pixel either positively or negatively
            $r = $percent > 1 ? min(floor($r * $percent), 255) : max(ceil($r * $percent), 0);
            $g = $percent > 1 ? min(floor($g * $percent), 255) : max(ceil($g * $percent), 0);
            $b = $percent > 1 ? min(floor($b * $percent), 255) : max(ceil($b * $percent), 0);
            
            // Set the new RGB value for this pixel in the image
            imagesetpixel($img, $i, $j, imagecolorallocate($img, $r, $g, $b));
        }
    }
}

/**
 * Convert an image to grayscale based on the value of the source colour image.
 *
 * @param resource $img A reference to an image handler that we are going to modify.
 */
function image_grayscale(&$img) {
    $x = imagesx($img);
    $y = imagesy($img);

    // Convert the image to grayscale
    for ($i = 0; $i < $x; $i++) {
        for ($j = 0; $j < $y; $j++) {
            // Get the RGB value for the current pixel
            $rgb = imagecolorat($img, $i, $j);

            // Extract each alue for R, G, B
            $rr = ($rgb >> 16) & 0xFF;
            $gg = ($rgb >> 8) & 0xFF;
            $bb = $rgb & 0xFF;

            // Extract each value for R, G, B from the current pixel
            $g = round(($rr + $gg + $bb) / 3);

            // Grayscale values have R = G = B = v (v = overall pixel value)
            $val = imagecolorallocate($img, $g, $g, $g);

            // Set the gray value for this pixel in this image
            imagesetpixel($img, $i, $j, $val);
        }
    }
}

/**
 * Rotate an image 90 degrees clockwise
 *
 * @param resource $img An image handler that we are going to modify.
 * @return resource A new image handler that has been rotated.
 */
function image_rotate($src) {
    // Get the source dimensions.
    $sx = imagesx($src);
    $sy = imagesy($src);

    // Create the rotated image handler with swapped dimensions.
    $rot = imagecreatetruecolor($sy, $sx);

    for ($i = 0; $i < $sx; $i++) {
        for ($j = 0; $j < $sy; $j++) {
            // Get the source pixel at the original X,Y coordinates
            $rgb = imagecolorat($src, $i, $j);

            // Extract each value for R, G, B from the current pixel
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;

            // Set the pixel values at the new X,Y coordinates in the new image (rot: +90deg)
            imagesetpixel($rot, $sy - $j, $i, imagecolorallocate($rot, $r, $g, $b));
        }
    }

    // Free up the memory used by the original image.
    imagedestroy($src);

    return $rot;
}

/**
 * Return a JPEG for a given image thumbnail. This allows the thumbnail images to be
 * cached as opcode using something like PHP's APC.
 *
 * @param string $file A filename within the output directory to get / send the thumbnail for.
 * @return bin The binary data for the JPEG of the given thumbnail to be sent to a browser.
 */
function get_thumbnail($file) {
    $fpath = DIR_OUT . '/' . $file;
    $tpath = DIR_THUMB . '/' . $file;

    if (!file_exists($tpath)) {
        if (file_exists($fpath)) {
            make_thumbnail($file);
        }
    }

    if (!file_exists($tpath)) {
        return false;
    }

    header('Content-type: image/jpeg');

    $img = imagecreatefromjpeg($tpath);
    imagejpeg($img, NULL, 80);
    imagedestroy($img);
}

/**
 * Given a source input image, process the image and return the resource handler
 * for the final image that will be used in a strip.
 *
 * @param string $file A filename within the input directory to process for inclusion
 *                     within an image strip.
 * @return resource A new image handler for the processed input image.
 */
function get_strip_image($file) {
    $fpath = DIR_IN . '/' . $file;

    $img = imagecreatefromjpeg($fpath);

    $sizex = imagesx($img);
    $sizey = imagesy($img);
    $scalex = 0;
    $scaley = 0;

    // Get new dimensions for the scaled-down image based on the length of the
    // longest side in the source image
    if ($sizex >= $sizey) {
        $scalex = STRIP_LONG;
        $scaley = STRIP_SHORT;
    } else {
        $scalex = STRIP_SHORT;
        $scaley = STRIP_LONG;
    }

    // Create the scaled copy of the image.
    $imgscale = imagecreatetruecolor($scalex, $scaley);

    // Copy the resized source image into the destination resource handler
    imagecopyresampled($imgscale, $img, 0, 0, 0, 0, $scalex, $scaley, $sizex, $sizey);

    // Free up memory used from the source image.
    imagedestroy($img);

    // Get the +90deg rotated image based on the source image
    $imgrotate = image_rotate($imgscale);

    return $imgrotate;
}

/**
 * Get the filename for an image strip based on a given set of input parameters.
 *
 * A strip consistens of four sequental grayscale images in portrait orientation
 * assembled into a larger image with a white border surrounding each image.
 *
 * The strip is meant to look like the output from a photobooth.
 *
 * @param array $files An array of four images from the input directory.
 * @return string The filename for the assembled strip image.
 */
function make_strip($files) {
    $cx = 0;
    $cy = 0;

    $images = array();

    // The strip filename is an MD5 hash based on the concatenation of the four input
    // image filenames.
    $fname = DIR_OUT . '/' . md5(implode('', $files)) . '.jpg';

    // If the strip image already exists then just return the filename now.
    if (file_exists($fname)) {
        return $fname;
    }

    // Otherwise we have to create the strip image.
    foreach ($files as $file) {
        $fpath = DIR_IN . '/' . $file;

        // Get the processed image resouce handler for this input file.
        $img = get_strip_image($file);

        $sizex = imagesx($img);
        $sizey = imagesy($img);

        $images[] = $img;

        // Determine the dimensions of the container image based on the processed
        // source images (40px border around the images).
        if ($sizey >= $sizex) {
            $cx  = $sizex + 40;
            $cy += $sizey + STRIP_BORDER;
        } else {
            $cx += $sizex + STRIP_BORDER;
            $cy  = $sizey + 40;
        }
    }

    // Make sure we account for the border width on the right and bottom sides.
    if ($cy > $cx) {
        $cy += STRIP_BORDER;
    } else {
        $cx += STRIP_BORDER;
    }

    // Create the image for the strip container and set the background to be
    // solid white.
    $strip = imagecreatetruecolor($cx, $cy);
    $col   = imagecolorallocate($strip, 255, 255, 255);
    imagefill($strip, 1, 1, $col);

    $curx = 0;
    $cury = 0;

    foreach ($images as $image) {
        // Insert the image with the border width "margin" taken into account.
        if ($cy > $cx) {
            $curx  = STRIP_BORDER;
            $cury += STRIP_BORDER;
        } else {
            $curx += STRIP_BORDER;
            $cury  = STRIP_BORDER;
        }

        // Get the processed image dimentions and adjust the contrast.
        $sizex = imagesx($image);
        $sizey = imagesy($image);
        image_contrast($image, 70);

        // Copy the processed image into the strip.
        imagecopyresampled($strip, $image, $curx, $cury, 0, 0, $sizex, $sizey, $sizex, $sizey);
        imagedestroy($image);

        // Increment our coordinates for the start of the next image to be inserted.
        if ($cy > $cx) {
            $cury += $sizey;
        } else {
            $curx += $sizex;
        }
    }

    // Convert the entire image string to grayscale and output the JPEG file.
    image_grayscale($strip);
    imagejpeg($strip, $fname, 100);
    imagedestroy($strip);

    return $fname;
}
