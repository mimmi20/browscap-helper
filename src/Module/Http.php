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

use BrowscapHelper\Helper\Request;
use BrowscapHelper\Module\Check\CheckInterface;
use BrowscapHelper\Module\Mapper\MapperInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleHttpRequest;
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
class Http implements ModuleInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache = null;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var \GuzzleHttp\Psr7\Response|null
     */
    private $detectionResult = null;

    /**
     * @var string
     */
    private $agent = '';

    /**
     * @var \Ubench
     */
    private $bench;

    /**
     * @var array
     */
    private $config = null;

    /**
     * @var \BrowscapHelper\Module\Check\CheckInterface
     */
    private $check;

    /**
     * @var \BrowscapHelper\Module\Mapper\MapperInterface
     */
    private $mapper;

    /**
     * @var \GuzzleHttp\Psr7\Request
     */
    private $request = null;

    /**
     * @var float
     */
    private $duration = 0.0;

    /**
     * @var int
     */
    private $memory = 0;

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
    ) {
        $this->logger = $logger;
        $this->cache  = $cache;
        $this->name   = $name;
        $this->config = $config;
        $this->check  = $check;
        $this->mapper = $mapper;

        $this->bench = new \Ubench();
    }

    /**
     * @param string $agent
     * @param array  $headers
     */
    public function detect(string $agent, array $headers = []): void
    {
        $this->agent = $agent;
        $body        = null;

        $params  = [$this->config['ua-key'] => $agent] + $this->config['params'];
        $headers = $headers + $this->config['headers'];

        if ('GET' === $this->config['method']) {
            $uri = $this->config['uri'] . '?' . http_build_query($params, '', '&');
        } else {
            $uri  = $this->config['uri'];
            $body = http_build_query($params, '', '&');
        }

        $this->request = new GuzzleHttpRequest($this->config['method'], $uri, $headers, $body);
        $requestHelper = new Request();

        $this->detectionResult = null;

        try {
            $this->detectionResult = $requestHelper->getResponse($this->request, new Client());
        } catch (ConnectException $e) {
            $this->logger->error(new ConnectException('could not connect to uri "' . $uri . '"', $this->request, $e));
        } catch (RequestException $e) {
            $this->logger->error($e);
        }
    }

    /**
     * starts the detection timer
     */
    public function startTimer(): void
    {
        $this->bench->start();
    }

    /**
     * stops the detection timer
     */
    public function endTimer(): void
    {
        $this->bench->end();

        $this->duration = (float) $this->bench->getTime(true);
        $this->memory   = (int) $this->bench->getMemoryPeak(true);
    }

    /**
     * returns the needed time
     *
     * @return float
     */
    public function getTime(): float
    {
        return $this->duration;
    }

    /**
     * returns the maximum needed memory
     *
     * @return int
     */
    public function getMaxMemory(): int
    {
        return $this->memory;
    }

    /**
     * @return \UaResult\Result\ResultInterface|null
     */
    public function getDetectionResult(): ?ResultInterface
    {
        if (null === $this->detectionResult) {
            return null;
        }

        try {
            $return = $this->check->getResponse(
                $this->detectionResult,
                $this->request,
                $this->cache,
                $this->logger,
                $this->agent
            );
        } catch (RequestException $e) {
            $this->logger->error($e);

            return null;
        }

        if (isset($return->duration)) {
            $this->duration = $return->duration;

            unset($return->duration);
        }

        if (isset($return->memory)) {
            $this->memory = $return->memory;

            unset($return->memory);
        }

        try {
            if (isset($return->result)) {
                return $this->mapper->map($return->result, $this->agent);
            }

            return $this->mapper->map($return, $this->agent);
        } catch (\UnexpectedValueException $e) {
            $this->logger->error($e);
        }

        return null;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
