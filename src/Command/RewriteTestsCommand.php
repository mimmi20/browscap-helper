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
namespace BrowscapHelper\Command;

use BrowscapHelper\Command\Helper\JsonNormalizer;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowserDetector\Detector;
use BrowserDetector\DetectorFactory;
use BrowserDetector\Version\VersionInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use UaResult\Result\ResultInterface;

final class RewriteTestsCommand extends Command
{
    /**
     * @var array
     */
    private $tests = [];

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        $this
            ->setName('rewrite-tests')
            ->setDescription('Rewrites existing tests');
    }

    /**
     * Executes the current command.
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException                   When this abstract method is not implemented
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('init Detector ...', OutputInterface::VERBOSITY_NORMAL);

        $cache    = new Psr16Cache(new NullAdapter());
        $factory  = new DetectorFactory($cache, new ConsoleLogger($output));
        $detector = $factory();

        $basePath                = 'vendor/mimmi20/browser-detector/';
        $detectorTargetDirectory = $basePath . 'tests/data/';
        $testSource              = 'tests';

        $this->getHelper('existing-tests-remover')->remove($output, $detectorTargetDirectory);

        $sources = [new JsonFileSource($testSource)];

        $output->writeln('reading already existing tests ...', OutputInterface::VERBOSITY_NORMAL);

        $txtChecks     = [];
        $messageLength = 0;

        foreach ($this->getHelper('existing-tests-loader')->getHeaders($output, $sources) as $header) {
            $seachHeader = (string) UserAgent::fromHeaderArray($header);

            if (array_key_exists($seachHeader, $txtChecks)) {
                $output->writeln('<error>' . sprintf('Header "%s" added more than once --> skipped', $seachHeader) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            $txtChecks[$seachHeader] = $header;
        }

        $testResults = [];
        $counter     = 0;
        $duplicates  = 0;
        $errors      = 0;
        $testCount   = 0;
        $baseMessage = 'checking Header ';

        foreach ($txtChecks as $seachHeader => $headers) {
            ++$counter;

            $message = $baseMessage . sprintf('[%7d]', $counter) . ' - redetect';

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write("\r" . str_pad($message, $messageLength, ' '), false);

            try {
                $result = $this->handleTest($output, $detector, $headers, $message, $messageLength);
            } catch (\UnexpectedValueException $e) {
                ++$errors;
                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln('<error>' . (new \Exception(sprintf('An error occured while checking Headers "%s"', $seachHeader), 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            if (null === $result) {
                ++$duplicates;
                continue;
            }

            $c = mb_strtolower(utf8_decode($result->getDevice()->getManufacturer()->getType()));

            if ('' === $c) {
                $c = 'unknown';
            } else {
                $c = str_replace(['.', ' '], ['', '-'], $c);
            }

            $t = mb_strtolower($result->getDevice()->getType()->getType());

            try {
                $testResults[$c][$t][] = $result->toArray();
            } catch (\UnexpectedValueException $e) {
                ++$errors;
                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln('<error>' . (new \Exception('An error occured while converting a result to an array', 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            ++$testCount;
        }

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);

        $testSchemaUri = 'file://' . realpath($basePath . 'schema/tests.json');

        /** @var JsonNormalizer $jsonNormalizer */
        $jsonNormalizer = $this->getHelperSet()->get('json-normalizer');
        $jsonNormalizer->init($output, $testSchemaUri);

        $output->writeln(sprintf('check result: %7d test(s), %7d duplicate(s), %7d error(s)', $testCount, $duplicates, $errors), OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('rewrite tests ...', OutputInterface::VERBOSITY_NORMAL);

        $messageLength = 0;
        $baseMessage   = 're-write test files in directory ';

        ksort($testResults, SORT_STRING | SORT_ASC);

        foreach ($testResults as $c => $x) {
            $message = $baseMessage . sprintf('tests/data/%s/', $c);

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write("\r" . str_pad($message, $messageLength, ' '), false, OutputInterface::VERBOSITY_VERY_VERBOSE);

            if (!file_exists(sprintf($basePath . 'tests/data/%s', $c))) {
                mkdir(sprintf($basePath . 'tests/data/%s', $c));
            }

            ksort($x, SORT_STRING | SORT_ASC);

            foreach ($x as $t => $data) {
                if (!file_exists(sprintf($basePath . 'tests/data/%s/%s', $c, $t))) {
                    mkdir(sprintf($basePath . 'tests/data/%s/%s', $c, $t));
                }

                foreach (array_chunk($data, 100) as $number => $parts) {
                    $path = $basePath . sprintf('tests/data/%s/%s/%07d.json', $c, $t, $number);

                    $message = $baseMessage . sprintf('tests/data/%s/%s/%07d.json', $c, $t, $number) . ' - normalizing';

                    if (mb_strlen($message) > $messageLength) {
                        $messageLength = mb_strlen($message);
                    }

                    $output->write("\r" . str_pad($message, $messageLength, ' '), false, OutputInterface::VERBOSITY_VERY_VERBOSE);

                    try {
                        $normalized = $jsonNormalizer->normalize($output, $parts, $message, $messageLength);
                    } catch (\InvalidArgumentException | \RuntimeException $e) {
                        $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                        $output->writeln('<error>' . $e . '</error>', OutputInterface::VERBOSITY_NORMAL);

                        continue;
                    }

                    if (null === $normalized) {
                        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                        $output->writeln('<error>' . (new \Exception(sprintf('file "%s" contains invalid json', $path))) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                        return 1;
                    }

                    $message = $baseMessage . sprintf('tests/data/%s/%s/%07d.json', $c, $t, $number) . ' - writing';

                    if (mb_strlen($message) > $messageLength) {
                        $messageLength = mb_strlen($message);
                    }

                    $output->write("\r" . str_pad($message, $messageLength, ' '), false, OutputInterface::VERBOSITY_VERY_VERBOSE);

                    file_put_contents(
                        $path,
                        $normalized
                    );
                }
            }
        }

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('done', OutputInterface::VERBOSITY_NORMAL);

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param Detector        $detector
     * @param array           $headers
     * @param string          $parentMessage
     * @param int             $messageLength
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \UnexpectedValueException
     *
     * @return \UaResult\Result\ResultInterface|null
     */
    private function handleTest(OutputInterface $output, Detector $detector, array $headers, string $parentMessage, int &$messageLength = 0): ?ResultInterface
    {
        $message = $parentMessage . ' - detect for new result ...';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        $newResult = $detector->__invoke($headers);

        $message = $parentMessage . ' - analyze new result ...';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        if (in_array($newResult->getDevice()->getDeviceName(), ['general Desktop', 'general Apple Device', 'general Philips TV'], true)
            || (
                !$newResult->getDevice()->getType()->isMobile()
                && !$newResult->getDevice()->getType()->isTablet()
                && !$newResult->getDevice()->getType()->isTv()
            )
        ) {
            $keys = [
                (string) $newResult->getBrowser()->getName(),
                $newResult->getBrowser()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getEngine()->getName(),
                $newResult->getEngine()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getOs()->getName(),
                $newResult->getOs()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getDevice()->getDeviceName(),
                (string) $newResult->getDevice()->getMarketingName(),
                (string) $newResult->getDevice()->getManufacturer()->getName(),
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return null;
            }

            $this->tests[$key] = 1;
        } elseif (($newResult->getDevice()->getType()->isMobile() || $newResult->getDevice()->getType()->isTablet() || $newResult->getDevice()->getType()->isTv())
            && false === mb_strpos((string) $newResult->getBrowser()->getName(), 'general')
            && !in_array($newResult->getBrowser()->getName(), [null, 'unknown'], true)
            && false === mb_strpos((string) $newResult->getDevice()->getDeviceName(), 'general')
            && !in_array($newResult->getDevice()->getDeviceName(), [null, 'unknown'], true)
        ) {
            $keys = [
                $newResult->getBrowser()->getName(),
                $newResult->getBrowser()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getEngine()->getName(),
                $newResult->getEngine()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getOs()->getName(),
                $newResult->getOs()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                $newResult->getDevice()->getDeviceName(),
                (string) $newResult->getDevice()->getMarketingName(),
                (string) $newResult->getDevice()->getManufacturer()->getName(),
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return null;
            }

            $this->tests[$key] = 1;
        }

        return $newResult;
    }
}
