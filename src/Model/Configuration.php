<?php

declare(strict_types=1);

namespace App\Model;

class Configuration
{
    /** @var array */
    private $poweredBy;

    /** @var array */
    private $authorizedRoles = [];

    /** @var array */
    private $allianceRoles = [];

    /** @var array */
    private $corporationRoles = [];

    /** @var array */
    private $corporationBulletins = [];

    /** @var string */
    private $botLogChannel;

    /**
     * @param array $cleverConfiguration
     */
    public function __construct(array $cleverConfiguration)
    {
        $cleverConfiguration = $cleverConfiguration['clever_configuration'];

        $this->poweredBy = isset($cleverConfiguration['powered_by']) && is_array($cleverConfiguration['powered_by'])
            ? $cleverConfiguration['powered_by']
            : [];

        $this->authorizedRoles = isset($cleverConfiguration['authorized_roles']) && is_array($cleverConfiguration['authorized_roles'])
            ? $cleverConfiguration['authorized_roles']
            : [];

        $this->allianceRoles = isset($cleverConfiguration['alliance_roles']) && is_array($cleverConfiguration['alliance_roles'])
            ? $cleverConfiguration['alliance_roles']
            : [];

        $this->corporationRoles = isset($cleverConfiguration['corporation_roles']) && is_array($cleverConfiguration['corporation_roles'])
            ? $cleverConfiguration['corporation_roles']
            : [];

        $this->corporationBulletins = isset($cleverConfiguration['corporation_bulletins']) && is_array($cleverConfiguration['corporation_bulletins'])
            ? $cleverConfiguration['corporation_bulletins']
            : [];

        $this->botLogChannel = (string) $cleverConfiguration['bot_log_channel'];
    }

    /**
     * @return array
     */
    public function getPoweredBy(): array
    {
        return $this->poweredBy;
    }

    /**
     * @return array
     */
    public function getAuthorizedRoles(): array
    {
        return $this->authorizedRoles;
    }

    /**
     * @return array
     */
    public function getAllianceRoles(): array
    {
        return $this->allianceRoles;
    }

    /**
     * @return array
     */
    public function getCorporationRoles(): array
    {
        return $this->corporationRoles;
    }

    /**
     * @return array
     */
    public function getCorporationBulletins(): array
    {
        return $this->corporationBulletins;
    }

    /**
     * @return string
     */
    public function getBotLogChannel(): string
    {
        return $this->botLogChannel;
    }
}
