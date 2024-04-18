<?php
/**
 * MIT License
 *
 * Copyright (c) 2024-Present Kevin Traini
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

declare(strict_types=1);

namespace Archict\Core\Bricks;

use Archict\Core\Fixtures\brick1\src\Service1;
use Archict\Core\Fixtures\brick1\src\Service1Configuration;
use Composer\InstalledVersions;
use PHPUnit\Framework\TestCase;

class LoadBricksTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        InstalledVersions::reload(
            [
                'root'     => InstalledVersions::getRootPackage(),
                'versions' => [
                    'brick1'      => [
                        'dev_requirement' => false,
                        'type'            => BricksLoader::PACKAGE_TYPE,
                        'install_path'    => __DIR__ . '/../Fixtures/brick1',
                    ],
                    'brick2'      => [
                        'dev_requirement' => true,
                        'type'            => BricksLoader::PACKAGE_TYPE,
                        'install_path'    => __DIR__ . '/../Fixtures/brick2',
                    ],
                    'not_a_brick' => [
                        'dev_requirement' => false,
                        'type'            => 'library',
                        'install_path'    => __DIR__ . '/../Fixtures/not_a_brick',
                    ],
                ],
            ]
        );
    }

    public static function tearDownAfterClass(): void
    {
        InstalledVersions::reload(
            [
                'root'     => InstalledVersions::getRootPackage(),
                'versions' => [],
            ]
        );
    }

    public function testItLoadBrick(): void
    {
        $loader = new LoadBricks();
        $bricks = $loader->loadInstalledBricks();

        self::assertCount(1, $bricks);
        $brick = $bricks[0];
        self::assertSame('brick1', $brick->package_name);
        self::assertSame(__DIR__ . '/../Fixtures/brick1', $brick->package_path);
        self::assertCount(1, $brick->services);
        $service = $brick->services[0];
        self::assertSame(Service1::class, $service->reflection->name);
        self::assertSame('bar.yml', $service->service_attribute->configuration_filename);
        self::assertSame(Service1Configuration::class, $service->service_attribute->configuration_classname);
    }
}
