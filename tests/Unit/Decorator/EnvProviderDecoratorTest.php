<?php

declare(strict_types=1);

namespace Lamoda\MultiEnvTests\Unit\Decorator;

use Lamoda\MultiEnv\Decorator\EnvProviderDecorator;
use Lamoda\MultiEnv\Decorator\Exception\EnvProviderDecoratorException;
use Lamoda\MultiEnv\Strategy\EnvResolvingStrategyInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EnvProviderDecoratorTest extends TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        EnvProviderDecorator::resetStrategy();
    }

    /**
     * @throws EnvProviderDecoratorException
     */
    public function testSuccessRun(): void
    {
        /** @var EnvResolvingStrategyInterface|MockObject $mock */
        $mock = $this->createMock(EnvResolvingStrategyInterface::class);
        $mock->expects(self::atLeastOnce())->method('getEnv');
        EnvProviderDecorator::init($mock);
        EnvProviderDecorator::getEnv('test');
    }

    /**
     * @throws EnvProviderDecoratorException
     */
    public function testExceptionRiseWhileRunGetEnvBeforeInit(): void
    {
        $expectedException = EnvProviderDecoratorException::becauseDecoratorNotInitialised();
        $this->expectException(get_class($expectedException));
        $this->expectExceptionMessage($expectedException->getMessage());
        EnvProviderDecorator::getEnv('test');
    }
}
