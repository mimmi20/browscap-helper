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
use Symfony\Component\Yaml\Yaml;

class YamlTestWriter extends Helper
{
    public function getName()
    {
        return 'yaml-test-writer';
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
        $content = Yaml::dump($headers, 2, 1, Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE);

        file_put_contents(
            $dir . '/' . sprintf('%1$07d', $number) . '.yaml',
            $content . PHP_EOL
        );
    }
}
