<?php

declare(strict_types=1);

/*
 * This file is part of York CS Negasaurus.
 *
 * (c) Graham Campbell
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace YorkCS\Tests\Negasaurus;

use GrahamCampbell\TestBenchCore\LaravelTrait;
use GrahamCampbell\TestBenchCore\ServiceProviderTrait;
use YorkCS\Negasaurus\AppServiceProvider;
use YorkCS\Negasaurus\Engine;
use YorkCS\Negasaurus\Generators\GeneratorInterface;
use YorkCS\Negasaurus\Validators\ValidatorInterface;

class ServiceProviderTest extends AbstractTestCase
{
    use LaravelTrait, ServiceProviderTrait;

    protected function getServiceProviderClass($app)
    {
        return AppServiceProvider::class;
    }

    public function testEngineIsInjectable()
    {
        $this->assertIsInjectable(Engine::class);
    }

    public function testGeneratorInterfaceIsInjectable()
    {
        $this->assertIsInjectable(GeneratorInterface::class);
    }

    public function testValidatorInterfaceIsInjectable()
    {
        $this->assertIsInjectable(ValidatorInterface::class);
    }
}
