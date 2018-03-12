<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2018, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command\Helper;

use Symfony\Component\Console\Helper\Helper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * detection class using regexes
 *
 * @category  BrowserDetector
 */
class RegexLoader extends Helper
{
    /**
     * an logger instance
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var array|null
     */
    private $regexes;

    /**
     * @var self|null
     */
    private static $instance;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger  = $logger;
        $this->regexes = Yaml::parseFile(
            __DIR__ . '/../../data/regexes.yaml',
            Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE
        );
    }

    public function getName()
    {
        return 'regex-loader';
    }

    /**
     * @return \Generator|string[]
     */
    public function getRegexes(): \Generator
    {
        foreach ($this->regexes['regexes'] as $data) {
            yield $data;
        }
    }
}
