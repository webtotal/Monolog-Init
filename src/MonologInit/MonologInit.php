<?php
/**
 * Monolog Init File
 *
 * Very basic and light Dependency Injector Container for Monolog
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          https://github.com/kamisama/Monolog-Init
 * @package       MonologInit
 * @since         0.1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
namespace MonologInit;

use Monolog\Handler;

class MonologInit
{
    public $handler = null;
    public $target = null;
    protected $instance = null;

    const VERSION = '0.1.1';


    public function __construct($handler = false, $target = false)
    {
        if ($handler === false || $target === false) {
            return null;
        }

        $this->createLoggerInstance($handler, $target);
    }

    /**
     * Return a Monolog Logger instance
     *
     * @return Monolog\Logger instance, ready to use
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Create a Monolog\Logger instance and attach a handler
     *
     * @param  string $handler Name of the handler, without the "Handler" part
     * @param  string $target  Comma separated list of arguments to pass to the handler
     * @return void
     */
    protected function createLoggerInstance($handler, $target)
    {
        $handlerClassName = $handler . 'Handler';

        if (class_exists('\Monolog\Logger') && class_exists('\Monolog\Handler\\' . $handlerClassName)) {
            if (null !== $handlerInstance = $this->createHandlerInstance($handlerClassName, $target)) {
                $this->instance = new \Monolog\Logger('main');
                $this->instance->pushHandler($handlerInstance);
            }

            $this->handler = $handler;
            $this->target = $target;
        }
    }

    /**
     * Create an Monolog Handler instance
     *
     * @param  string $className   Monolog handler classname
     * @param  string $handlerArgs Comma separated list of arguments to pass to the handler
     * @return Monolog\Handler instance if successfull, null otherwise
     */
    protected function createHandlerInstance($className, $handlerArgs)
    {
        $handlerArgsArray = explode(',', $handlerArgs);

        if (method_exists($this, 'init' . $className)) {
            return call_user_func(array($this, 'init' . $className), $handlerArgsArray);
        } else {
            // Fallback to the default handler, but fail gracefully
            try {
                $instance = $this->defaultInit($className, $handlerArgsArray);
            } catch(\Exception $e){
                $instance = null;
            }

            return $instance;
        }
    }

    protected function defaultInit($className, $args)
    {
        $reflect = new \ReflectionClass('\Monolog\Handler\\' . $className);
        return $reflect->newInstanceArgs($args);
    }

    /**
     * Handle public class-specific init method from previous versions
     */
    public function __call($name, $args)
    {
        if (substr($name, 0, 4) == 'init') {
            $className = substr($name, 4);

            // $args is now an array of arguments in an array of arguments - unwrap it
            if(count($args) > 0){
                $args = $args[0];
            }

            return $this->defaultInit($className, $args);
        } else {
            trigger_error('Undefined method ' . $name, E_USER_ERROR);
        }
    }    

    public function initMongoDBHandler($args)
    {
        $reflect  = new \ReflectionClass('\Monolog\Handler\MongoDBHandler');
        $mongo = new \Mongo(array_shift($args));
        array_unshift($args, $mongo);
        return $reflect->newInstanceArgs($args);
    }

    /**
     *
     * @since 0.1.1
     */
    public function initCouchDBHandler($args)
    {
        $reflect  = new \ReflectionClass('\Monolog\Handler\CouchDBHandler');
        if (isset($args[0])) {
            $args[0] = explode(':', $args[0]);
        }
        return $reflect->newInstanceArgs($args);
    }
}

