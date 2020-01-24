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
namespace BrowscapHelper\Source;

use BrowscapHelper\Source\Ua\UserAgent;
use Symfony\Component\Console\Output\OutputInterface;

final class PdoSource implements SourceInterface, OutputAwareInterface
{
    use GetNameTrait;
    use OutputAwareTrait;

    /**
     * @var \PDO
     */
    private $pdo;

    private const NAME = 'PDO';

    /**
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $parentMessage
     *
     * @return bool
     */
    public function isReady(string $parentMessage): bool
    {
        return extension_loaded('PDO');
    }

    /**
     * @param string $message
     * @param int    $messageLength
     *
     * @return array[]|iterable
     */
    public function getHeaders(string $message, int &$messageLength = 0): iterable
    {
        foreach ($this->getAgents() as $row) {
            $ua    = UserAgent::fromUseragent(trim($row->agent));
            $agent = (string) $ua;

            if (empty($agent)) {
                continue;
            }

            yield $ua->getHeaders();
        }
    }

    /**
     * @return iterable|\stdClass[]
     */
    private function getAgents(): iterable
    {
        $sql = 'SELECT DISTINCT SQL_BIG_RESULT HIGH_PRIORITY `agent` FROM `agents` ORDER BY `lastTimeFound` DESC, `count` DESC, `idAgents` DESC';

        $driverOptions = [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY];

        /** @var \PDOStatement $stmt */
        $stmt = $this->pdo->prepare($sql, $driverOptions);
        $stmt->execute();

        while ($row = $stmt->fetch(\PDO::FETCH_OBJ)) {
            yield $row;
        }
    }
}
