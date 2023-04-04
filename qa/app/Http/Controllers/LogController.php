<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;

class LogController extends Controller
{
    const LOGS_IS_EMPTY = "Logs is empty";

    public function index()
    {
        if (!file_exists($this->getSessionFile())) {
            return self::LOGS_IS_EMPTY;
        }
        return file_get_contents($this->getSessionFile());
    }

    public function clear()
    {
        if (!file_exists($this->getSessionFile())) {
            return self::LOGS_IS_EMPTY;
        }
        unlink($this->getSessionFile());
        return self::LOGS_IS_EMPTY;
    }

    private function getSessionFile()
    {
        return storage_path('logs/lumen_' . Session::getId() . '.log');
    }
}
