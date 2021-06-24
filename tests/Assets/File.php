<?php

namespace Flagship\Assets {

    class File
    {
        public static $fileExist = false;
        public static $isDir = false;
        public static $fileContent = null;
        public static $fileName = null;
        public static $fwriteData = null;
        public static $directory;
        public static $directoryPermission;
    }
}

namespace Flagship\Decision{
    use Flagship\Assets\File;

    function file_exists($filename)
    {
        return File::$fileExist;
    }

    function is_dir($filename)
    {
        return File::$isDir;
    }

    function file_get_contents($filename)
    {
        return File::$fileContent;
    }

    function file_put_contents($filename, $data)
    {
        File::$fileName = $filename;
        File::$fileContent = $data;
    }

    function fwrite($stream, $data)
    {
        File::$fwriteData =  $data;
    }

    function mkdir($directory, $permissions = 0777, $recursive = false)
    {
        File::$directory = $directory;
        File::$directoryPermission = $permissions;
    }

}
