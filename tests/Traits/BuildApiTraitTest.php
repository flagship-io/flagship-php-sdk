<?php

namespace Flagship\Traits;

use Flagship\Enum\FlagshipConstant;
use Flagship\Utils\Utils;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class BuildApiTraitTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testBuildHeader()
    {

        $buildApiTraitMock = $this->getMockForTrait(
            BuildApiTrait::class,
            [],
            "",
            false,
            true,
            true
        );
        $buildHeader = Utils::getMethod($buildApiTraitMock, "buildHeader");

        $apiKey = "54545d8sfwr";

        $headers = $buildHeader->invokeArgs($buildApiTraitMock, [$apiKey]);

        $headerArray = [
                        FlagshipConstant::HEADER_X_API_KEY     => $apiKey,
                        FlagshipConstant::HEADER_X_SDK_VERSION => FlagshipConstant::SDK_VERSION,
                        FlagshipConstant::HEADER_CONTENT_TYPE  => FlagshipConstant::HEADER_APPLICATION_JSON,
                        FlagshipConstant::HEADER_X_SDK_CLIENT  => FlagshipConstant::SDK_LANGUAGE,
                       ];

        $this->assertSame($headerArray, $headers);

        $buildDecisionApiUrl = Utils::getMethod($buildApiTraitMock, "buildDecisionApiUrl");

        $url = "campaign";
        $apiUrl = $buildDecisionApiUrl->invokeArgs($buildApiTraitMock, [$url]);

        $this->assertSame(FlagshipConstant::BASE_API_URL . '/' . $url, $apiUrl);
    }
}
