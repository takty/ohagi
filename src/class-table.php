<?php
namespace Ohagi;
/**
 *
 * Table
 *
 * @author Takuto Yanagida
 * @version 2019-12-15
 *
 */


require_once(__DIR__ . '/class-record.php');


class Table {

	private $core;
	private $path;
	private $conf = [];

	public function __construct(Core $core, string $path) {
		$this->core = $core;
		$this->path = $path;

		if (!file_exists($this->path)) {
			$ret = mkdir($this->path, 0644, true);
			if ($ret === false) throw new \Exception('Cannot make a table directory.');
		}
		$this->loadConf();
	}

	public function getConfig($key) {
		if ($key === null) return $this->conf;
		if (isset($this->conf[$key])) return $this->conf[$key];
		return $this->core->getConfig($key);
	}

	private function loadConf() {
		$confPath = join_path($this->path, Core::CONFIG_NAME);
		if (file_exists($confPath)) {
			$ret = file_get_json($confPath);
			if ($ret === null) throw new \Exception('Cannot read "config.json".');
			$this->conf = $ret;
		}
	}

	private function saveConf() {
		$confPath = join_path($this->path, Core::CONFIG_NAME);
		$ret = file_put_json($confPath, $this->conf);
		if ($ret === false) throw new \Exception('Cannot read "config.json".');
	}


	// -------------------------------------------------------------------------


	private function makeId(): string {
		$conf = &$this->conf;
		if (!isset($conf['lastId'])) {
			$conf['lastId'] = 0;
		} else {
			$conf['lastId'] += 1;
		}
		return $conf['lastId'];
	}

	public function getRecordPath(Record $r) {
		$path = join_path($this->path, $r->getId());
		return $path;
	}

	public function getDataPath(Record $r) {
		$recordPath = $this->getRecordPath($r);
		$root = $this->getConfig('key_root');
		return join_path($recordPath, "$root.json");
	}

	public function getFieldPath(Record $r, $keyPath, $suffix = '') {
		$keys     = array_filter(explode('.', $keyPath), function ($it) { return !empty(trim($it)); });
		$sep      = $this->getConfig('key_separator');
		$root     = $this->getConfig('key_root');
		$fileName = $root . implode($sep, $keys);

		$recordPath = $this->getRecordPath($r);
		$path       = join_path($recordPath, $fileName);
		return ($suffix !== '') ? "$path.$suffix" : $path;
	}

	public function onSaveRecord(Record &$r) {
		if ($r->getId() === null) $r->setId($this->makeId());
		$this->saveConf();
	}


	// -------------------------------------------------------------------------


	public function getRecord(string $id): ?Record {
		$recordPath = join_path($this->path, $id);
		if (!file_exists($recordPath)) return null;
		$r = new Record($this, $id);
		return $r;
	}

	public function createRecord(): Record {
		$r = new Record($this);
		return $r;
	}

	public function queryRecords( array $args ) {

	}


	// -------------------------------------------------------------------------


	public function truncate() {
		$conf = &$this->conf;
		$conf['lastId'] = null;
		$fs = array_diff(scandir($this->path), ['.', '..']);
		foreach ($fs as $f) {
			$p = join_path($this->path, $f);
			if (!is_dir($p) || strpos($f, '__') === 0) continue;
			removeDirectory($p);
		}
		return $this;
	}

}
