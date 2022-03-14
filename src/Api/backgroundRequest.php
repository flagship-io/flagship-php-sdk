<?php

$urlArg = getopt(null, ["url:"]);

if (!isset($urlArg["url"])) {
    error_log("url required");
    exit(1);
}

$url = $urlArg["url"];

$method = "POST";
$methodArg = getopt(null, ["method:"]);
if (isset($methodArg["method"])) {
    $method = $methodArg["method"];
}

$timeout = 2;
$timeoutArg = getopt(null, ["timeout:"]);
if (isset($timeoutArg["timeout"])) {
    $timeout = $timeoutArg['timeout'];
}

$bodyArg = getopt(null, ["body:"]);
$bodyData = [];
if (isset($bodyArg["body"])) {
    $bodyData = $bodyArg["body"];
}

$headerArg = getopt(null, ["header:"]);
$headerData = [];
if (isset($headerArg["header"])) {
    $headerData = json_decode($headerArg["header"], true);
}

try {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FILETIME, true);

    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $bodyData);

    $headers = [];
    foreach ($headerData as $key => $value) {
        $headers[] = $key . ': ' . $value;
    }

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $rawResponse = curl_exec($curl);
    $curlErrorCode = curl_errno($curl);
    $curlErrorMessage = curl_error($curl);

    $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $httpError = in_array(floor($httpStatusCode / 100), [4, 5]);
    curl_close($curl);

    if ($httpError || $curlErrorCode) {
        $message = [
            'curlCode' => $curlErrorCode,
            'curlMessage' => $curlErrorMessage,
            'httpCode' => $httpStatusCode,
            'httpMessage' => $rawResponse
        ];
        throw new Exception(json_encode($message), $curlErrorCode);
    }

    $date  = (new DateTime())->format("Y-m-d h:i:s u");
    error_log("[$date] httpStatusCode: $httpStatusCode");
} catch (Exception $e) {
    error_log($e->getMessage());
}
