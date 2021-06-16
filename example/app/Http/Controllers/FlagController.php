<?php

namespace App\Http\Controllers;

use App\Casts\TypeCastInterface;
use App\Rules\CheckBoolean;
use App\Rules\TypeCheck;
use App\Traits\ErrorFormatTrait;
use Exception;
use Flagship\Visitor\Visitor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FlagController extends Controller
{
    use ErrorFormatTrait;

    public function getModification($key, Request $request, Visitor $visitor, TypeCastInterface $typeCast)
    {
        try {
            $data = $this->validate($request, [
                'type' => ['required', 'string', Rule::in(['string',
                    'bool', 'float', 'int', 'bool', 'double', 'long',
                    'JSONObject', 'JSONArray'])],
                'activate' => ['required', new CheckBoolean()],
                'defaultValue' => ['required', new TypeCheck($request->get('type'))]
            ]);

            $defaultValue = $typeCast->castToType($data['defaultValue'], $data['type']);

            $modificationValue = $visitor->getModification($key, $defaultValue, $data['activate']);

            $result = [
                "value" => $modificationValue,
                'error' => null
            ];
            return response()->json($result);
        } catch (ValidationException $exception) {
            return response()->json($this->formatError($exception->errors()), 422);
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }

    public function getModificationInfo($key, Visitor $visitor)
    {
        try {
            $response = $visitor->getModificationInfo($key);
            return response()->json($response);
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }

    public function activeModification($key, Visitor $visitor)
    {
        try {
            $visitor->activateModification($key);
            return response()->json('successful operation');
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }
}
