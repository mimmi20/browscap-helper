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

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\DeadCode\Rector\StmtsAwareInterface\RemoveDeadInstanceOfAssertRector;
use Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\CodeQuality\Rector\FuncCall\AssertFuncCallToPHPUnitAssertRector;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpVersion(PhpVersion::PHP_85)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        naming: true,
        namedArgs: true,
        instanceOf: true,
        if: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
        phpunitNarrowAsserts: true,
        phpunitMockToStub: true,
    )
    ->withPhpSets(php85: true)
    ->withSkip([
        RemoveDeadInstanceOfRector::class,
        RemoveAlwaysTrueIfConditionRector::class,
        RemoveParentCallWithoutParentRector::class,
        NewMethodCallWithoutParenthesesRector::class,
        PreferPHPUnitThisCallRector::class,
        RemoveDeadInstanceOfAssertRector::class,
        AssertFuncCallToPHPUnitAssertRector::class,
        YieldDataProviderRector::class,
        \Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector::class,
        \Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector::class,
        \Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector::class,
        \Rector\PHPUnit\CodeQuality\Rector\ClassMethod\NoSetupWithParentCallOverrideRector::class,
    ])
    ->withoutParallel()
    ->withMemoryLimit('2048M');
