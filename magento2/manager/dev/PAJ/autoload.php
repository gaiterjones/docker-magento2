<?php
/**
 *  AUTOLOADER

 *  Copyright (C) 2017
 *
 *
 *  @who	   	PAJ
 *  @info   	paj@gaiterjones.com
 *  @license    blog.gaiterjones.com
 * 	
 *

 */

if (version_compare(PHP_VERSION, '5.4.0', '<')) {
  throw new Exception('Requires PHP version 5.3 or higher.');
}

/**
 * Based on the official PSR-4 autoloader example found here:
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
 
// PAJ Loader
//
spl_autoload_register(function ($class)
{
  // project-specific namespace prefix
  $prefix = 'PAJ\\';

  // base directory for the namespace prefix
  $base_dir = __DIR__;

  // does the class use the namespace prefix?
  $len = strlen($prefix);
  if (strncmp($prefix, $class, $len) !== 0) {
    // no, move to the next registered autoloader
    return;
  }

  // get the relative class name
  $relative_class = substr($class, $len);

  // replace the namespace prefix with the base directory, replace namespace
  // separators with directory separators in the relative class name, append
  // with .php
  $file = $base_dir . '/'. str_replace('\\', '/', $relative_class) . '.php';

  // if the file exists, require it
  if (file_exists($file)) {
	require $file;
  }
});

