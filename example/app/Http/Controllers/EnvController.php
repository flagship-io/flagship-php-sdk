<?php

namespace App\Http\Controllers;

use Flagship\Flagship;
use Flagship\FlagshipConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $data = $this->validate($request, [
            'environment_id' => 'required',
            'api_key' => 'required',
            'timeout' => 'required| numeric',
            'bucketing' => 'nullable',
            'polling_interval' => 'nullable'
        ]);

        $config = new FlagshipConfig($data['environment_id'], $data["api_key"]);
        $config->setTimeOut($data['timeout'] / 1000);

        $request->session()->start();

        $logManager = Log::getLogger();
        $config->setLogManager($logManager);

        Flagship::start($config->getEnvId(), $config->getApiKey(), $config);

        $request->session()->put('flagshipConfig', $config);
        return response()->json($this->getEnvJson($config));
    }

    private function getEnvJson($config)
    {
        return [
            "environment_id" => $config->getEnvId(),
            "api_key" => $config->getApiKey(),
            "timeout" => $config->getTimeOut() * 1000,
        ];
    }
}
