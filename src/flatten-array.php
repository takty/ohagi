<?php
/**
 * Functions for flattening and unflattening arrays.
 *
 * These functions provide functionality to flatten a nested array and unflatten a flattened array.
 * The flattening operation combines keys using a specified separator, and the unflattening operation
 * restores the array to its original nested structure.
 *
 * @package ArrayOperations
 * @author Takuto Yanagida
 * @version 2023-12-02
 */

/**
 * Flatten a nested array with a specified separator.
 *
 * This function takes a nested array and flattens it, combining keys with a specified separator.
 * It uses a stack-based approach to process nested arrays.
 *
 * @param array  $arr       The nested array to flatten.
 * @param string $separator The separator used to combine keys. Default is '/'.
 *
 * @return array The flattened array.
 */
function flatten_array( array $arr, string $separator = '/' ): array {
	$result = array();

	$stack = array(
		array(
			'array' => $arr,
			'path'  => '',
		),
	);

	while ( ! empty( $stack ) ) {
		$current       = array_pop( $stack );
		$current_array = $current['array'];
		$path          = $current['path'];

		foreach ( $current_array as $key => $value ) {
			$new_path = $path ? "{$path}{$separator}{$key}" : $key;
			if ( is_array( $value ) ) {
				$stack[] = array(
					'array' => $value,
					'path'  => $new_path,
				);
			} else {
				$result[ $new_path ] = $value;
			}
		}
	}

	return $result;
}

/**
 * Unflatten a flattened array.
 *
 * This function takes a flattened array and restores it to its original nested structure.
 * It uses a loop to recreate the nested arrays based on the specified separator.
 *
 * @param array  $arr       The flattened array to unflatten.
 * @param string $separator The separator used to combine keys. Default is '/'.
 *
 * @return array The unflattened nested array.
 */
function unflatten_array( array $arr, string $separator = '/' ): array {
	$result = array();

	foreach ( $arr as $key => $value ) {
		$keys          = explode( $separator, $key );
		$current_array = &$result;

		foreach ( $keys as $i => $current_key ) {
			if ( ! isset( $current_array[ $current_key ] ) ) {
				if ( count( $keys ) - 1 === $i ) {
					$current_array[ $current_key ] = $value;
				} else {
					$current_array[ $current_key ] = array();
				}
			}
			$current_array = &$current_array[ $current_key ];
		}
	}

	return $result;
}
