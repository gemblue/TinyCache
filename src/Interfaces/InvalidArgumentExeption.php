<?php

/**
 * Exception interface for invalid cache arguments.
 *
 * When an invalid argument is passed, it must throw an exception which implements
 * this interface.
 */

namespace Gemblue\TinyCache\Interfaces;

interface InvalidArgumentException extends CacheException
{

}