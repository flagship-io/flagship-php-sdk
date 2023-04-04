<?php

namespace App\Http\Controllers;

use Flagship\Visitor\VisitorInterface;

class HomeController extends Controller
{
    // Inject VisitorInterface
    public function index(VisitorInterface $visitor)
    {
        //Get the flag my-flag
        $myFlagValue = $visitor->getFlag("my-flag", "defaultValue")->getValue();

        return view('welcome', ["myFlagValue"=>$myFlagValue, "visitorId"=>$visitor->getVisitorId()]);
    }
}
