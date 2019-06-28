<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Character;
use App\Model\Configuration;
use App\Repository\CharacterRepository;
use App\Service\CharacterService;
use App\Service\RefreshAuthorization;
use CharlotteDunois\Yasmin\Client;
use CharlotteDunois\Yasmin\Models\GuildMember;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DiscordPoliceCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'clever:discord:police';

    /** @var string */
    private $botToken;

    /** @var \App\Repository\CharacterRepository */
    private $characterRepository;

    /** @var \App\Service\CharacterService */
    private $characterService;

    /** @var \App\Model\Configuration */
    private $configuration;

    /**
     * @param string $botToken
     * @param \App\Model\Configuration $configuration
     * @param \App\Repository\CharacterRepository $characterRepository
     * @param \App\Service\CharacterService $characterService
     */
    public function __construct(
        string $botToken,
        Configuration $configuration,
        CharacterRepository $characterRepository,
        CharacterService $characterService
    ) {
        $this->botToken = $botToken;
        $this->configuration = $configuration;
        $this->characterRepository = $characterRepository;
        $this->characterService = $characterService;

        parent::__construct();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $self = $this;

        $loop = Factory::create();
        $client = new Client([], $loop);

        $loop->addTimer(30, function () use ($loop) {
            $loop->stop();
        });

        $loop->futureTick(function () use ($client, $self) {
            // --- When an error is received.
            $client->on('error', function ($error) {
                // log me
            });

            // --- On connection to bot on server.
            $client->on('ready', function () use ($client, $self) {
                // log the start-up

                $client->channels->get($this->configuration->getBotLogChannel())->send('Police arrived :oncoming_police_car: Checking roles.');

                foreach ($client->guilds->all() as $guild) {
                    foreach ($guild->members->all() as $member) {
                        $character = $this->characterService->refreshCharacterByDiscordUserId($member->user->id);
                        if (null === $character) {
                            continue;
                        }

                        $this->checkAuthorizationForMember($character, $member, $client);
                    }
                }
            });
        });

        $client->login($this->botToken)->done();
        $loop->run();
    }

    /**
     * @param \App\Entity\Character $character
     * @param \CharlotteDunois\Yasmin\Models\GuildMember $member
     * @param \CharlotteDunois\Yasmin\Client $client
     */
    private function checkAuthorizationForMember(Character $character, GuildMember $member, Client $client)
    {
        $allowedRoles = $this->characterService->getAllowedRoles($character);
        $currentRoles = [];
        foreach ($member->roles->all() as $currentRole) {
            $currentRoles[] = $currentRole->id;
        }

        $rolesToAdd = array_diff($allowedRoles, $currentRoles);

        if (!empty($rolesToAdd)) {
            $this->addRoles($rolesToAdd, $member, $client);
        }

        $otherRoles = array_diff($currentRoles, $allowedRoles);
        $authorizedRoles = $this->configuration->getAuthorizedRoles();
        $rolesToRemove = array_intersect($otherRoles, $authorizedRoles);

        if (!empty($rolesToRemove)) {
            $this->removeRoles($rolesToRemove, $member, $client);
        }
    }

    /**
     * @param array $rolesToAdd
     * @param \CharlotteDunois\Yasmin\Models\GuildMember $member
     * @param \CharlotteDunois\Yasmin\Client $client
     */
    private function addRoles(array $rolesToAdd, GuildMember $member, Client $client)
    {
        $member->addRoles($rolesToAdd)->done(
            function () use ($member, $client) {
                $client->channels->get($this->configuration->getBotLogChannel())->send(sprintf('Roles added for \'%s\'.', $member->user->username));
            },
            function ($error) use ($member, $client) {
                $client->channels->get($this->configuration->getBotLogChannel())->send(
                    sprintf('Error in adding roles for \'%s\': %s',
                        $member->user->username,
                        $error
                    )
                );
            }
        );
    }

    /**
     * @param array $rolesToRemove
     * @param \CharlotteDunois\Yasmin\Models\GuildMember $member
     * @param \CharlotteDunois\Yasmin\Client $client
     */
    private function removeRoles(array $rolesToRemove, GuildMember $member, Client $client)
    {
        $member->removeRoles($rolesToRemove)->done(
            function () use ($member, $client) {
                $client->channels->get($this->configuration->getBotLogChannel())->send(sprintf('Roles removed for \'%s\'.', $member->user->username));
            },
            function ($error) use ($member, $client) {
                $client->channels->get($this->configuration->getBotLogChannel())->send(
                    sprintf('Error in removing roles for \'%s\': %s',
                        $member->user->username,
                        $error
                    )
                );
            }
        );
    }
}
