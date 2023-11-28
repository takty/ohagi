<?php
/**
 * Core
 *
 * @author Takuto Yanagida
 * @version 2023-11-29
 */

namespace ohagi;

require_once __DIR__ . '/filesystem.php';
require_once __DIR__ . '/class-table.php';

class Core {

	const CONFIG_NAME = 'config.json';

	private $path;
	private $conf;

	public function __construct( string $path ) {
		$this->path = $path;

		if ( ! file_exists( $this->path ) ) {
			$ret = mkdir( $this->path, 0644, true );
			if ( false === $ret ) {
				throw new \Exception( 'Cannot make a db directory.' );
			}
		}
		$this->load_conf();
	}

	private function load_conf() {
		$conf_path = join_path( $this->path, self::CONFIG_NAME );
		if ( ! file_exists( $conf_path ) ) {
			$ret = file_put_json( $conf_path, $this->make_default_conf() );
			if ( false === $ret ) {
				throw new \Exception( 'Cannot make "config.json".' );
			}
		}
		$ret = file_get_json( $conf_path );
		if ( null === $ret ) {
			throw new \Exception('Cannot read "config.json".');
		}
		$this->conf = $ret;
	}

	private function make_default_conf() {
		return array(
			'key_separator' => '@',
			'key_root'      => '@',
		);
	}

	public function get_config( $key = null ) {
		if ( null === $key ) {
			return $this->conf;
		}
		if ( isset( $this->conf[ $key ] ) ) {
			return $this->conf[ $key ];
		}
		return null;
	}

	public function get_table( string $table_name ) {
		$table_path = join_path( $this->path, $table_name );
		return new Table( $this, $table_path );
	}
}
