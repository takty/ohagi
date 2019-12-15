<?php
namespace Ohagi;
/**
 *
 * Record
 *
 * @author Takuto Yanagida
 * @version 2019-12-15
 *
 */


class Record {

	private $__tbl = null;
	private $__id  = null;

	public $__data  = [];
	public $__large = [];

	public $__data_updated = false;

	public function __construct(Table $table, string $id = null) {
		$this->__tbl = $table;
		if ($id) {
			$this->__id = $id;
			$this->loadFields();
		}
	}

	public function setId(string $id): Record {
		$this->__id = $id;
		return $this;
	}

	public function getTable(): Table {
		return $this->__tbl;
	}

	public function getId(): ?string {
		return $this->__id;
	}

	public function save(): void {
		$this->__tbl->onSaveRecord($this);
		$this->saveFields();
		$this->saveLargeFields();
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


	public function getLarge(string $keyPath): ?string {
		if (!isset($this->__large[$keyPath])) {
			$ret = $this->loadLargeField($keyPath);
			if (!$ret) return null;
		}
		$ent = $this->__large[$keyPath];
		return $ent['value'];
	}

	public function setLarge(string $keyPath, string $value, string $suffix = ''): void {
		$ent = [
			'value'   => $value,
			'suffix'  => $suffix,
			'updated' => true,
		];
		$this->__large[$keyPath] = $ent;
	}


	// -------------------------------------------------------------------------


	private function getDirectory(string $keyPath): ?string {
		$fieldPath = $this->__tbl->getFieldPath($this, $keyPath);
		ensure_directory_existence($fieldPath);
		return $fieldPath;
	}


	// -------------------------------------------------------------------------


	private function loadFields(): void {
		$path = $this->__tbl->getDataPath($this);
		$ret = file_get_contents($path);
		if ($ret === false) throw new \Exception("Cannot read '$path'.");
		$this->__data = json_decode($ret, true);
		$this->__data_updated = false;
	}

	private function saveFields(bool $force = false): void {
		if (!$force && !$this->__data_updated) return;
		$recordPath = $this->__tbl->getRecordPath($this);
		ensure_directory_existence($recordPath);
		$path = $this->__tbl->getDataPath($this);
		$str = json_encode($this->__data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$ret = file_put_contents($path, $str, LOCK_EX);
		if ($ret === false) throw new \Exception("Cannot write '$path'.");
		$this->__data_updated = false;
	}


	// -------------------------------------------------------------------------


	private function loadLargeField(string $keyPath): bool {
		$fieldPath = $this->__tbl->getFieldPath($this, $keyPath);
		$fs = glob("$fieldPath.*");
		if ($fs === false || empty($fs)) return false;
		$ret = file_get_contents($fs[0]);
		if ($ret === false) throw new \Exception("Cannot read '$path'.");
		$this->__large[$keyPath] = $ret;
		return true;
	}

	private function saveLargeFields(): void {
		foreach ($this->__large as $key => &$ent) {
			if ($ent['updated']) {
				$this->saveLargeField($key, $ent);
				unset($ent['updated']);
			}
		}
	}

	private function saveLargeField(string $keyPath, array $ent): void {
		$path = $this->__tbl->getFieldPath($this, $keyPath, $ent['suffix']);
		$ret = file_put_contents($path, $ent['value'], LOCK_EX);
		if ($ret === false) throw new \Exception("Cannot write '$path'.");
	}

}
