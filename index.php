<?php

/**
 * Display the thumbnails for the photo strips.
 *
 * @author Justin Filip <jfilip@gmail.com>
 * @copyright 2008 Justin Filip - http://jfilip.ca/
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/lib.php');


page_header('Photo Booth');


$thumbs = array();

if (false === ($dh = opendir(DIR_OUT))) {
    error('ERROR READING FROM INPUT DIRECTORY: ' . DIR_OUT);
}

while ($file = readdir($dh)) {
    if ($file == '..' || $file == '.' || is_dir(DIR_OUT . '/' . $file) ||
        !strstr($file, '.jpg')) {

        continue;
    }

    $thumbs[] = $file;
}

natsort($thumbs);

echo '    <div class="thumbnails">' . "\n";

foreach ($thumbs as $thumb) {
    echo '      <a href="output/' . $thumb . '" rel="lightbox[photobooth]">' .
         '<img class="thumb" src="thumbnail.php?f=' . urlencode($thumb) .
         '" /></a> ' . "\n";
}

echo '    </div>' . "\n";

page_footer();
