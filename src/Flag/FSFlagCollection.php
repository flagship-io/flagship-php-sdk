<?php

namespace Flagship\Flag;

use Flagship\Traits\Helper;
use Flagship\Traits\LogTrait;
use Flagship\Enum\FlagshipConstant;
use Flagship\Visitor\VisitorAbstract;

class FSFlagCollection implements FSFlagCollectionInterface
{
    use LogTrait;
    use Helper;
    /**
     * @var VisitorAbstract|null
     */
    private $visitor;

    /**
     * @var string[]
     */
    private $keys;

    /**
     * @var array<string, FSFlag> $flags
     */
    private $flags;

    private $index = 0;

    /**
     * @param VisitorAbstract|null $visitor
     * @param array<string, FSFlag> $flags
     */
    public function __construct(VisitorAbstract $visitor = null, $flags = [])
    {
        $this->visitor = $visitor;
        $this->flags = $flags;

        if (count($this->flags) === 0) {
            $this->keys = array_map(function ($flag) {
                return $flag->getKey();
            },  $visitor ? $visitor->getFlagsDTO() : []);

            foreach ($this->keys as $key) {
                $this->flags[$key] = new FSFlag($key,  $visitor);
            }
        } else {
            $this->keys = array_keys($this->flags);
        }
    }

    public function getSize()
    {
        return count($this->keys);
    }

    public function get($key)
    {
        if (!isset($this->flags[$key])) {
            $this->logWarningSprintf(
                $this->visitor->getConfig(),
                FlagshipConstant::GET_FLAG,
                FlagshipConstant::GET_FLAG_NOT_FOUND,
                [$this->visitor->getVisitorId(), $key]
            );
            return new FSFlag($key);
        }
        return $this->flags[$key];
    }

    public function has($key)
    {
        return in_array($key, $this->keys);
    }

    public function keys()
    {
        return $this->keys;
    }

    public function filter(callable $predicate)
    {
        $flags = [];
        foreach ($this->flags as $key => $flag) {
            if ($predicate($flag, $key, $this)) {
                $flags[$key] = $flag;
            }
        }
        return new FSFlagCollection($this->visitor,  $flags);
    }

    public function exposeAll()
    {
        foreach ($this->flags as $flag) {
            $flag->visitorExposed();
        }
    }

    public function getMetadata()
    {
        $metadata = [];
        foreach ($this->flags as $key => $flag) {
            $metadata[$key] = $flag->getMetadata();
        }
        return $metadata;
    }

    public function toJSON()
    {
        $serializedData = [];
        foreach ($this->flags as $key => $flag) {
            $metadata = $flag->getMetadata();
            $serializedData[] = [
                'key' => $key,
                'campaignId' => $metadata->getCampaignId(),
                'campaignName' => $metadata->getCampaignName(),
                'variationGroupId' => $metadata->getVariationGroupId(),
                'variationGroupName' => $metadata->getVariationGroupName(),
                'variationId' => $metadata->getVariationId(),
                'variationName' => $metadata->getVariationName(),
                'isReference' => $metadata->isReference(),
                'campaignType' => $metadata->getCampaignType(),
                'slug' => $metadata->getSlug(),
                'hex' => $this->valueToHex(['v' => $flag->getValue(null, false)])
            ];
        }
        return $serializedData;
    }

    public function each(callable $callbackfn)
    {
        foreach ($this->flags as $key => $flag) {
            $callbackfn($flag, $key, $this);
        }
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        $key = $this->keys[$this->index];
        if (!array_key_exists($key, $this->flags)) {
            $this->logWarningSprintf(
                $this->visitor->getConfig(),
                FlagshipConstant::GET_FLAG,
                FlagshipConstant::GET_FLAG_MISSING_ERROR,
                [$this->visitor->getVisitorId(), $key]
            );
            return new FSFlag($key);
        }
        return $this->flags[$key];
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->index++;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->keys[$this->index];
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return isset($this->keys[$this->index]);
    }
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->index = 0;
    }
}
