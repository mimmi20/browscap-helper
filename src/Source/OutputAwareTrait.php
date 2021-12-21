<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Source;

use Symfony\Component\Console\Output\OutputInterface;

trait OutputAwareTrait
{
    private ?OutputInterface $output = null;

    /**
     * @throws void
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Writes a message to the output.
     *
     * @param iterable<string|null>|string $messages The message as an iterable of strings or a single string
     * @param bool                         $newline  Whether to add a newline
     * @param int                          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @throws void
     */
    public function write($messages, bool $newline = false, int $options = 0): void
    {
        if (null === $this->output) {
            return;
        }

        $this->output->write($messages, $newline, $options);
    }

    /**
     * Writes a message to the output and adds a newline at the end.
     *
     * @param iterable<string|null>|string $messages The message as an iterable of strings or a single string
     * @param int                          $options  A bitmask of options (one of the OUTPUT or VERBOSITY constants), 0 is considered the same as self::OUTPUT_NORMAL | self::VERBOSITY_NORMAL
     *
     * @throws void
     */
    public function writeln($messages, int $options = 0): void
    {
        if (null === $this->output) {
            return;
        }

        $this->output->writeln($messages, $options);
    }
}
