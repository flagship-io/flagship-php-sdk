<?php

namespace App\Http\Controllers;

use App\Casts\TypeCastInterface;
use App\Rules\CheckBoolean;
use App\Rules\TypeCheck;
use Flagship\Visitor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FlagController extends Controller
{

    public function getModification($key, Request $request, Visitor $visitor, TypeCastInterface $typeCast)
    {
        try {
            $data = $this->validate($request, [
                'type' => ['required', 'string', Rule::in(['string',
                    'bool', 'float',
                    'int', 'bool', 'double', 'long'])],
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
            return response()->json(['error' => $exception->errors()], 422);
        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }
    }

    public function getModificationInfo($key, Visitor $visitor)
    {
        $response = $visitor->getModificationInfo($key);
        if (!$response) {
            return response()->json(['error' => 'Failed'], 404);
        }
        return response()->json($response);
    }

    public function activeModification($key, Visitor $visitor)
    {
        $visitor->activateModification($key);

        return response()->json('successful operation');
    }
}
