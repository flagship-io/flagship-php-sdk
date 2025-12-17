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
     * @var float
     */
    protected float $createdAt;

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
        $this->createdAt = $this->getNow();
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
     * @return self
     */
    public function setVisitorId(string $visitorId): self
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
     * @return self
     */
    public function setDs(string $ds): self
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

    protected function setType(HitType $type): self
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
     * @return self
     */
    public function setConfig(FlagshipConfig $config): self
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
     * @return self
     */
    public function setAnonymousId(?string $anonymousId): self
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
     * @return self
     */
    public function setUserIP(?string $userIP): self
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
     * @return self
     */
    public function setScreenResolution(?string $screenResolution): self
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
     * @return self
     */
    public function setLocale(?string $locale): self
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
     * @return self
     */
    public function setSessionNumber(float|int|string|null $sessionNumber): self
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
     * @return self
     */
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return float
     */
    public function getCreatedAt(): float
    {
        return $this->createdAt;
    }

    /**
     * @param float $createdAt
     * @return self
     */
    public function setCreatedAt(float $createdAt): self
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
     * @return self
     */
    protected function setIsFromCache(bool $isFromCache): self
    {
        $this->isFromCache = $isFromCache;
        return $this;
    }

    /**
     * Return an associative array of the class with Api parameters as keys
     *
     * @return array<string, mixed>
     */
    public function toApiKeys(): array
    {
        $data = [
            FlagshipConstant::VISITOR_ID_API_ITEM      => $this->visitorId ?: $this->anonymousId,
            FlagshipConstant::DS_API_ITEM              => $this->getDs(),
            FlagshipConstant::CUSTOMER_ENV_ID_API_ITEM => $this->getConfig()?->getEnvId(),
            FlagshipConstant::T_API_ITEM               => $this->getType()->value,
            FlagshipConstant::CUSTOMER_UID             => null,
            FlagshipConstant::QT_API_ITEM              => $this->getNow() - $this->createdAt,
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
     * @template T of object
     * @param class-string<T> $class
     * @param array<string, mixed> $data
     * @return T
     * @throws ReflectionException
     */
    public static function hydrate(string $class, array $data): object
    {

        $reflector = new ReflectionClass($class);
        $objet = $reflector->newInstanceWithoutConstructor();
        foreach ($data as $key => $value) {
            $method = 'set' . ucwords($key);
            if (is_callable(array($objet, $method))) {
                if ($key === "type" && is_string($value)) {
                    $value = HitType::from($value);
                }
                $objet->$method($value);
            }
        }
        if ($objet instanceof HitAbstract) {
            $objet->setIsFromCache(true);
        }
        return $objet;
    }

    /**
     * @return array<string,mixed>
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
            $this->getConfig()->getEnvId();
    }

    /**
     * This function return the error message according to required attributes of class
     *
     * @return string
     */
    abstract public function getErrorMessage(): string;
}
