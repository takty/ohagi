<?php
/**
 * Table
 *
 * @author Takuto Yanagida
 * @version 2023-11-29
 */

namespace ohagi;

require_once __DIR__ . '/class-record.php';

class Table {

	private $core;
	private $path;
	private $conf = array();

	public function __construct( Core $core, string $path ) {
		$this->core = $core;
		$this->path = $path;

		if ( ! file_exists( $this->path ) ) {
			$ret = mkdir( $this->path, 0644, true );
			if ( false === $ret ) {
				throw new \Exception( 'Cannot make a table directory.' );
			}
		}
		$this->load_conf();
	}

	public function get_config( $key ) {
		if ( null === $key ) {
			return $this->conf;
		}
		if ( isset( $this->conf[ $key ] ) ) {
			return $this->conf[ $key ];
		}
		return $this->core->get_config( $key );
	}

	private function load_conf() {
		$conf_path = join_path( $this->path, Core::CONFIG_NAME );
		if ( file_exists( $conf_path ) ) {
			$ret = file_get_json( $conf_path );
			if ( null === $ret ) {
				throw new \Exception( 'Cannot read "config.json".' );
			}
			$this->conf = $ret;
		}
	}

	private function save_conf() {
		$conf_path = join_path( $this->path, Core::CONFIG_NAME );
		$ret       = file_put_json( $conf_path, $this->conf );
		if ( false === $ret ) {
			throw new \Exception( 'Cannot read "config.json".' );
		}
	}


	// -------------------------------------------------------------------------


	private function make_id(): string {
		$conf = &$this->conf;
		if ( ! isset( $conf['last_id'] ) ) {
			$conf['last_id'] = 0;
		} else {
			$conf['last_id'] += 1;
		}
		return $conf['last_id'];
	}

	public function get_record_path( Record $r ) {
		$path = join_path( $this->path, $r->get_id() );
		return $path;
	}

	public function get_data_path( Record $r ) {
		$record_path = $this->get_record_path($r);
		$root        = $this->get_config('key_root');
		return join_path($record_path, "$root.json");
	}

	public function get_field_path( Record $r, $key_path, $suffix = '' ) {
		$keys     = array_filter(
			explode( '.', $key_path ),
			function ( $it ) {
				return ! empty( trim( $it ) );
			}
		);
		$sep       = $this->get_config( 'key_separator' );
		$root      = $this->get_config( 'key_root' );
		$file_name = $root . implode( $sep, $keys );

		$record_path = $this->get_record_path( $r );
		$path        = join_path( $record_path, $file_name );
		return ( $suffix !== '' ) ? "$path.$suffix" : $path;
	}

	public function on_save_record( Record &$r ) {
		if ( $r->get_id() === null ) {
			$r->set_id( $this->make_id() );
		}
		$this->save_conf();
	}


	// -------------------------------------------------------------------------


	public function get_record( string $id ): ?Record {
		$record_path = join_path( $this->path, $id );
		if ( ! file_exists( $record_path ) ) {
			return null;
		}
		$r = new Record( $this, $id );
		return $r;
	}

	public function create_record(): Record {
		$r = new Record( $this );
		return $r;
	}

	public function queryRecords( array $args ) {

	}


	// -------------------------------------------------------------------------


	public function truncate() {
		$conf           = &$this->conf;
		$conf['last_id'] = null;
		$fs             = array_diff( scandir( $this->path ), array( '.', '..' ) );

		foreach ( $fs as $f ) {
			$p = join_path( $this->path, $f );
			if ( ! is_dir( $p ) || strpos( $f, '__' ) === 0 ) {
				continue;
			}
			remove_directory( $p );
		}
		return $this;
	}
}
