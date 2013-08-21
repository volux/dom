<?php
function loader($className)
{
    $paths = array(
        'Tests' => 'Tests/',
        'volux' => 'src/volux/',
    );
    $path = explode('\\', str_replace('_', '\\', $className));
    $file = $paths[array_shift($path)].implode(DIRECTORY_SEPARATOR, $path).'.php';
    var_dump($file);
    require $file;
}

spl_autoload_register('loader');