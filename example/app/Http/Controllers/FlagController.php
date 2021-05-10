<?php

namespace App\Http\Controllers;

use Flagship\Flagship;
use Flagship\Visitor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FlagController extends Controller
{
    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function getModification($key, Request $request, Visitor $visitor)
    {
        $data = $this->validate($request, [
            'type' => ['required','string', Rule::in(['string', 'bool', 'float', 'int', 'bool', 'double', 'long'])],
            'activate' => 'required|boolean',
            'defaultValue' => ['required', function ($input, $value, $fails) use ($request) {
                $type = $request->get('type');

                $check = false;
                switch ($type) {
                    case 'bool':
                        $check = null !== filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        break;
                    case 'double':
                    case 'long':
                    case 'float':
                    case 'int':
                        $check = is_numeric($value);
                        break;
                    default:
                        $check = is_string($value) ;
                }
                if (!$check) {
                    $fails('The ' . $input . ' is not ' . $type);
                }
            }]
        ]);

        $defaultValue = $data['defaultValue'];

        switch ($data['type']) {
            case 'bool':
                $defaultValue = (bool)$defaultValue;
                break;
            case 'double':
            case 'long':
            case 'float':
                $defaultValue = (float)$defaultValue;
                break;
            case 'int':
                $defaultValue = (int)$defaultValue;
                break;
        }

        return response()->json($visitor->getModification($key, $defaultValue, $data['activate']));
    }

    public function getModificationInfo($key, Visitor $visitor)
    {
        return response()->json($visitor->getModificationInfo($key));
    }

    public function activeModification($key, Visitor $visitor)
    {
        $visitor->activateModification($key);
        return response()->json(null);
    }
}
