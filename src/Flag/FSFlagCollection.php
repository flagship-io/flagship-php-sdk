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
    private ?VisitorAbstract $visitor;

    /**
     * @var string[]
     */
    private array $keys;

    /**
     * @var array<string, FSFlag> $flags
     */
    private array $flags;

    private int $index = 0; 

    /**
     * @param VisitorAbstract|null $visitor
     * @param array<string, FSFlag> $flags
     */
    public function __construct(?VisitorAbstract $visitor = null, array $flags = [])
    {
        $this->visitor = $visitor;
        $this->flags = $flags;

        if (count($this->flags) === 0) {
            $this->keys = array_map(function ($flag) {
                return $flag->getKey();
            }, $visitor ? $visitor->getFlagsDTO() : []);

            foreach ($this->keys as $key) {
                $this->flags[$key] = new FSFlag($key, $visitor);
            }
        } else {
            $this->keys = array_keys($this->flags);
        }
    }

    public function getSize(): int
    {
        return count($this->keys);
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): FSFlagInterface
    {
        if (!isset($this->flags[$key])) {
            $this->logWarningSprintf(
                $this->visitor?->getConfig(),
                FlagshipConstant::GET_FLAG,
                FlagshipConstant::GET_FLAG_NOT_FOUND,
                [
                    $this->visitor?->getVisitorId(),
                    $key,
                ] 
            );
            return new FSFlag($key);
        }
        return $this->flags[$key];
    }

    public function has(string $key): bool
    {
        return in_array($key, $this->keys);
    }

    /**
     * @inheritDoc
     * @return string[] A set of all keys in the collection.
     */
    public function keys(): array
    {
        return $this->keys;
    }

    public function filter(callable $predicate): FSFlagCollectionInterface
    {
        $flags = [];
        foreach ($this->flags as $key => $flag) {
            if ($predicate($flag, $key, $this)) {
                $flags[$key] = $flag;
            }
        }
        return new FSFlagCollection($this->visitor, $flags);
    }

    public function exposeAll(): void
    {
        foreach ($this->flags as $flag) {
            $flag->visitorExposed();
        }
    }

    /**
     * @inheritDoc
     * @return array<string, FSFlagMetadataInterface> An array containing the metadata for all flags in the collection.
     */
    public function getMetadata(): array
    {
        $metadata = [];
        foreach ($this->flags as $key => $flag) {
            $metadata[$key] = $flag->getMetadata();
        }
        return $metadata;
    }

    public function toJSON(): string
    {
        $serializedData = [];
        foreach ($this->flags as $key => $flag) {
            $metadata = $flag->getMetadata();
            $serializedData[] = [
                'key'                => $key,
                'campaignId'         => $metadata->getCampaignId(),
                'campaignName'       => $metadata->getCampaignName(),
                'variationGroupId'   => $metadata->getVariationGroupId(),
                'variationGroupName' => $metadata->getVariationGroupName(),
                'variationId'        => $metadata->getVariationId(),
                'variationName'      => $metadata->getVariationName(),
                'isReference'        => $metadata->isReference(),
                'campaignType'       => $metadata->getCampaignType(),
                'slug'               => $metadata->getSlug(),
                'hex'                => $this->valueToHex(['v' => $flag->getValue(null, false)]),
            ];
        }
        $json = json_encode($serializedData);
        return $json !== false ? $json : '[]';
    }

    /**
     * @inheritdoc
     */
    public function each(callable $callbackFn): void
    {
        foreach ($this->flags as $key => $flag) {
            $callbackFn($flag, $key, $this);
        }
    }


    /**
     * @inheritDoc
     */
    public function current(): FSFlag
    {
        $key = $this->keys[$this->index];
        return $this->flags[$key];
    }


    /**
     * @inheritDoc
     */
    public function next(): void
    {
        $this->index++;
    }

    /**
     * @inheritDoc
     */
    public function key(): mixed
    {
        return $this->keys[$this->index];
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return isset($this->keys[$this->index]);
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->index = 0;
    }
}
