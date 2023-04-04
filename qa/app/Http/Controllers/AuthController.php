<?php

namespace App\Http\Controllers;

use App\Traits\ErrorFormatTrait;
use Exception;
use Flagship\Visitor\Visitor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ErrorFormatTrait;

    public function authenticate(Request $request, Visitor $visitor)
    {
        try {
            $data = $this->validate($request, [
                "new_visitor_id" => 'required|string'
            ]);
            $visitor->authenticate($data["new_visitor_id"]);
            return response()->json([
                "visitorId" => $visitor->getVisitorId(),
                "anonymousId" => $visitor->getAnonymousId()]);
        } catch (ValidationException $exception) {
            return response()->json($this->formatError($exception->errors()), 422);
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }

    public function unauthenticate(Visitor $visitor)
    {
        try {
            $visitor->unauthenticate();
            return response()->json([
                "visitorId" => $visitor->getVisitorId(),
                "anonymousId" => $visitor->getAnonymousId()
            ]);
        } catch (Exception $exception) {
            return response()->json($this->formatError($exception->getMessage()), 500);
        }
    }
}
