<?php

namespace Flagship\Assets {

    class File
    {
        public static $fileExist = false;
        public static $fileContent = null;
        public static $fileName = null;
        public static $fwriteData = null;
    }
}

namespace Flagship\Decision{
    use Flagship\Assets\File;

    function file_exists($filename)
    {
        return File::$fileExist;
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

}
