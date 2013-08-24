<?php
function loader($className)
{
    $paths = array(
        'tests' => '..'.DIRECTORY_SEPARATOR.'Tests',
        'volux' => '..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'volux',
    );
    $find = function($path) use ($paths) {
        if (isset($paths[$path])) {
            return $paths[$path];
        }
        return '.';
    };
    $path = explode('\\', str_replace('_', '\\', $className));
    $file = $find(array_shift($path)).DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $path).'.php';
    var_dump($file);
    if (is_file($file)) {
        require $file;
    }
}

spl_autoload_register('loader');