<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source;

use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;
use Wurfl\Request\GenericRequestFactory;

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class UapCoreSource implements SourceInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param int $limit
     *
     * @return string[]
     */
    public function getUserAgents(int $limit = 0): iterator
    {
        $counter = 0;

        foreach ($this->loadFromPath() as $row) {
            if ($limit && $counter >= $limit) {
                return;
            }

            yield trim($row['user_agent_string']);
            ++$counter;
        }
    }

    /**
     * @return \UaResult\Result\Result[]
     */
    public function getTests(): iterator
    {
        foreach ($this->loadFromPath() as $row) {
            $request  = (new GenericRequestFactory())->createRequestForUserAgent($row['user_agent_string']);
            $browser  = new Browser(null);
            $device   = new Device(null, null);
            $platform = new Os(null, null);
            $engine   = new Engine(null);

            yield trim($row['user_agent_string']) => new Result($request, $device, $platform, $browser, $engine);
        }
    }

    /**
     * @return array[]
     */
    private function loadFromPath(): iterator
    {
        $path = 'vendor/thadafinser/uap-core/tests';

        if (!file_exists($path)) {
            return;
        }

        $this->logger->info('    reading path ' . $path);

        $allTests = [];
        $finder   = new Finder();
        $finder->files();
        $finder->name('*.yaml');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($path);

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }

            if ('yaml' !== $file->getExtension()) {
                continue;
            }

            $filepath = $file->getPathname();

            $this->logger->info('    reading file ' . str_pad($filepath, 100, ' ', STR_PAD_RIGHT));
            $data = Yaml::parse(file_get_contents($filepath));

            if (!is_array($data)) {
                continue;
            }

            if (empty($data['test_cases']) || !is_array($data['test_cases'])) {
                continue;
            }

            foreach ($data['test_cases'] as $row) {
                if (empty($row['user_agent_string'])) {
                    continue;
                }

                $agent = trim($row['user_agent_string']);

                if (array_key_exists($agent, $allTests)) {
                    continue;
                }

                yield $row;
                $allTests[$agent] = 1;
            }
        }
    }
}
