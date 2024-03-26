<?php

define( 'DB_DIR', './your_common_directory/' );

function convert_to_directory( $id ) {
	$orig_path = DB_DIR . $id . '.json';
	$new_dir   = DB_DIR . $id . '/';
	$new_path  = $new_dir . '_.json';

	if ( ! file_exists( $orig_path ) ) {
		return 'File with the specified ID not found.';
	}
	if ( file_exists( $new_path ) || file_exists( $new_dir ) ) {
		return 'File or directory with the specified ID already exists.';
	}

	$json = file_get_contents( $orig_path );

	if ( false === $json ) {
		return 'Failed to read JSON data from the original file.';
	}
	if ( ! mkdir( $new_dir, 0777, true ) ) {
		return 'Failed to create the new directory.';
	}
	if ( ! file_put_contents( $new_path, $json ) ) {
		unlink( $new_dir );
		return 'Failed to write JSON data to the new file.';
	}
	if ( ! unlink( $orig_path ) ) {
		unlink( $new_path );
		rmdir( $new_dir );
		return 'Failed to delete the original file.';
	}
	return 'Conversion completed.';
}

function convert_to_file( $id ) {
	$orig_path = DB_DIR . $id . '/_.json';
	$orig_dir  = DB_DIR . $id;
	$new_path  = DB_DIR . $id . '.json';

	if ( ! file_exists( $orig_path ) ) {
		return 'New file with the specified ID not found.';
	}
	if ( file_exists( $new_path ) ) {
		return 'File with the specified ID already exists.';
	}

	$json = file_get_contents( $orig_path );

	if ( false === $json ) {
		return 'Failed to read JSON data from the new file.';
	}
	if ( ! file_put_contents( $new_path, $json ) ) {
		return 'Failed to write JSON data to the original file.';
	}
	if ( ! unlink( $orig_path ) ) {
		unlink( $new_path );
		return 'Failed to delete the new file.';
	}
	if ( ! rmdir( $orig_dir ) ) {
		unlink( $new_path );
		file_put_contents( $orig_path, $json );
		return 'Failed to delete the directory with the specified ID.';
	}
	return 'Conversion completed.';
}
