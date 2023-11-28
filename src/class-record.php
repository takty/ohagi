<?php
/**
 * Record
 *
 * @author Takuto Yanagida
 * @version 2023-11-29
 */

namespace ohagi;

class Record {

	private $tbl_ = null;
	private $id_  = null;

	public $data_  = [];
	public $large_ = [];

	public $data_updated_ = false;

	public function __construct( Table $table, string $id = null ) {
		$this->tbl_ = $table;
		if ( $id ) {
			$this->id_ = $id;
			$this->load_fields();
		}
	}

	public function set_id( string $id ): Record {
		$this->id_ = $id;
		return $this;
	}

	public function get_table(): Table {
		return $this->tbl_;
	}

	public function get_id(): ?string {
		return $this->id_;
	}

	public function save(): void {
		$this->tbl_->on_save_record( $this );
		$this->save_fields();
		$this->save_large_fields();
	}


	// -------------------------------------------------------------------------


	public function __set( string $key, string $value ): string {
		$this->data_[ $key ] = $value;
		$this->data_updated_ = true;
		return $value;
	}

	public function &__get( string $key ): ?string {
		if ( ! array_key_exists( $key, $this->data_ ) ) {
			return null;
		}
		return $this->data_[ $key ];
	}

	public function __isset( string $key ): bool {
		return isset( $this->data_[ $key ] );
	}

	public function __unset( string $key ): void {
		unset( $this->data_[ $key ] );
	}


	// -------------------------------------------------------------------------


	public function get( string $key_path ): ?string {
		$keys = explode( '.', $key_path );
		$d    = $this->data_;

		foreach ( $keys as $key ) {
			if ( empty( trim( $key ) ) ) {
				return null;
			}
			if ( ! array_key_exists( $key, $d ) ) {
				return null;
			}
			$d = $d[ $key ];
		}
		return $d;
	}

	public function set( string $key_path, string $value ): void {
		$keys = explode( '.', $key_path );
		$d    = &$this->data_;
		$last = array_pop( $keys );

		foreach ($keys as $key) {
			if ( empty( trim( $key ) ) ) {
				return;
			}
			if ( ! array_key_exists($key, $d ) ) {
				$d[ $key ] = [];
			}
			$d = &$d[ $key ];
		}
		$d[ $last ] = $value;
		$this->data_updated_ = true;
	}


	// -------------------------------------------------------------------------


	public function getLarge( string $key_path ): ?string {
		if ( ! isset( $this->large_[ $key_path ] ) ) {
			$ret = $this->load_large_field( $key_path );
			if ( ! $ret ) {
				return null;
			}
		}
		$ent = $this->large_[ $key_path ];
		return $ent['value'];
	}

	public function setLarge( string $key_path, string $value, string $suffix = '' ): void {
		$ent = array(
			'value'   => $value,
			'suffix'  => $suffix,
			'updated' => true,
		);
		$this->large_[ $key_path ] = $ent;
	}


	// -------------------------------------------------------------------------


	private function get_directory( string $key_path ): ?string {
		$field_path = $this->tbl_->get_field_path( $this, $key_path );
		ensure_directory_existence( $field_path );
		return $field_path;
	}


	// -------------------------------------------------------------------------


	private function load_fields(): void {
		$path = $this->tbl_->get_data_path( $this );
		$ret  = file_get_contents($path);
		if ( false === $ret ) {
			throw new \Exception("Cannot read '$path'.");
		}
		$this->data_ = json_decode( $ret, true );
		$this->data_updated_ = false;
	}

	private function save_fields( bool $force = false ): void {
		if ( ! $force && !$this->data_updated_) {
			return;
		}
		$record_path = $this->tbl_->get_record_path( $this );
		ensure_directory_existence( $record_path );
		$path = $this->tbl_->get_data_path( $this );
		$str  = json_encode( $this->data_, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		$ret  = file_put_contents( $path, $str, LOCK_EX );
		if ( false === $ret ) {
			throw new \Exception( "Cannot write '$path'." );
		}
		$this->data_updated_ = false;
	}


	// -------------------------------------------------------------------------


	private function load_large_field( string $key_path ): bool {
		$field_path = $this->tbl_->get_field_path( $this, $key_path );
		$fs         = glob( "$field_path.*" );
		if ( $fs === false || empty( $fs ) ) {
			return false;
		}
		$ret = file_get_contents( $fs[0] );
		if ( false === $ret ) {
			throw new \Exception( "Cannot read '$path'." );
		}
		$this->large_[ $key_path ] = $ret;
		return true;
	}

	private function save_large_fields(): void {
		foreach ( $this->large_ as $key => &$ent ) {
			if ( $ent['updated'] ) {
				$this->save_large_field( $key, $ent );
				unset( $ent['updated'] );
			}
		}
	}

	private function save_large_field( string $key_path, array $ent ): void {
		$path = $this->tbl_->get_field_path( $this, $key_path, $ent['suffix'] );
		$ret  = file_put_contents( $path, $ent['value'], LOCK_EX );
		if ( false === $ret ) {
			throw new \Exception( "Cannot write '$path'." );
		}
	}
}
