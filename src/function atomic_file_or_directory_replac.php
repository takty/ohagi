<?php
function atomic_file_or_directory_replace($path, $is_directory) {
	$is_file = is_file($path);

	$temp_filename = tempnam(dirname($path), basename($path));

	if ($is_file) {
		file_put_contents($temp_filename, file_get_contents($path));
	} else {
		copy($path, $temp_filename);
	}

	if (!rename($temp_filename, $path)) {
		throw new Exception('Failed to replace file or directory.');
	}
}
