<?php

namespace Flagship\Assets {

    class File
    {
        public static $fileExist = false;
        public static $fileContent = null;
    }
}

namespace Flagship\Decision{
    use Flagship\Assets\File;

    function file_exists($filename)
    {
        return File::$fileExist;
    }

    function file_get_contents ($filename){
        return File::$fileContent;
    }

}
