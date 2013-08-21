<?php
function loader($className)
{
    $paths = array(
        'Tests' => '.',
        'volux' => '../src',
    );
    $path = explode('\\', str_replace('_', '\\', $className));
    $file = $paths[array_shift($path)].implode(DIRECTORY_SEPARATOR, $path).'.php';
    require $file;
}

spl_autoload_register('loader');