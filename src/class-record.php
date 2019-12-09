<?php
namespace Ohagi;
/**
 *
 * Record
 *
 * @author Takuto Yanagida
 * @version 2019-12-09
 *
 */


class Record {

	private $__tbl = null;
	private $__id  = null;

	public $__data  = [];
	public $__large = [];

	public $__data_updated = false;

	public function __construct(Table $table) {
		$this->__tbl = $table;
	}

	public function setId(string $id): Record {
		$this->__id = $id;
		return $this;
	}

	// public function setData(array $data): Record {
	// 	$this->__data = $data;
	// 	return $this;
	// }

	public function getTable(): Table {
		return $this->__tbl;
	}

	public function getId(): ?string {
		return $this->__id;
	}

	// public function getData(): array {
	// 	return $this->__data;
	// }

	public function save(): void {
		$this->__tbl->saveRecord($this);
	}


	// -------------------------------------------------------------------------


	public function __set(string $key, string $value): string {
		$val = $this->__data[$key] = $value;
		$this->__data_updated = true;
		return $val;
	}

	public function &__get(string $key): ?string {
		if (!array_key_exists($key, $this->__data)) return null;
		return $this->__data[$key];
	}

	public function __isset(string $key): boolean {
		return isset($this->__data[$key]);
	}

	public function __unset(string $key): void {
		unset($this->__data[$key]);
	}


	// -------------------------------------------------------------------------


	public function get(string $keyPath): ?string {
		$keys = explode('.', $keyPath);
		$d = $this->__data;

		foreach ($keys as $key) {
			if (empty(trim($key))) return null;
			if (!array_key_exists($key, $d)) return null;
			$d = $d[$key];
		}
		return $d;
	}

	public function set(string $keyPath, string $value): void {
		$keys = explode('.', $keyPath);
		$d = &$this->__data;
		$last = array_pop($keys);

		foreach ($keys as $key) {
			if (empty(trim($key))) return;
			if (!array_key_exists($key, $d)) $d[$key] = [];
			$d = &$d[$key];
		}
		$d[$last] = $value;
		$this->__data_updated = true;
	}


	// -------------------------------------------------------------------------


	private function getLarge(string $keyPath): ?string {
		if (!isset($this->__large[$keyPath])) {
			$ret = $this->__tbl->loadLargeField($this, $keyPath);
			if (!$ret) return null;
		}
		if (!isset($this->__large[$keyPath])) return null;
		$ent = $this->__large[$keyPath];
		return $ent['value'];
	}

	private function setLarge(string $keyPath, string $value, string $suffix = ''): void {
		$ent = [
			'value'   => $value,
			'suffix'  => $suffix,
			'updated' => true,
		];
		$this->__large[$keyPath] = $ent;
	}


	// -------------------------------------------------------------------------


	private function getDirectory(string $keyPath) : ?string {
		$filePath = $this->__tbl->convertKeyPathToFilePath($this, $keyPath);
		return $filePath;
	}

	private function setDirectory(string $keyPath): ?string {
		$filePath = $this->__tbl->createDirectory($this, $keyPath);
		return $filePath;
	}

}
