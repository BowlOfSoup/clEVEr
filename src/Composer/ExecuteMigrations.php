<?php

declare(strict_types=1);

namespace App\Composer;

use Composer\IO\IOInterface;
use Composer\Script\Event;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ExecuteMigrations
{
    /**
     * @param \Composer\Script\Event $event
     */
    public static function run(Event $event)
    {
        $io = $event->getIO();

        try {
            $process = new Process('bin/console doctrine:migrations:migrate --no-interaction --no-debug');
            $process->setTimeout(null);
            $process->mustRun(static::createIOCallback($io));
        } catch (ProcessFailedException $e) {
            // Doctrine is not available, gracefully exit.
        }
    }

    /**
     * @param \Composer\IO\IOInterface $io
     *
     * @throws \RuntimeException
     *
     * @return \Closure
     */
    private static function createIOCallback(IOInterface $io)
    {
        static $callback;

        if (is_callable($callback)) {
            return $callback;
        }

        return $callback = function ($type, $buffer) use ($io) {
            if (Process::ERR === $type) {
                $io->writeError($buffer, false);
            } else {
                $io->write($buffer, false);
            }
        };
    }
}
