<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Configuration;
use App\Repository\CharacterRepository;
use App\Service\CharacterService;
use CharlotteDunois\Yasmin\Client;
use CharlotteDunois\Yasmin\Models\Message;
use React\EventLoop\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Authorize a bot with the following URL:
 * https://discordapp.com/oauth2/authorize?client_id=<clientID>&scope=bot&permissions=<permissionMask>
 */
class DiscordConsumerCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'clever:discord:consumer';

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;

    /** @var string */
    private $botToken;

    /** @var \App\Repository\CharacterRepository */
    private $characterRepository;

    /** @var \App\Service\CharacterService */
    private $characterService;

    /**
     * @param string $botToken
     * @param \App\Repository\CharacterRepository $characterRepository
     * @param \App\Service\CharacterService $characterService
     */
    public function __construct(
        string $botToken,
        CharacterRepository $characterRepository,
        CharacterService $characterService
    ) {
        $this->botToken = $botToken;
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

        $io = new SymfonyStyle($input, $output);
        $outputStyle = new OutputFormatterStyle('green', null, ['bold']);
        $output->getFormatter()->setStyle('success', $outputStyle);
        $outputStyle = new OutputFormatterStyle('red');
        $output->getFormatter()->setStyle('error', $outputStyle);

        $this->output = $output;

        $loop = Factory::create();
        $client = new Client([], $loop);

        // --- When an error is received.
        $client->on('error', function ($error) use ($io) {
            $io->warning($error);
        });

        // --- On connection to bot on server.
        $client->on('ready', function () use ($client, $output) {
            $guildNames = [];
            foreach ($client->channels->all() as $channel) {
                if (!in_array($channel->guild->name, $guildNames)) {
                    $guildNames[] = $channel->guild->name;
                }
            }

            $output->writeln(
                sprintf('<success>[Consumer] Logged in as %s to \'%s\' on %s</success>',
                    $client->user->tag,
                    implode(', ', $guildNames),
                    (new \DateTime())->format('Y-m-d H:i:s')
                )
            );
        });

        // --- On any message received.
        $client->on('message', function ($message) use ($client, $self) {
            $body = trim($message->cleanContent);
            if (substr($body, 0, 1) === '!') {
                $self->handleCommand($body, $message);
            }
        });

        try {
            $client->login($this->botToken)->done();
        } catch (\Exception $e) {
            $output->writeln(
                sprintf('<error>%s; %s</error>',
                    (new \DateTime())->format('Y-m-d H:i:s'),
                    $e->getMessage()
                )
            );
        }

        $loop->run();
    }

    /**
     * @param string $body
     * @param \CharlotteDunois\Yasmin\Models\Message $message
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function handleCommand(string $body, Message $message)
    {
        $command = strtok($body, ' ');
        $payload = trim(substr($body, 0 + strlen($command), strlen($body)));

        switch ($command) {
            case '!auth' :
                $this->handleAuth($payload, $message);
                break;
        }
    }

    /**
     * @param string $payload
     * @param \CharlotteDunois\Yasmin\Models\Message $message
     *
     * @throws \Doctrine\ORM\ORMException
     */
    private function handleAuth(string $payload, Message $message)
    {
        /** @var \App\Entity\Character $character */
        $character = $this->characterRepository->findOneBy(['discordAuthToken' => $payload]);
        if (null === $character) {
            $this->output->writeln(sprintf('%s; \'%s\' has used an unknown authentication token.', (new \DateTime)->format('Y-m-d H:i:s'), $message->author->username));
            $message->reply('Unknown authentication token :sweat:');

            return;
        }

        $character->setDiscordAuthToken(null);
        $character->setDiscordUserId($message->member->user->id);
        $this->characterRepository->persist($character);
        $this->characterRepository->flush($character);

        $allowedRoles = $this->characterService->getAllowedRoles($character);
        if (empty($allowedRoles)) {
            $message->reply('You\'re not allowed to have additional roles :thinking:');

            return;
        }

        foreach ($allowedRoles as $allowedRole) {
            $message->member->addRole($allowedRole)->done(
                function () use ($message, $character) {

                    $this->output->writeln(
                        sprintf('%s; Roles set for \'%s\' (%s)',
                            (new \DateTime)->format('Y-m-d H:i:s'),
                            $message->author->username,
                            $character->getEveId()
                        )
                    );
                },
                function ($error) use ($message, $character) {
                    if (!$error instanceof \Exception) {
                        return;
                    }
                    $this->output->writeln(
                        sprintf('<error>%s; Error when setting roles for \'%s\' (%s): "%s".</error>',
                            (new \DateTime)->format('Y-m-d H:i:s'),
                            $message->author->username,
                            $character->getEveId(),
                            $error->getMessage()
                        )
                    );
                }
            );
        }

        $message->reply('Authentication successful: Discord roles set :sunglasses:');
    }
}
