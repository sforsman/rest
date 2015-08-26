<?php

namespace sforsman\Rest;

class DirectoryServiceLoader implements ServiceLoaderInterface
{
  protected $path;
  protected $ns;

  public function __construct($path, $ns = '\\API')
  {
    if(!is_readable($path) or !is_dir($path)) {
      throw new \Exception('Path ' . $path . ' is not a readable directory');
    }
    $this->path = $path;
    $this->ns = $ns;
  }

  public function getServices()
  {
    if(!$this->path) {
      throw new \Exception('Services path is undefined'); 
    }

    $services = [];

    $directory = new \RecursiveDirectoryIterator($this->path);
    $iterator = new \RecursiveIteratorIterator($directory);
    $regex = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);

    foreach($regex as $file) {
      $path = $file[0];
      list($version, $filename) = array_slice(explode(DIRECTORY_SEPARATOR, $path), -2);

      $class = substr($filename,0,-4);
      $service = strtolower(substr($filename,0,-11));

      if(substr($filename,-11) !== 'Service.php' or strlen($filename) <= 11) {
        continue;
      } 

      require_once $path;
      
      $classNs = "{$this->ns}\\{$version}\\{$class}";

      if(!class_exists($classNs)) {
        continue;
      }

      $services[$version][$service] = $classNs;
    }

    return $services;
  }
}