<?php

namespace App\Http\Controllers;

use App\Casts\TypeCastInterface;
use App\Traits\ErrorFormatTrait;
use App\Rules\TypeCheck;
use Exception;
use Flagship\Flagship;
use Flagship\Visitor\Visitor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VisitorController extends Controller
{
    use ErrorFormatTrait;

    public function index(Request $request)
    {
        $visitor = $request->session()->get('visitor');
        if (!$visitor) {
            return response()->json(null);
        }
        $array = [
            'visitor_id' => $visitor->getVisitorId(),
            'context' => $visitor->getContext(),
            'consent' => $visitor->hasConsented(),
            'modification' => $visitor->getModifications()
        ];
        return response()->json($array);
    }

    public function update(Request $request)
    {
        try {
            $data = $this->validate($request, [
                "visitor_id" => "string|required",
                "consent" => 'nullable|boolean',
                "context" => 'array'
            ]);

            $visitor = Flagship::newVisitor($data['visitor_id'])
                ->withContext($data['context'])
                ->hasConsented(!empty($data['consent']))
                ->build();

            $visitor->setConsent(!empty($data['consent']));

            $visitor->synchronizeModifications();

            $request->session()->put('visitor', $visitor);
            return response()->json($visitor->getModifications());
        } catch (ValidationException $exception) {
            return response()->json($this->formatError($exception->errors()), 422);
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }

    public function updateConsent(Request $request, TypeCastInterface $typeCast, Visitor $visitor)
    {
        try {
            $data = $this->validate($request, [
                "value" => ['required', new TypeCheck('bool')]
            ]);
            $value = $typeCast->castToType($data['value'], 'bool');
            $visitor->setConsent($value);

            return response()->json($visitor);
        } catch (ValidationException $exception) {
            return response()->json($this->formatError($exception->errors()), 422);
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
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

            $visitor->synchronizeModifications();

            return response()->json($visitor);
        } catch (ValidationException $exception) {
            return response()->json($this->formatError($exception->errors()), 422);
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }
}
