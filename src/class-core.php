<?php
namespace Ohagi;
/**
 *
 * Core
 *
 * @author Takuto Yanagida
 * @version 2019-12-15
 *
 */


require_once(__DIR__ . '/filesystem.php');
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
		$confPath = join_path($this->path, self::CONFIG_NAME);
		if (!file_exists($confPath)) {
			$ret = file_put_json($confPath, $this->makeDefaultConf());
			if ($ret === false) throw new \Exception('Cannot make "config.json".');
		}
		$ret = file_get_json($confPath);
		if ($ret === null) throw new \Exception('Cannot read "config.json".');
		$this->conf = $ret;
	}

	private function makeDefaultConf() {
		return [
			'key_separator' => '@',
			'key_root'      => '@'
		];
	}

	public function getConfig($key = null) {
		if ($key === null) return $this->conf;
		if (isset($this->conf[$key])) return $this->conf[$key];
		return null;
	}

	public function getTable(string $tableName) {
		$tablePath = join_path($this->path, $tableName);
		return new Table($this, $tablePath);
	}

}
