<?php

/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Helper;

use BrowscapHelper\Entity\TestProperties;
use BrowscapHelper\Source\SourceInterface;
use BrowserDetector\Loader\CompanyLoaderFactory;
use BrowserDetector\Version\Exception\NotNumericException;
use BrowserDetector\Version\NullVersion;
use BrowserDetector\Version\VersionBuilder;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;
use UaBrowserType\Type as ClientType;
use UaData\Company;
use UaDeviceType\Type as DeviceType;
use UaLoader\Exception\NotFoundException;
use UaResult\Bits\Bits;
use UaResult\Browser\Browser;
use UaResult\Device\Architecture;
use UaResult\Device\Device;
use UaResult\Device\Display;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;

use function assert;
use function max;
use function mb_str_pad;
use function min;
use function sprintf;

final class ExistingTestsLoader
{
    /**
     * @param array<SourceInterface> $sources
     *
     * @return iterable<non-empty-string, TestProperties>
     *
     * @throws RuntimeException
     *
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     */
    public function getProperties(
        OutputInterface $output,
        LoggerInterface $logger,
        array $sources,
        int &$messageLength = 0,
    ): iterable {
        $baseMessage = 'reading sources';
        $message     = $baseMessage . ' ...';
        $diff        = $this->messageLength($output, $message, $messageLength);

        $output->writeln(
            mb_str_pad(string: $message, length: $messageLength + $diff),
            OutputInterface::VERBOSITY_NORMAL,
        );

        $companyLoaderFactory = new CompanyLoaderFactory($logger);
        $companyLoader        = $companyLoaderFactory();
        $versionBuilder       = new VersionBuilder();

        foreach ($sources as $source) {
            $baseMessage = sprintf('reading from source %s ', $source->getName());

            if (!$source->isReady($baseMessage)) {
                continue;
            }

            $message = '<info>' . $baseMessage . '...</info>';

            $diff = $this->messageLength($output, $message, $messageLength);

            $output->write(
                "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                newline: false,
                options: OutputInterface::VERBOSITY_VERBOSE,
            );

            foreach ($source->getProperties($baseMessage, $messageLength) as $uid => $data) {
                assert($data instanceof \BrowscapHelper\Source\Entity\TestProperties);

                /** @var array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string|null, bits: int|null} $os */
                $os = $data->getOs();

                try {
                    $osVersion = $versionBuilder->set($os['version'] ?? '');
                } catch (NotNumericException) {
                    $osVersion = new NullVersion();
                }

                /** @var array{name: string|null, version: string|null, manufacturer: string|null, type: string|null, bits: int|null, modus: string|null} $client */
                $client = $data->getClient();

                try {
                    $clientVersion = $versionBuilder->set($client['version'] ?? '');
                } catch (NotNumericException) {
                    $clientVersion = new NullVersion();
                }

                /** @var array{name: string|null, version: string|null, manufacturer: string|null} $engine */
                $engine = $data->getEngine();

                try {
                    $engineVersion = $versionBuilder->set($engine['version'] ?? '');
                } catch (NotNumericException) {
                    $engineVersion = new NullVersion();
                }

                /** @var array{architecture: string|null, deviceName: string|null, marketingName: string|null, manufacturer: string|null, brand: string|null, dualOrientation: bool|null, simCount: int|null, display: array{width: int|null, height: int|null, touch: bool|null, size: float|null, type?: string|null}, type: string|null, ismobile: bool|null, istv: bool|null, bits: int|null} $device */
                $device = $data->getDevice();

                try {
                    $deviceManufacturer = $companyLoader->load($device['manufacturer'] ?? '');
                } catch (NotFoundException) {
                    $deviceManufacturer = Company::unknown;
                }

                try {
                    $deviceBrand = $companyLoader->load($device['brand'] ?? '');
                } catch (NotFoundException) {
                    $deviceBrand = Company::unknown;
                }

                try {
                    $osManufacturer = $companyLoader->load($os['manufacturer'] ?? '');
                } catch (NotFoundException) {
                    $osManufacturer = Company::unknown;
                }

                try {
                    $clientManufacturer = $companyLoader->load($client['manufacturer'] ?? '');
                } catch (NotFoundException) {
                    $clientManufacturer = Company::unknown;
                }

                try {
                    $engineManufacturer = $companyLoader->load($engine['manufacturer'] ?? '');
                } catch (NotFoundException) {
                    $engineManufacturer = Company::unknown;
                }

                yield $uid => new TestProperties(
                    result: new Result(
                        headers: $data->getHeaders(),
                        device: new Device(
                            architecture: Architecture::tryFrom($device['architecture'] ?? '')
                                ?? Architecture::unknown,
                            deviceName: $device['deviceName'] ?? null,
                            marketingName: $device['marketingName'] ?? null,
                            manufacturer: $deviceManufacturer,
                            brand: $deviceBrand,
                            type: DeviceType::fromName($device['type'] ?? null),
                            display: new Display(
                                width: $device['display']['width'] ?? null,
                                height: $device['display']['height'] ?? null,
                                touch: $device['display']['touch'] ?? null,
                                size: $device['display']['size'] ?? null,
                            ),
                            dualOrientation: $device['dualOrientation'] ?? null,
                            simCount: $device['simCount'] ?? null,
                            bits: Bits::from($device['bits'] ?? 0),
                        ),
                        os: new Os(
                            name: $os['name'] ?? null,
                            marketingName: $os['marketingName'] ?? null,
                            manufacturer: $osManufacturer,
                            version: $osVersion,
                            bits: Bits::from($os['bits'] ?? 0),
                        ),
                        browser: new Browser(
                            name: $client['name'] ?? null,
                            manufacturer: $clientManufacturer,
                            version: $clientVersion,
                            type: ClientType::fromName($client['type'] ?? null),
                            bits: Bits::from($client['bits'] ?? 0),
                            modus: $client['modus'] ?? null,
                        ),
                        engine: new Engine(
                            name: $engine['name'] ?? null,
                            manufacturer: $engineManufacturer,
                            version: $engineVersion,
                        ),
                    ),
                    file: $data->getFile(),
                    dateFirst: $data->getDateFirst(),
                    dateLast: $data->getDateLast(),
                    raw: $data->getRaw(),
                    sourceName: $source->getName(),
                    sources: [
                        $source->getName() => [
                            'date-first' => $data->getDateFirst(),
                            'date-last' => $data->getDateLast(),
                        ],
                    ],
                );
            }

            $message = '<info>' . $baseMessage . '- done</info>';

            $diff = $this->messageLength($output, $message, $messageLength);

            $output->writeln(
                "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                OutputInterface::VERBOSITY_VERBOSE,
            );
        }
    }

    /** @throws void */
    private function messageLength(OutputInterface $output, string $message, int &$messageLength): int
    {
        $messageLengthWithoutFormat = Helper::width(
            Helper::removeDecoration($output->getFormatter(), $message),
        );
        $messageLengthWithFormat    = Helper::width($message);

        $messageLength = min(
            max(
                $messageLength,
                $messageLengthWithFormat,
            ),
            200,
        );

        return $messageLengthWithFormat - $messageLengthWithoutFormat;
    }
}
