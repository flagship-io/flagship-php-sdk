<?php

namespace App\Http\Controllers;

use App\Casts\TypeCastInterface;
use App\Rules\TypeCheck;
use Exception;
use Flagship\Config\BucketingConfig;
use Flagship\Config\DecisionApiConfig;
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


    public function update(Request $request, TypeCastInterface $typeCast)
    {
        try {
            $data = $this->validate($request, [
                'environment_id' => 'required',
                'api_key' => 'required',
                'timeout' => 'required|numeric',
                'bucketing' => ['nullable', new TypeCheck('bool')],
                'polling_interval' => 'numeric'
            ]);

            $bucketing = false;
            if (isset($data['bucketing'])) {
                $bucketing = $typeCast->castToType($data['bucketing'], 'bool');
            }

            if ($bucketing) {
                $config = new BucketingConfig($data['environment_id'], $data["api_key"]);
                if (isset($data['polling_interval'])) {
                    $config->setPollingInterval($data['polling_interval']);
                }
            } else {
                $config = new DecisionApiConfig($data['environment_id'], $data["api_key"]);
            }

            $config->setTimeout($data['timeout']);
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
            "environment_id" => $config->getEnvId(),
            "api_key" => $config->getApiKey(),
            "timeout" => $config->getTimeOut(),
            "bucketing" => $config instanceof  BucketingConfig,
            "polling_interval" => $config instanceof  BucketingConfig ? $config->getPollingInterval() : 0,
        ];
    }
}
