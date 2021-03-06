<?php

/**
 * Simple Driver for Memcached.
 *
 * This class just wrap the Memcached PHP Ext and follow PSR 16 Interface.
 * We don't extend Memcached in this class. Just inject the class in construct.
 * Maybe in the future we change `php-memcached` to other Ext.
 * 
 * @package Drivers
 * @author Gemblue
 */

namespace Gemblue\TinyCache\Drivers;

use Gemblue\TinyCache\Interfaces\CacheInterface;

class Memcached implements CacheInterface
{
    /** Ext container */
    protected $memcached;
    
    /**
     * Constructor 
     * 
     * Handle connection and inject ext.
     * 
     * @return void
     */
    public function __construct(array $options)
    {
        // Inject dependency.
        $this->memcached = new \Memcached;
        
        if (!$this->memcached->addServer($options['host'], $options['port'], $options['persistence']))
        {
            return new \Exception('Failed to connect, maybe Memcached server is not running, or wrong config for host and port.');
        }
    }
    
    /**
     * Get key with default if not exist.
     * 
     * @return mixed
     */
    public function get(string $key, $default = null) 
    {
        $get = $this->memcached->get($key);
        
        if ($get == false)
            return $default;

        return unserialize($get);
    }

    /**
     * Set key, value, expire.
     * 
     * @return bool
     */
    public function set(string $key, $value, int $ttl = null) 
    {
        return $this->memcached->set($key, serialize($value), $ttl);
    }
    
    /**
     * Delete key
     * 
     * @return bool
     */
    public function delete(string $key) 
    {
        if(strpos($key, '*'))
        {
            $keys = $this->memcached->getAllKeys();
            $prefix = str_replace('*', '', $key);
            foreach ($keys as $index => $name) {
                if (strpos($name,$prefix) !== 0) {
                    unset($keys[$index]);
                } else {
                    $this->memcached->delete($name);
                }
            }
        }

        return $this->memcached->delete($key) ?? false;
    }

    /**
     * To Wipe Cache.
     * 
     * @return bool
     */
    public function clear() 
    {
        return $this->memcached->flush();
    }

    /**
     * Get multiple Keys.
     * 
     * @return iterable
     */
    public function getMultiple(array $keys, $default = null) 
    {
        $temp = [];

        foreach ($keys as $key) {
            $temp[] = [$key => $this->memcached->get($key)];
        }
        
        return $temp ?? $default;
    }

    /**
     * Set multiple key value, also with ttl.
     * 
     * @return bool
     */
    public function setMultiple(iterable $values, int $ttl = null) 
    {
        foreach ($values as $key => $value) {
            if (!$this->memcached->set($key, $value, $ttl))
                return false;
        }

        return true;
    }

    /**
     * Delete multiple key.
     * 
     * @return bool
     */
    public function deleteMultiple(array $keys) 
    {
        foreach ($keys as $key) {
            if (!$this->memcached->delete($key))
                return false;
        }

        return true;
    }

    /**
     * Has
     * 
     * To check value is exist or no.
     * 
     * @return bool
     */
    public function has(string $key) 
    {
        if ($this->memcached->get($key))
            return true;

        return false;
    }
}