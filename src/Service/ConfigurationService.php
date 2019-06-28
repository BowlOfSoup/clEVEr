<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Character;
use App\Model\Configuration;

class ConfigurationService
{
    /** @var \App\Model\Configuration */
    private $configuration;

    /**
     * @param \App\Model\Configuration $configuration
     */
    public function __construct(
        Configuration $configuration
    ) {
        $this->configuration = $configuration;
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @return bool
     */
    public function isCharacterCorporationConfigured(Character $character): bool
    {
        $corporationRoles = $this->configuration->getCorporationRoles();
        $configuredCorporations = array_keys($corporationRoles);

        return in_array($character->getCorporation()->getEveId(), $configuredCorporations);
    }

    /**
     * @param \App\Entity\Character $character
     *
     * @return bool
     */
    public function isCharacterAllowedToEditTheBulletin(Character $character): bool
    {
        $corporationBulletins = $this->configuration->getCorporationBulletins();
        $configuredCorporations = array_keys($corporationBulletins);

        if (in_array($character->getCorporation()->getEveId(), $configuredCorporations)) {
            $allowedCharacters = $corporationBulletins[$character->getCorporation()->getEveId()];

            return in_array($character->getEveId(), $allowedCharacters);
        }

        return false;
    }

    /**
     * @return array
     */
    public function getPoweredBy(): array
    {
        $poweredBy = $this->configuration->getPoweredBy();

        $imageUrl = null;
        if (isset($poweredBy['alliance_id']) && null !== $poweredBy['alliance_id']) {
            $imageUrl = sprintf('https://image.eveonline.com/Alliance/%s_128.png', $poweredBy['alliance_id']);
        } elseif (isset($poweredBy['corporation_id']) && null !== $poweredBy['corporation_id']) {
            $imageUrl = sprintf('https://image.eveonline.com/Corporation/%s_128.png', $poweredBy['corporation_id']);
        }

        return [
            'imageUrl' => $imageUrl,
            'name' => isset($poweredBy['name']) && null !== $poweredBy['name'] ? $poweredBy['name'] : null,
        ];
    }
}
