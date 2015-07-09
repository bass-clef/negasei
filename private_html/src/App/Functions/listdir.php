<?php
function listdir($dir='.', $extension=null) {
    if (!is_dir($dir)) {
        return false;
    }
   
    $files = array();
    listdiraux($dir, $files, $extension);

    return $files;
}

function listdiraux($dir, &$files, $extension=null) {
    $handle = opendir($dir);
    while (($file = readdir($handle)) !== false) {
        if ($file == '.' or $file == '..') {
            continue;
        }
        if (!is_null($extension)) {
            if (!in_array(pathinfo($file, PATHINFO_EXTENSION), $extension, true)) {
                continue;
            }
        }
        $filepath = $dir == '.' ? $file : $dir . '/' . $file;
        if (is_link($filepath))
            continue;
        if (is_file($filepath))
            $files[] = $filepath;
        else if (is_dir($filepath))
            listdiraux($filepath, $files);
    }
    closedir($handle);
}
