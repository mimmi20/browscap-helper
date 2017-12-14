<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Module;

use ArrayAccess;
use Countable;
use Iterator;

/**
 * BrowscapHelper.ini parsing class with caching and update capabilities
 *
 * @category  BrowscapHelper
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class ModuleCollection implements Iterator, Countable, ArrayAccess
{
    /**
     * @var \BrowscapHelper\Module\ModuleInterface[]
     */
    private $modules = [];

    /**
     * @var int
     */
    private $position = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * @param \BrowscapHelper\Module\ModuleInterface $module
     *
     * @return void
     */
    public function addModule(ModuleInterface $module): void
    {
        $this->modules[] = $module;
    }

    /**
     * @return \BrowscapHelper\Module\ModuleInterface[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     *
     * @return \BrowscapHelper\Module\ModuleInterface
     */
    public function current(): ModuleInterface
    {
        return $this->modules[$this->position];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     */
    public function next(): int
    {
        ++$this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return int scalar on success, or null on failure
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return isset($this->modules[$this->position]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Count elements of an object
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int the custom count as an integer
     */
    public function count(): int
    {
        return count($this->modules);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param int $offset
     *
     * @return bool true on success or false on failure
     */
    public function offsetExists($offset): bool
    {
        return isset($this->modules[$offset]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param int $offset
     *
     * @return \BrowscapHelper\Module\ModuleInterface|null
     */
    public function offsetGet($offset): ?ModuleInterface
    {
        return isset($this->modules[$offset]) ? $this->modules[$offset] : null;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param int|null                               $offset
     * @param \BrowscapHelper\Module\ModuleInterface $value
     */
    public function offsetSet($offset, $value): void
    {
        if (null === $offset) {
            $this->modules[] = $value;
        } else {
            $this->modules[$offset] = $value;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->modules[$offset]);
    }
}
