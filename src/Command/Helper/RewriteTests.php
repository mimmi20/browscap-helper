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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * rewriting tests
 */
class RewriteTests extends Helper
{
    public function getName()
    {
        return 'rewrite-tests';
    }

    /**
     * @param OutputInterface $output
     * @param array           $txtChecks
     * @param string          $testSource
     *
     * @return void
     */
    public function rewrite(OutputInterface $output, array $txtChecks, string $testSource): void
    {
        $output->writeln('rewrite tests ...');

        /** @var JsonTestWriter $jsonTestWriter */
        $jsonTestWriter = $this->getHelperSet()->get('json-test-writer');

        /** @var YamlTestWriter $yamlTestWriter */
        $yamlTestWriter = $this->getHelperSet()->get('yaml-test-writer');

        $folderChunks = array_chunk(array_unique(array_keys($txtChecks)), 1000);

        foreach ($folderChunks as $folderId => $folderChunk) {
            $headers = [];

            foreach ($folderChunk as $encodeHeaders) {
                $headers[] = json_decode($encodeHeaders);
            }

            $jsonTestWriter->write(
                $headers,
                $testSource,
                $folderId
            );
            $yamlTestWriter->write(
                $headers,
                $testSource,
                $folderId
            );
        }
    }
}
