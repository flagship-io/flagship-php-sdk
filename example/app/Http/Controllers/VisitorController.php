<?php

namespace App\Http\Controllers;

use App\Casts\TypeCastInterface;
use App\Rules\TypeCheck;
use Exception;
use Flagship\Flagship;
use Flagship\Visitor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VisitorController extends Controller
{
    public function index(Request $request)
    {
        $visitor = $request->session()->get('visitor');
        if (!$visitor) {
            return response()->json(null);
        }
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
                "context" => 'array'
            ]);
            $visitor = Flagship::newVisitor($data['visitor_id'], $data['context']);

            $visitor->synchronizedModifications();

            $request->session()->put('visitor', $visitor);
            return response()->json($visitor);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->errors()], 422);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
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

            $visitor->synchronizedModifications();

            return response()->json($visitor);
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->errors()], 422);
        }
    }
}
