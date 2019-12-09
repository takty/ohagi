<?php
namespace Ohagi;
/**
 *
 * Table
 *
 * @author Takuto Yanagida
 * @version 2019-12-09
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
		$confPath = rtrim($this->path, '\\/') . DIRECTORY_SEPARATOR . Core::CONFIG_NAME;
		if (file_exists($confPath)) {
			$ret = file_get_contents($confPath);
			if ($ret === false) throw new \Exception('Cannot read "config.json".');
			$this->conf = json_decode($ret, true);
		}
	}

	private function saveConf() {
		$confPath = rtrim($this->path, '\\/') . DIRECTORY_SEPARATOR . Core::CONFIG_NAME;
		$str = json_encode($this->conf, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$ret = file_put_contents($confPath, $str, LOCK_EX);
		if ($ret === false) throw new \Exception('Cannot read "config.json".');
	}


	// -------------------------------------------------------------------------


	public function getRecord(string $id): ?Record {
		$recordPath = $this->getRecordPath($id);
		if (!file_exists($recordPath)) return null;
		$r = new Record($this);
		$r->setId($id);
		$this->loadFields($r);
		return $r;
	}

	public function createRecord(): Record {
		$r = new Record($this);
		return $r;
	}

	public function queryRecords( array $args ) {

	}

	public function saveRecord(Record &$r) {
		if ($r->getId() === null) {
			$id = $this->makeId();
			$r->setId($id);
		}
		if ($r->__data_updated) {
			$this->saveFields($r);
		}
		$this->saveLargeFields($r);
		$this->saveConf();
	}

	private function makePath($r, $keyPath, $suffix = '') {
		$recordPath = $this->getRecordPath($r->getId());
		$keys = array_filter(explode('.', $keyPath), function ($it) { return !empty(trim($it)); });
		$sep = $this->getConfig('key_separator');
		$fileName = $sep . implode($sep, $keys);
		$path = $recordPath . DIRECTORY_SEPARATOR . $fileName;
		return ($suffix !== '') ? "$path.$suffix" : $path;
	}

	private function getRecordPath($id) {
		$path = $this->path . DIRECTORY_SEPARATOR . $id;
		return $path;
	}

	private function makeId(): string {
		$conf = &$this->conf;
		if (!isset($conf['lastId'])) {
			$conf['lastId'] = 0;
		} else {
			$conf['lastId'] += 1;
		}
		return $conf['lastId'];
	}


	// -------------------------------------------------------------------------


	public function truncate() {
		$conf = &$this->conf;
		$conf['lastId'] = null;
		$fs = array_diff(scandir($this->path), ['.', '..']);
		foreach ($fs as $f) {
			$p = $this->path . DIRECTORY_SEPARATOR . $f;
			if (is_dir($p)) {
				if (strpos($f, '__') !== 0) $this->removeDirectory($p);
			}
		}
		return $this;
	}

	private function removeDirectory($dir) {
		$fs = array_diff(scandir($dir), ['.', '..']);
		foreach ($fs as $f) {
			if (is_dir($dir . DIRECTORY_SEPARATOR . $f)) {
				remove_directory($dir . DIRECTORY_SEPARATOR . $f);
			} else {
				unlink($dir . DIRECTORY_SEPARATOR . $f);
			}
		}
		return rmdir($dir);
	}


	// -------------------------------------------------------------------------


	private function loadFields(&$r) {
		$recordPath = $this->getRecordPath($r->getId());
		$path = $recordPath . DIRECTORY_SEPARATOR . '@.json';
		$ret = file_get_contents($path);
		if ($ret === false) throw new \Exception("Cannot load '$path'.");
		$r->__data = json_decode($ret, true);
		$r->__data_updated = false;
	}

	private function saveFields($r) {
		$recordPath = $this->getRecordPath($r->getId());
		if (!file_exists($recordPath)) {
			$ret = mkdir($recordPath, 0644, true);
			if ($ret === false) throw new \Exception('Cannot make a record directory.');
		}
		$path = $recordPath . DIRECTORY_SEPARATOR . '@.json';
		$str = json_encode($r->__data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$ret = file_put_contents($path, $str, LOCK_EX);
		if ($ret === false) throw new \Exception("Cannot save '$path'.");
		$r->__data_updated = false;
	}


	// -------------------------------------------------------------------------


	public function loadLargeField($r, $keyPath) {
		$path = $this->makePath($r, $keyPath);
		$fs = glob("$path.*");
		if ($fs === false || empty($fs)) return null;
		$ret = file_get_contents($fs[0]);
		if ($ret === false) throw new \Exception("Cannot read '$path'.");
		$r->__large[$keyPath] = $ret;
	}

	public function saveLargeFields(Record $r) {
		foreach ($r->__large as $key => &$ent) {
			if ($ent['updated']) {
				$this->writeLargeField($r, $key, $ent);
				unset($ent['updated']);
			}
		}
	}

	private function writeLargeField(Record $r, $keyPath, $ent) {
		$path = $this->makePath($r, $keyPath, $ent['suffix']);
		$ret = file_put_contents($path, $ent['value'], LOCK_EX);
		if ($ret === false) throw new \Exception("Cannot write '$path'.");
	}


	// -------------------------------------------------------------------------


	public function convertKeyPathToFilePath(Record $r, string $keyPath) {
		// TODO
		return $filePath;
	}

	public function createDirectory(Record $r, string $keyPath) {
		// TODO
		return $filePath;
	}

}
