<?php

namespace phpMultiLog;

/**
 * Shutdown process handler, allows you to unregister a process (not supported natively in PHP)
 * Usage:
 * $sd = new System_ShutdownProcess($callable);
 * $sd->register() /// $sd->unregister()
 * or via factory
 * $sd = System_ShutdownProcess::factory($callable)->register()
 */
class phpMultiLogShutdownProcess 
{

	/**
	 * Callback to be executed by the shutdown function
	 * @var callble $callback
	 */
    private $callback;

	/**
	 * Create a shutdown process
	 * @param callable $callback
	 */
    public function __construct($callback)
    {
    	if (!is_callable($callback))
    	{
			throw new \Exception('Callback must be of a callable type');
    	} 
    	$this->callback = $callback;
    }

	/**
	 * Executed by the register_shutdown_function
	 */
    public function call()
    {
        if ($this->callback)
        {
	        $callback = $this->callback;
	        $callback();
        }
    }

    /**
     * Unregister the callback
     */
    public function unregister()
    {
        $this->callback = null;
    }
    
    /**
     * Register the callback
     * @return System_ShutdownProcess $this instance
     */
    public function register()
    {
    	register_shutdown_function(array($this, 'call'));
    	return $this;
    }
    
    /**
     * Factory method for shutdown process
     * @param callable $callback
     * @return System_ShutdownProcess $sd_process
     */
    public static function factory($callback)
    {
    	$obj = new self($callback);
    	return $obj;
    }
}
