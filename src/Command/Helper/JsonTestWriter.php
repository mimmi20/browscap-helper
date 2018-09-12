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
use Symfony\Component\Console\Helper\Helper;

class JsonTestWriter extends Helper
{
    public function getName()
    {
        return 'json-test-writer';
    }

    /**
     * @param array  $headers
     * @param string $dir
     * @param int    $number
     *
     * @return void
     */
    public function write(array $headers, string $dir, int $number): void
    {
        try {
            $content = (new Json())->encode(
                $headers,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        } catch (\ExceptionalJSON\EncodeErrorException $e) {
            return;
        }

        file_put_contents(
            $dir . '/' . sprintf('%1$07d', $number) . '.json',
            $content . PHP_EOL
        );
    }
}
