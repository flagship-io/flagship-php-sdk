<?php

namespace Flagship\Assets {
    class ShellExec
    {
        public static $toThrowException = null;
        public static $command = null;
    }
}


namespace Flagship\Api {

    use Flagship\Assets\ShellExec;

    /**
     * @throws \Exception
     */
    function shell_exec($command) {
        if (ShellExec::$toThrowException) throw new \Exception(ShellExec::$toThrowException);
        ShellExec::$command = $command;
    }
}