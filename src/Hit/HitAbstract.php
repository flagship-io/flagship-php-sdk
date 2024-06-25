<?php

namespace Flagship\Hit;

use Flagship\Config\FlagshipConfig;
use Flagship\Enum\FlagshipConstant;
use Flagship\Enum\HitType;
use Flagship\Traits\BuildApiTrait;
use Flagship\Traits\Helper;
use Flagship\Traits\LogTrait;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;

/**
 * Class HitAbstract
 *
 * @abstract
 * @package  Flagship\Hit
 */
abstract class HitAbstract
{
    use LogTrait;
    use BuildApiTrait;
    use Helper;

    /**
     * @var string
     */
    protected string $visitorId;

    /**
     * @var string
     */
    protected string $ds;

    /**
     * @var HitType
     */
    protected HitType $type;

    /**
     * @var ?FlagshipConfig
     */
    protected ?FlagshipConfig $config = null;

    /**
     * @var string|null
     */
    protected ?string $anonymousId = null;

    /**
     * The User IP address
     * @var string|null
     */
    protected ?string $userIP = null;

    /**
     * @var string|null
     */
    protected ?string $screenResolution = null;

    /**
     * @var string|null
     */
    protected ?string $locale = null;

    /**
     * @var numeric|string|null
     */
    protected string|int|float|null $sessionNumber = null;

    /**
     * @var string
     */
    protected string $key;

    /**
     * @var int
     */
    protected int $createdAt;

    /**
     * @var bool
     */
    protected bool $isFromCache;

    /**
     * HitAbstract constructor.
     *
     * @param HitType $type  Hit type
     */
    public function __construct(HitType $type)
    {
        $this->setType($type);
        $this->ds = FlagshipConstant::SDK_APP;
        $this->createdAt =  $this->getNow();
        $this->anonymousId = null;
        $this->isFromCache = false;
        $this->userIP = null;
        $this->screenResolution = null;
        $this->locale = null;
        $this->sessionNumber = null;
    }

    /**
     * @return string
     */
    public function getVisitorId(): string
    {
        return $this->visitorId;
    }

    /**
     * Specifies visitor unique identifier provided by developer at visitor creation
     *
     * @param string $visitorId
     * @return HitAbstract
     */
    public function setVisitorId(string $visitorId): static
    {
        $this->visitorId = $visitorId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDs(): string
    {
        return $this->ds;
    }

    /**
     * @param string $ds
     * @return HitAbstract
     */
    public function setDs(string $ds): static
    {
        $this->ds = $ds;
        return $this;
    }

    /**
     * Hit Type
     *
     * @return HitType
     */
    public function getType(): HitType
    {
        return $this->type;
    }

    protected function setType(HitType $type): static
    {
         $this->type = $type;
         return $this;
    }

    /**
     * @return ?FlagshipConfig
     */
    public function getConfig(): ?FlagshipConfig
    {
        return $this->config;
    }

    /**
     * @param FlagshipConfig $config
     * @return HitAbstract
     */
    public function setConfig(FlagshipConfig $config): static
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAnonymousId(): ?string
    {
        return $this->anonymousId;
    }

    /**
     * @param string|null $anonymousId
     * @return HitAbstract
     */
    public function setAnonymousId(?string $anonymousId): static
    {
        $this->anonymousId = $anonymousId;
        return $this;
    }

    /**
     * The User IP address
     * @return string|null
     */
    public function getUserIP(): ?string
    {
        return $this->userIP;
    }

    /**
     * Define the User IP address
     * @param string|null $userIP
     * @return HitAbstract
     */
    public function setUserIP(?string $userIP): static
    {
        $this->userIP = $userIP;
        return $this;
    }

    /**
     * Screen Resolution.
     * @return string|null
     */
    public function getScreenResolution(): ?string
    {
        return $this->screenResolution;
    }

    /**
     * Screen Resolution
     * @param string|null $screenResolution
     * @return HitAbstract
     */
    public function setScreenResolution(?string $screenResolution): static
    {
        $this->screenResolution = $screenResolution;
        return $this;
    }

    /**
     * User language
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Define User language
     * @param string|null $locale
     * @return HitAbstract
     */
    public function setLocale(?string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Session number. Number of sessions the current visitor has logged, including the current session
     * @return float|int|string|null
     */
    public function getSessionNumber(): float|int|string|null
    {
        return $this->sessionNumber;
    }

    /**
     * Define Session number. Number of sessions the current visitor has logged, including the current session
     * @param float|int|string|null $sessionNumber
     * @return HitAbstract
     */
    public function setSessionNumber(float|int|string|null $sessionNumber): static
    {
        $this->sessionNumber = $sessionNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return HitAbstract
     */
    public function setKey(string $key): static
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedAt(): int
    {
        return $this->createdAt;
    }

    /**
     * @param int $createdAt
     * @return HitAbstract
     */
    public function setCreatedAt(int $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFromCache(): bool
    {
        return $this->isFromCache;
    }

    /**
     * @param bool $isFromCache
     * @return HitAbstract
     */
    protected function setIsFromCache(bool $isFromCache): static
    {
        $this->isFromCache = $isFromCache;
        return $this;
    }

    /**
     * Return an associative array of the class with Api parameters as keys
     *
     * @return array
     */
    public function toApiKeys(): array
    {
        $data = [
            FlagshipConstant::VISITOR_ID_API_ITEM => $this->visitorId ?: $this->anonymousId,
            FlagshipConstant::DS_API_ITEM => $this->getDs(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->getConfig()->getEnvId(),
            FlagshipConstant::T_API_ITEM => $this->getType()->value,
            FlagshipConstant::CUSTOMER_UID => null,
            FlagshipConstant::QT_API_ITEM => $this->getNow() - $this->createdAt,
        ];

        if ($this->getUserIP() !== null) {
            $data[FlagshipConstant::USER_IP_API_ITEM] = $this->getUserIP();
        }

        if ($this->getScreenResolution() !== null) {
            $data[FlagshipConstant::SCREEN_RESOLUTION_API_ITEM] = $this->getScreenResolution();
        }

        if ($this->getLocale() !== null) {
            $data[FlagshipConstant::USER_LANGUAGE] = $this->getLocale();
        }

        if ($this->getSessionNumber() !== null) {
            $data[FlagshipConstant::SESSION_NUMBER] = $this->getSessionNumber();
        }

        if ($this->visitorId && $this->anonymousId) {
            $data[FlagshipConstant::VISITOR_ID_API_ITEM] = $this->anonymousId;
            $data[FlagshipConstant::CUSTOMER_UID] = $this->visitorId;
        }
        return $data;
    }

    /**
     * @param $class
     * @param $data
     * @return object
     * @throws ReflectionException
     */
    public static function hydrate($class, $data): object
    {
        $reflector = new ReflectionClass($class);
        $objet = $reflector->newInstanceWithoutConstructor();
        foreach ($data as $key => $value) {
            $method = 'set' . ucwords($key);
            if (is_callable(array($objet, $method))) {
                if ($key === "type") {
                    $value = HitType::from($value);
                }
                $objet->$method($value);
            }
        }
        $objet->setIsFromCache(true);
        return $objet;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $reflector = new ReflectionObject($this);
        $properties = $reflector->getProperties();
        $outArray = [];
        foreach ($properties as $property) {
            if ($property->getName() === 'config' || $property->getName() === 'isFromCache') {
                continue;
            }
            $property->setAccessible(true);
            $value = $property->getValue($this);
            if ($value instanceof HitType) {
                $value = $value->value;
            }
            $outArray[$property->getName()] = $value;
        }
        return $outArray;
    }


    /**
     * Return true if all required attributes are given, otherwise return false
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->getVisitorId() && $this->getDs() && $this->getConfig() &&
            $this->getConfig()->getEnvId() && $this->getType();
    }

    /**
     * This function return the error message according to required attributes of class
     *
     * @return string
     */
    abstract public function getErrorMessage(): string;
}
