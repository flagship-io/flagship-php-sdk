<?php

namespace Flagship\Visitor;

use Flagship\Model\FlagDTO;

trait CampaignsData
{
    public function campaignsModifications()
    {
        return [
            (new FlagDTO())
                ->setKey('Number')
                ->setValue(5)
                ->setIsReference(false)
                ->setVariationGroupId('c8pimlr7n0ig3a0pt2jg')
                ->setCampaignId('c8pimlr7n0ig3a0pt2ig')
                ->setVariationId('c8pimlr7n0ig3a0pt2kg')
                ->setSlug("campaign_1")
                ->setCampaignType("ab"),
            (new FlagDTO())
                ->setKey('isBool')
                ->setValue(false)
                ->setIsReference(false)
                ->setVariationGroupId('c8pimlr7n0ig3a0pt2jg')
                ->setCampaignId('c8pimlr7n0ig3a0pt2ig')
                ->setVariationId('c8pimlr7n0ig3a0pt2kg')
                ->setSlug("campaign_1")
                ->setCampaignType("ab"),
            (new FlagDTO())
                ->setKey('background')
                ->setValue('EE3300')
                ->setIsReference(false)
                ->setVariationGroupId('c8pimlr7n0ig3a0pt2jg')
                ->setCampaignId('c8pimlr7n0ig3a0pt2ig')
                ->setVariationId('c8pimlr7n0ig3a0pt2kg')
                ->setSlug("campaign_1")
                ->setCampaignType("ab"),
            (new FlagDTO())
                ->setKey('borderColor')
                ->setValue('blue')
                ->setIsReference(false)
                ->setVariationGroupId('c8pimlr7n0ig3a0pt2jg')
                ->setCampaignId('c8pimlr7n0ig3a0pt2ig')
                ->setVariationId('c8pimlr7n0ig3a0pt2kg')
                ->setSlug("campaign_1")
                ->setCampaignType("ab"),
            (new FlagDTO())
                ->setKey('Null')
                ->setValue(null)
                ->setIsReference(false)
                ->setVariationGroupId('c8pimlr7n0ig3a0pt2jg')
                ->setCampaignId('c8pimlr7n0ig3a0pt2ig')
                ->setVariationId('c8pimlr7n0ig3a0pt2kg')
                ->setSlug("campaign_1")
                ->setCampaignType("ab"),
            (new FlagDTO())
                ->setKey('Empty')
                ->setValue("")
                ->setIsReference(false)
                ->setVariationGroupId('c8pimlr7n0ig3a0pt2jg')
                ->setCampaignId('c8pimlr7n0ig3a0pt2ig')
                ->setVariationId('c8pimlr7n0ig3a0pt2kg')
                ->setSlug("campaign_1")
                ->setCampaignType("ab"),
            (new FlagDTO())
                ->setKey('php')
                ->setValue("value2")
                ->setIsReference(false)
                ->setVariationGroupId('c7q1lmuru9u05agq3apg')
                ->setCampaignId('c7q1lmuru9u05agq3aog')
                ->setVariationId('c7q1m8p172r04gs741og')
                ->setSlug("campaign_2")
                ->setCampaignType("ab"),
        ];
    }

    public function campaigns()
    {
        return [
            "visitorId" => "",
            "campaigns" => [
                [
                    "id" => "c8pimlr7n0ig3a0pt2ig",
                    "slug" => "campaign_1",
                    "type" => "ab",
                    "variationGroupId" => "c8pimlr7n0ig3a0pt2jg",
                    "variation" => [
                        "id" => "c8pimlr7n0ig3a0pt2kg",
                        "modifications" => [
                            "type" => "FLAG",
                            "value" => [
                                "Number" => 5,
                                "isBool" => false,
                                "background" => "EE3300",
                                "borderColor" => "blue",
                                "Null" => null,
                                "Empty" => ""
                            ]
                        ],
                        "reference" => false
                    ]
                ],
                [
                    "id" => "c7q1lmuru9u05agq3aog",
                    "slug" => "campaign_2",
                    "type" => "ab",
                    "variationGroupId" => "c7q1lmuru9u05agq3apg",
                    "variation" => [
                        "id" => "c7q1m8p172r04gs741og",
                        "modifications" => [
                            "type" => "FLAG",
                            "value" => [
                                "php" => "value2"
                            ]
                        ],
                        "reference" => false
                    ]
                ]
            ]
        ];
    }

    public function campaigns2()
    {
        return [
            "visitorId" => "",
            "campaigns" => [
                [
                    "id" => "c69sir3q6mc0ggqin8ag",
                    "slug" => "campaign_3",
                    "type" => "toggle",
                    "variationGroupId" => "c69sir3q6mc0ggqin8bg",
                    "variation" => [
                        "id" => "c69sir3q6mc0ggqin8c0",
                        "modifications" => [
                            "type" => "FLAG",
                            "value" => [
                                "myAwesomeFeature" => 10
                            ]
                        ],
                        "reference" => false
                    ]
                ]
            ]
        ];
    }
}
