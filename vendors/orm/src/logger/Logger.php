<?php

namespace ORM\Logger;

use ORM\Interfaces\ILogger;

class Logger implements ILogger
{

	const INFO = 1;
	const DEBUG = 2;
	const WARNING = 3;
	const ERROR = 4;
	const LABEL = [
		self::INFO => 'INFO',
		self::DEBUG => 'DEBUG',
		self::WARNING => 'WARNING',
		self::ERROR => 'ERROR',
	];

	private static $template = "{datetime} - {level} - {class} - {message}\n";

	private $file;

	private $level;

	public function __construct(String $file, ?int $level = self::INFO) {
		$this->file = $file;
		$this->level = $level;
	}

	private function log(String $text, String $class, String $level)
	{
		if ($level <= $this->level) {
			$log = $this->format($text, $class, $level);

			return $this->saveLog($log);
		}
	}

	public function debug(String $text, String $class)
	{
		return $this->log($text, $class, self::DEBUG);
	}

	public function info(String $text, String $class)
	{
		return $this->log($text, $class, self::INFO);
	}

	public function warning(String $text, String $class)
	{
		return $this->log($text, $class, self::WARNING);
	}

	public function error(String $text, String $class)
	{
		return $this->log($text, $class, self::ERROR);
	}

	private function format(String $text, String $class, String $level)
	{
		$log = self::$template;
		$now = new \DateTime();

		$log = str_replace('{date}', $now->format('Y-m-d'), $log);
		$log = str_replace('{time}', $now->format('H:i:s'), $log);
		$log = str_replace('{datetime}', $now->format('Y-m-d H:i:s'), $log);
		$log = str_replace('{level}', '[' . self::LABEL[$level] . ']', $log);
		$log = str_replace('{class}', $class, $log);
		$log = str_replace('{message}', $text, $log);

		return $log;
	}

	private function saveLog($log)
	{
		$dir = dirname($this->file);

		if (!file_exists($dir) && is_writable($dir)) {
			throw new \Exception('No such directory or the directory is not writable for logs: "' . $dir . '"');
		}

		return file_put_contents($this->file, $log, FILE_APPEND|FILE_TEXT);
	}

}
