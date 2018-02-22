<?php

namespace ORM\Logger;

use ORM\Interfaces\ILogger;

class Logger implements ILogger
{

	const LEVEL_INFO = 1;
	const LEVEL_DEBUG = 2;
	const LEVEL_WARNING = 3;
	const LEVEL_ERROR = 4;

	const LABEL = [
		self::LEVEL_INFO => 'INFO',
		self::LEVEL_DEBUG => 'DEBUG',
		self::LEVEL_WARNING => 'WARNING',
		self::LEVEL_ERROR => 'ERROR',
	];

	const OCCURRENCY_DAILY = 1;
	const OCCURRENCY_MONTHLY = 2;

	const TEMPLATE = "{datetime} - {level} - {class} - {message}\n";

	private $location;

	private $filename;

	private $level;

	private $occurrency;

	private $disabled;

	public function __construct(
		String $location,
		String $filename,
		int $level,
		int $occurrency,
		bool $disabled
	)
	{
		$this->location = $location;
		$this->filename = $filename;
		$this->level = $level;
		$this->occurrency = $occurrency;
		$this->disabled = $disabled;
	}

	public function setLogDisable() {
		$this->disabled = $disabled;
	}

	private function log(String $text, String $class, String $level)
	{
		if ($this->disabled) {
			return;
		}

		if ($level <= $this->level) {
			$log = $this->format($text, $class, $level);

			return $this->saveLog($log);
		}
	}

	public function debug(String $text, String $class)
	{
		return $this->log($text, $class, self::LEVEL_DEBUG);
	}

	public function info(String $text, String $class)
	{
		return $this->log($text, $class, self::LEVEL_INFO);
	}

	public function warning(String $text, String $class)
	{
		return $this->log($text, $class, self::LEVEL_WARNING);
	}

	public function error(String $text, String $class)
	{
		return $this->log($text, $class, self::LEVEL_ERROR);
	}

	private function format(String $text, String $class, String $level)
	{
		$log = self::TEMPLATE;
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
		if (!file_exists($this->location) && is_writable($this->location)) {
			throw new \Exception('No such directory or the directory is not writable for logs: "' . $dir . '"');
		}

		$file = $this->location;
		$file .= $this->filename . '.';

		switch ($this->occurrency) {
			case self::OCCURRENCY_DAILY:
				$file .= date('Y-m-d');
				break;

			case self::OCCURRENCY_MONTHLY:
				$file .= date('Y-m');
				break;

			default:
				throw new \Exception('Invalid occurrency was informed for the logger: "' . $this->occurrency . '"');
				break;
		}

		$file .= '.log';

		return file_put_contents($file, $log, FILE_APPEND|FILE_TEXT);
	}

}
