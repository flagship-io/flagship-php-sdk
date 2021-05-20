<?php

namespace App\Http\Controllers;

use Exception;
use Flagship\Flagship;
use Flagship\FlagshipConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EnvController extends Controller
{
    public function index(Request $request)
    {
        $config = $request->session()->get('flagshipConfig');
        if (!$config) {
            return response()->json(null);
        }

        return response()->json($this->getEnvJson($config));
    }


    public function update(Request $request)
    {
        try {
            $data = $this->validate($request, [
                'environment_id' => 'required',
                'api_key' => 'required',
                'timeout' => 'required|numeric',
                'bucketing' => 'nullable',
                'polling_interval' => 'nullable'
            ]);

            $config = new FlagshipConfig($data['environment_id'], $data["api_key"]);
            $config->setTimeout($data['timeout'] / 1000);

            $request->session()->start();

            $logManager = Log::getLogger();
            $config->setLogManager($logManager);

            Flagship::start($config->getEnvId(), $config->getApiKey(), $config);

            $request->session()->put('flagshipConfig', $config);
            return response()->json($this->getEnvJson($config));
        } catch (ValidationException $exception) {
            return response()->json(['error' => $exception->errors()], 422);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    private function getEnvJson($config)
    {
        return [
            'data' => [
                "environment_id" => $config->getEnvId(),
                "api_key" => $config->getApiKey(),
                "timeout" => $config->getTimeOut() * 1000
            ]
        ];
    }
}
