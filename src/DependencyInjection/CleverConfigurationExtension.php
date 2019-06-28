<?php

declare(strict_types=1);

namespace App\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CleverConfigurationExtension extends Extension
{
    /**
     * {@inheritDoc}
     *
     * @throws \Exception
     * @throws \UnexpectedValueException
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->setParameter('clever_configuration', $this->getContentFromFile('configuration'));
    }

    /**
     * @param string $filename
     *
     * @throws \Exception
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    private function getContentFromFile(string $filename): array
    {
        if (!file_exists(__DIR__ . '/../../' . $filename . '.local.json')) {
            throw new \Exception(sprintf('\'%s.local.json\' is missing. Did you copy \'%s.dist.json\'?', $filename, $filename));
        }
        $filename = $filename . '.local.json';

        $rawConfiguration = file_get_contents(__DIR__ . '/../../' . $filename);
        $configuration = json_decode($rawConfiguration, true);

        if (JSON_ERROR_NONE !== json_last_error() || !in_array(substr($rawConfiguration, 0, 1), ['{', '['])) {
            throw new \UnexpectedValueException(sprintf('\'%s\' does not contain valid JSON', $filename));
        }

        return $configuration;
    }
}
