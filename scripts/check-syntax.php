<?php

$root = dirname(__DIR__);
$files = [$root.'/artisan', $root.'/public/index.php'];
foreach (['app', 'bootstrap', 'config', 'database', 'lang', 'routes', 'tests'] as $directory) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$directory, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->getExtension() === 'php' && ! str_contains($file->getPathname(), 'bootstrap'.DIRECTORY_SEPARATOR.'cache')) {
            $files[] = $file->getPathname();
        }
    }
}
foreach ($files as $file) {
    exec(escapeshellarg(PHP_BINARY).' -l '.escapeshellarg($file).' 2>&1', $output, $exitCode);
    if ($exitCode !== 0) {
        echo implode(PHP_EOL, $output).PHP_EOL;
        exit($exitCode);
    }
    $output = [];
}
echo count($files)." PHP files: syntax OK.\n";
