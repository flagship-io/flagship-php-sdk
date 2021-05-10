<?php

namespace App\Http\Controllers;

use Flagship\Flagship;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $visitor =  $request->session()->get('visitor');
        return response()->json($visitor);
    }
    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $data = $this->validate($request, [
            "visitor_id" => "string|required",
            "context" => 'array|required'
        ]);
        $visitor = Flagship::newVisitor($data['visitor_id'], $data['context']);
        $request->session()->put('visitor', $visitor);
        return response()->json($visitor);
    }
}
