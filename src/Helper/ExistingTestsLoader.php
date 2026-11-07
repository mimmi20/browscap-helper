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
use BrowscapHelper\Source\OutputAwareInterface;
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

use function date;
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
            if ($source instanceof OutputAwareInterface) {
                $source->setOutput($output);
            }

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
                /** @var array{headers: array<non-empty-string, non-empty-string>, device: array{deviceName: string|null, marketingName: string|null, manufacturer: string|null, brand: string|null, display: array{width: int|null, height: int|null, touch: bool|null, type: string|null, size: float|int|null}, type: string|null, ismobile: bool|null}, client: array{name: string|null, modus: string|null, version: string|null, manufacturer: string|null, bits: int|null, type: string|null, isbot: bool|null}, platform: array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string|null, bits: int|null}, engine: array{name: string|null, version: string|null, manufacturer: string|null}, file: string|null, date-first: string|null, date-last: string|null, raw: mixed} $data */

                try {
                    $osVersion = $versionBuilder->set($data['platform']['version'] ?? '');
                } catch (NotNumericException) {
                    $osVersion = new NullVersion();
                }

                try {
                    $clientVersion = $versionBuilder->set($data['client']['version'] ?? '');
                } catch (NotNumericException) {
                    $clientVersion = new NullVersion();
                }

                try {
                    $engineVersion = $versionBuilder->set($data['engine']['version'] ?? '');
                } catch (NotNumericException) {
                    $engineVersion = new NullVersion();
                }

                try {
                    $deviceManufacturer = $companyLoader->load($data['device']['manufacturer'] ?? '');
                } catch (NotFoundException) {
                    $deviceManufacturer = Company::unknown;
                }

                try {
                    $deviceBrand = $companyLoader->load($data['device']['brand'] ?? '');
                } catch (NotFoundException) {
                    $deviceBrand = Company::unknown;
                }

                try {
                    $osManufacturer = $companyLoader->load($data['platform']['manufacturer'] ?? '');
                } catch (NotFoundException) {
                    $osManufacturer = Company::unknown;
                }

                try {
                    $clientManufacturer = $companyLoader->load($data['client']['manufacturer'] ?? '');
                } catch (NotFoundException) {
                    $clientManufacturer = Company::unknown;
                }

                try {
                    $engineManufacturer = $companyLoader->load($data['engine']['manufacturer'] ?? '');
                } catch (NotFoundException) {
                    $engineManufacturer = Company::unknown;
                }

                yield $uid => new TestProperties(
                    result: new Result(
                        headers: $data['headers'],
                        device: new Device(
                            architecture: Architecture::unknown,
                            deviceName: $data['device']['deviceName'] ?? null,
                            marketingName: $data['device']['marketingName'] ?? null,
                            manufacturer: $deviceManufacturer,
                            brand: $deviceBrand,
                            type: DeviceType::fromName($data['device']['type'] ?? null),
                            display: new Display(
                                width: $data['device']['display']['width'] ?? null,
                                height: $data['device']['display']['height'] ?? null,
                                touch: $data['device']['display']['touch'] ?? null,
                                size: $data['device']['display']['size'] ?? null,
                            ),
                            dualOrientation: $data['device']['dualOrientation'] ?? null,
                            simCount: $data['device']['simCount'] ?? null,
                            bits: Bits::from($data['device']['bits'] ?? 0),
                        ),
                        os: new Os(
                            name: $data['platform']['name'] ?? null,
                            marketingName: $data['platform']['marketingName'] ?? null,
                            manufacturer: $osManufacturer,
                            version: $osVersion,
                            bits: Bits::from($data['platform']['bits'] ?? 0),
                        ),
                        browser: new Browser(
                            name: $data['client']['name'] ?? null,
                            manufacturer: $clientManufacturer,
                            version: $clientVersion,
                            type: ClientType::fromName($data['client']['type'] ?? null),
                            bits: Bits::from($data['client']['bits'] ?? 0),
                            modus: $data['client']['modus'] ?? null,
                        ),
                        engine: new Engine(
                            name: $data['engine']['name'] ?? null,
                            manufacturer: $engineManufacturer,
                            version: $engineVersion,
                        ),
                    ),
                    file: $data['file'],
                    dateFirst: $data['date-first'] ?? date('Y-m-d'),
                    dateLast: $data['date-last'] ?? date('Y-m-d'),
                    raw: $data['raw'],
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
