<?php

namespace Flagship\Hit;

use Flagship\Enum\HitType;
use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;

class Segment extends HitAbstract
{
    public const SL_MESSAGE_ERROR = "Sl value must be an associative array";
    public const ERROR_MESSAGE  = 'sl is required';

    /**
     * @var array<string, mixed>
     */
    protected array $sl = [];

    /**
     * @return array<string, mixed>
     */
    public function getSl(): array
    {
        return $this->sl;
    }

    /**
     * @param array<string, scalar> $sl
     * @return Segment
     */
    public function setSl(array $sl): self
    {
        if (!$this->isAssoc($sl)) {
            $this->logError($this->getConfig(), self::SL_MESSAGE_ERROR, [FlagshipConstant::TAG => __FUNCTION__]);
            return $this;
        }
        $this->sl = $sl;
        return $this;
    }

    /**
     * @param array<string, scalar> $sl
     */
    public function __construct(array $sl, FlagshipConfig $config)
    {
        parent::__construct(HitType::SEGMENT);
        $this->setConfig($config);
        $this->setSl($sl);
    }

    /**
     * @param array<mixed> $array
     * @return bool
     */
    protected function isAssoc(array $array): bool
    {
        return !array_is_list($array);
    }

    /**
     * @inheritDoc
     */
    public function toApiKeys(): array
    {
        $arrayParent = parent::toApiKeys();
        $apiContext = array_map(function ($value) {
            if ($value === null) {
                return 'null';
            }
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }
            if (is_scalar($value)) {
                return strval($value);
            }
            return '';
        }, $this->getSl());

        $arrayParent[FlagshipConstant::SL_API_ITEM] = $apiContext;
        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady(): bool
    {
        return parent::isReady() && !empty($this->getSl());
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }
}
