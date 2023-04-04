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

            $flag = $visitor->getFlag($key, $defaultValue);

            $result = [
                "value" => $flag->getValue($data['activate']),
                'error' => null
            ];
            return response()->json($result);
        } catch (ValidationException $exception) {
            return response()->json($this->formatError($exception->errors()), 422);
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }

    public function getModificationInfo($key, Visitor $visitor, Request $request, TypeCastInterface $typeCast)
    {
        $defaultValueQuery = $request->get("defaultValue");
        $type = $request->get("type");
        try {
            $defaultValue = $typeCast->castToType($defaultValueQuery, $type);
            $flag = $visitor->getFlag($key, $defaultValue);
            $response = $flag->getMetadata();
            return response()->json($response);
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }

    public function activeModification($key, Visitor $visitor, Request $request, TypeCastInterface $typeCast)
    {
        try {
            $defaultValueQuery = $request->get("defaultValue");
            $type = $request->get("type");
            $defaultValue = $typeCast->castToType($defaultValueQuery, $type);
            $flag = $visitor->getFlag($key, $defaultValue);
            $flag->userExposed();
            return response()->json('successful operation');
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }
}
