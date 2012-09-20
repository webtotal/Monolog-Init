<?php

namespace MonologInit;

class MonologInit
{
	public $handler = null;
	public $target = null;
	protected $handlerInstance = null;


	public function __construct($handler = false, $target = false) {
		if ($handler === false || $target === false) {
			return null;
		}

		$logHandlerClassName = '\Monolog\Handler\\' . $handler . 'Handler';

		if (class_exists('\Monolog\Logger') && class_exists($logHandlerClassName))
		{
			$this->handlerInstance = new \Monolog\Logger('main');
			$this->handlerInstance->pushHandler(new $logHandlerClassName($target));

			$this->handler = $handler;
			$this->target = $target;
		}
	}

	public function getInstance() {
		return $this->handlerInstance;
	}
}