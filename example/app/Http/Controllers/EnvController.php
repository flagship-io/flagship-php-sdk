<?php

namespace App\Http\Controllers;

use Flagship\Flagship;
use Flagship\FlagshipConfig;
use Illuminate\Http\Request;

class EnvController extends Controller
{
    public function index()
    {
        return response()->json(Flagship::getConfig());
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
        $request->session()->put('flagshipConfig', $config);

        return response()->json($config);
    }
}
