<?php

/**
 * Process all of the images in the input directory creating the image strips.
 *
 * Note: the input directory files will be processed in batches of four,
 * ordered alphanumerically in descending order.
 *
 * @author Justin Filip <jfilip@gmail.com>
 * @copyright 2008 Justin Filip - http://jfilip.ca/
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once dirname(__FILE__) . '/lib.php';


define('BATCH_SIZE', 4);


if (false === ($dh = opendir(DIR_IN))) {
    error('ERROR READING FROM INPUT DIRECTORY: ' . DIR_IN);
}

$files = array();
$batch = array();

// Get the list of all the image files within the input directory
while ($file = readdir($dh)) {
    // Ignore directories and non-image files.
    if ($file == '..' || $file == '.' || is_dir(DIR_IN . '/' . $file) || !stristr($file, '.jpg')) {
        continue;
    }

    $files[] = $file;
}

// Sort the file listing.
natsort($files);

// Assemble each batch of four images and generate the photo strip for them. 
foreach ($files as $file) {
    $batch[] = $file;

    if (count($batch) == BATCH_SIZE) {
        print_object('$batch(' . count($batch) . '):');
        print_object($batch);
        make_strip($batch);
        $batch = array();
    }
}
