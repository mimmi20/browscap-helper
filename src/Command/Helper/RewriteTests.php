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

use BrowscapHelper\Source\Ua\UserAgent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Helper;

final class RewriteTests extends Helper
{
    public function getName()
    {
        return 'rewrite-tests';
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param array                    $txtChecks
     * @param string                   $testSource
     *
     * @throws \Localheinz\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Localheinz\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \Localheinz\Json\Normalizer\Exception\InvalidIndentStyleException
     * @throws \Localheinz\Json\Normalizer\Exception\InvalidIndentSizeException
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return void
     */
    public function rewrite(LoggerInterface $logger, array $txtChecks, string $testSource): void
    {
        /** @var JsonTestWriter $jsonTestWriter */
        $jsonTestWriter = $this->getHelperSet()->get('json-test-writer');
        $folderChunks   = array_chunk(array_unique(array_keys($txtChecks)), 1000);

        foreach ($folderChunks as $folderId => $folderChunk) {
            $headers = [];

            foreach ($folderChunk as $headerString) {
                $headers[] = UserAgent::fromString($headerString)->getHeader();
            }

            $jsonTestWriter->write($logger, $headers, $testSource, $folderId);
        }
    }
}
