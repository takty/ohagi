<?php
/**
 * File System Utilities
 *
 * @author Takuto Yanagida
 * @version 2023-11-29
 */

namespace ohagi;

function join_path( string $path1, string $path2 ): string {
	return rtrim( $path1, '\\/' ) . DIRECTORY_SEPARATOR . $path2;
}

function ensure_directory_existence( string $path ): void {
	if ( ! file_exists( $path ) ) {
		$ret = mkdir( $path, 0644, true );
		if ( false === $ret ) {
			throw new \Exception( 'Cannot make a directory.' );
		}
	}
}

function remove_directory( string $dir ): bool {
	$fs = array_diff(scandir( $dir ), array( '.', '..' ) );
	foreach ( $fs as $f ) {
		$path = join_path( $dir, $f );
		if ( is_dir( $path ) ) {
			remove_directory( $path );
		} else {
			unlink( $path );
		}
	}
	return rmdir( $dir );
}

function file_get_json( string $path ): ?array {
	$ret = file_get_contents( $path );
	if ( false === $ret ) {
		return null;
	}
	return json_decode( $ret, true );
}

function file_put_json( string $path, array $value ): bool {
	$str = json_encode( $value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( false === $str ) {
		return false;
	}
	return file_put_contents( $path, $str, LOCK_EX );
}
