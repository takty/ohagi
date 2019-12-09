<?php
namespace Ohagi;
/**
 *
 * Core
 *
 * @author Takuto Yanagida
 * @version 2019-12-09
 *
 */


require_once(__DIR__ . '/class-table.php');


class Core {

	const CONFIG_NAME = 'config.json';

	private $path;
	private $conf;

	public function __construct(string $path) {
		$this->path = $path;

		if (!file_exists($this->path)) {
			$ret = mkdir($this->path, 0644, true);
			if ($ret === false) throw new \Exception('Cannot make a db directory.');
		}
		$this->loadConf();
	}

	private function loadConf() {
		$confPath = rtrim($this->path, '\\/') . DIRECTORY_SEPARATOR . self::CONFIG_NAME;
		if (!file_exists($confPath)) {
			$str = json_encode($this->makeDefaultConf(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			$ret = file_put_contents($confPath, $str, LOCK_EX);
			if ($ret === false) throw new \Exception('Cannot make "config.json".');
		}
		$ret = file_get_contents($confPath);
		if ($ret === false) throw new \Exception('Cannot read "config.json".');
		$this->conf = json_decode($ret, true);
	}

	private function makeDefaultConf() {
		return ['key_separator' => '@'];
	}

	public function getConfig($key = null) {
		if ($key === null) return $this->conf;
		if (isset($this->conf[$key])) return $this->conf[$key];
		return null;
	}

	public function getTable(string $tableName) {
		$tablePath = rtrim($this->path, '\\/') . DIRECTORY_SEPARATOR . $tableName;
		return new Table($this, $tablePath);
	}

}
