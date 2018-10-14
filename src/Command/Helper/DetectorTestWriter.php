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

use JsonClass\Json;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Helper;

class DetectorTestWriter extends Helper
{
    public function getName()
    {
        return 'detector-test-writer';
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param array                    $tests
     * @param string                   $dir
     * @param int                      $folderId
     * @param int                      $fileId
     *
     * @return void
     */
    public function write(LoggerInterface $logger, array $tests, string $dir, int $folderId, int $fileId): void
    {
        try {
            $content = (new Json())->encode(
                $tests,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
            );
        } catch (\ExceptionalJSON\EncodeErrorException $e) {
            $logger->critical('could not encode content');

            return;
        }

        file_put_contents(
            $dir . '/test-' . sprintf('%1$07d', $folderId) . '-' . sprintf('%1$03d', $fileId) . '.json',
            $content . PHP_EOL
        );
    }
}
