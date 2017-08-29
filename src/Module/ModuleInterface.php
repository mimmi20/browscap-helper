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
namespace BrowscapHelper\Module;

use BrowscapHelper\Module\Check\CheckInterface;
use BrowscapHelper\Module\Mapper\MapperInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use UaResult\Result\ResultInterface;

/**
 * BrowscapHelper.ini parsing class with caching and update capabilities
 *
 * @category  BrowscapHelper
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
interface ModuleInterface
{
    /**
     * creates the module
     *
     * @param \Psr\Log\LoggerInterface                      $logger
     * @param \Psr\Cache\CacheItemPoolInterface             $cache
     * @param string                                        $name
     * @param array                                         $config
     * @param \BrowscapHelper\Module\Check\CheckInterface   $check
     * @param \BrowscapHelper\Module\Mapper\MapperInterface $mapper
     */
    public function __construct(
        LoggerInterface $logger,
        CacheItemPoolInterface $cache,
        string $name,
        array $config,
        CheckInterface $check,
        MapperInterface $mapper
    );

    /**
     * @param string $agent
     * @param array  $headers
     */
    public function detect(string $agent, array $headers = []): void;

    /**
     * starts the detection timer
     */
    public function startTimer(): void;

    /**
     * stops the detection timer
     */
    public function endTimer(): void;

    /**
     * returns the needed time
     *
     * @return float
     */
    public function getTime(): float;

    /**
     * returns the maximum needed memory
     *
     * @return int
     */
    public function getMaxMemory(): int;

    /**
     * @return \UaResult\Result\ResultInterface|null
     */
    public function getDetectionResult(): ?ResultInterface;
}
