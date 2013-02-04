<?php

/**
 * Return the JPEG data for a given thumbnail image.
 *
 * @author Justin Filip <jfilip@gmail.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__) . '/lib.php');


// Expect a URL parameter 'f' to specify the thumbnail filename
$file = urldecode(isset($_GET['f']) ? $_GET['f'] : '');

if (empty($file)) {
    error('INVALID FILE SPECIFIED');
}

// Input sanitize, ensure that a valid thumbnail filename was specified.
if (!preg_match('/^[0-9a-f]{32}\.jpg$/i', $file)) {
	error('INVALID FILENAME SPECIFICD');
}

get_thumbnail($file);
