<?php

namespace App\Http\Controllers;

use App\Casts\TypeCastInterface;
use App\Rules\TypeCheck;
use Flagship\Flagship;
use Flagship\Visitor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VisitorController extends Controller
{
    public function index(Visitor $visitor = null)
    {
        $array = [
            'visitor_id' => $visitor->getVisitorId(),
            'context' => $visitor->getContext(),
        ];
        return response()->json($array);
    }

    public function update(Request $request)
    {
        try {
            $data = $this->validate($request, [
                "visitor_id" => "string|required",
                "context" => 'array|required'
            ]);
            $visitor = Flagship::newVisitor($data['visitor_id'], $data['context']);
            $request->session()->put('visitor', $visitor);
            return response()->json($visitor);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->errors()]);
        }
    }

    public function updateContext($key, Request $request, TypeCastInterface $typeCast, Visitor $visitor)
    {
        try {
            $data = $this->validate($request, [
                "type" => "string|required",
                "value" => ['required', new TypeCheck($request->get('type'))]
            ]);
            $value = $typeCast->castToType($data['value'], $data['type']);

            $visitor->updateContext($key, $value);

            return response()->json($visitor);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->errors()]);
        }
    }
}
