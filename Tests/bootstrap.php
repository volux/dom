<?php
function loader($className)
{
    $dir = '/home/travis/build/volux/dom/';
    $paths = array(
        'tests' => $dir.'Tests',
        'volux' => $dir.'src/volux',
    );
    $find = function($path) use ($paths) {
        if (isset($paths[$path])) {
            return $paths[$path];
        }
        return '.';
    };
    $path = explode('\\', str_replace('_', '\\', $className));
    $file = $find(array_shift($path)).'/'.implode('/', $path).'.php';
    var_dump($file);
    if (is_file($file)) {
        require $file;
    }
}

spl_autoload_register('loader');