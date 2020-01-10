<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2019, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command\Helper;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Helper;

final class JsonTestWriter extends Helper
{
    public function getName()
    {
        return 'json-test-writer';
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param array                    $headers
     * @param string                   $dir
     * @param int                      $number
     *
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function write(LoggerInterface $logger, array $headers, string $dir, int $number): void
    {
        $fileName = $dir . '/' . sprintf('%1$07d', $number) . '.json';
        $schema   = 'file://' . realpath(__DIR__ . '/../../../schema/tests.json');

        /** @var JsonNormalizer $jsonNormalizer */
        $jsonNormalizer = $this->getHelperSet()->get('json-normalizer');
        $normalized     = $jsonNormalizer->normalize($logger, $headers, $schema);

        if (null === $normalized) {
            return;
        }

        file_put_contents($fileName, $normalized);
    }
}
