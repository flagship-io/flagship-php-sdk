<?php

namespace Flagship\Hit;

use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;

class Segment extends HitAbstract
{
    public const SL_MESSAGE_ERROR = "Sl value must be an associative array";
    public const ERROR_MESSAGE  = 'sl is required';

    public static function getClassName(): string
    {
        return __CLASS__;
    }

    /**
     * @var array<string, mixed>
     */
    protected array $sl;

    /**
     * @return array<string, mixed>
     */
    public function getSl(): array
    {
        return $this->sl;
    }

    /**
     * @param array<string, mixed> $sl
     * @return Segment
     */
    public function setSl(array $sl): static
    {
        if (!$this->isAssoc($sl)) {
            $this->logError($this->getConfig(), self::SL_MESSAGE_ERROR, [FlagshipConstant::TAG => __FUNCTION__]);
            return $this;
        }
        $this->sl = $sl;
        return $this;
    }

    /**
     * @param array $sl
     */
    public function __construct(array $sl)
    {
        parent::__construct(HitType::SEGMENT);
        $this->setSl($sl);
    }

    /**
     * @param array<string, mixed> $array
     * @return bool
     */
    protected function isAssoc(array $array): bool
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }

    /**
     * @inheritDoc
     */
    public function toApiKeys(): array
    {
        $arrayParent = parent::toApiKeys();
        $arrayParent[FlagshipConstant::SL_API_ITEM] = $this->getSl();
        return $arrayParent;
    }

    /**
     * @inheritDoc
     */
    public function isReady(): bool
    {
        return parent::isReady() && $this->getSl();
    }

    /**
     * @inheritDoc
     */
    public function getErrorMessage(): string
    {
        return self::ERROR_MESSAGE;
    }
}
