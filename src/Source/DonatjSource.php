<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2020, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source;

use BrowscapHelper\Source\Ua\UserAgent;
use ExceptionalJSON\DecodeErrorException;
use JsonClass\Json;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class DonatjSource implements SourceInterface, OutputAwareInterface
{
    use GetNameTrait;
    use OutputAwareTrait;

    private const NAME = 'donatj/phpuseragentparser';
    private const PATH = 'vendor/donatj/phpuseragentparser/Tests';

    /**
     * @param string $parentMessage
     *
     * @return bool
     */
    public function isReady(string $parentMessage): bool
    {
        if (file_exists(self::PATH)) {
            return true;
        }

        $this->writeln("\r" . '<error>' . $parentMessage . sprintf('- path %s not found</error>', self::PATH), OutputInterface::VERBOSITY_NORMAL);

        return false;
    }

    /**
     * @param string $message
     * @param int    $messageLength
     *
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(string $message, int &$messageLength = 0): iterable
    {
        foreach ($this->loadFromPath($message, $messageLength) as $test => $data) {
            $ua    = UserAgent::fromUseragent(trim($test));
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $ua->getHeaders();
        }
    }

    /**
     * @param string $parentMessage
     * @param int    $messageLength
     *
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    private function loadFromPath(string $parentMessage, int &$messageLength = 0): iterable
    {
        $message = $parentMessage . sprintf('- reading path %s', self::PATH);

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);

        $finder = new Finder();
        $finder->files();
        $finder->name('user_agents.json');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in(self::PATH);

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            $filepath = $file->getPathname();

            $message = $parentMessage . sprintf('- reading file %s', $filepath);

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $this->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            $content = $file->getContents();

            if ('' === $content || PHP_EOL === $content) {
                continue;
            }

            try {
                $provider = (new Json())->decode(
                    $content,
                    true
                );
            } catch (DecodeErrorException $e) {
                $this->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                $this->writeln('    <error>parsing file content [' . $filepath . '] failed</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            if (!is_array($provider)) {
                continue;
            }

            foreach ($provider as $test => $data) {
                yield $test => $data;
            }
        }
    }
}
