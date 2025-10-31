<?php

namespace DomainCertificateBundle\Tests\Command;

use DomainCertificateBundle\Command\AutoRenewTlsCertCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(AutoRenewTlsCertCommand::class)]
#[RunTestsInSeparateProcesses]
final class AutoRenewTlsCertCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getContainer()->get(AutoRenewTlsCertCommand::class);
        self::assertInstanceOf(Command::class, $command);

        return new CommandTester($command);
    }

    public function testCommandExtendsCommand(): void
    {
        $reflection = new \ReflectionClass(AutoRenewTlsCertCommand::class);
        $parent = $reflection->getParentClass();

        $this->assertNotFalse($parent);
        $this->assertSame(Command::class, $parent->getName());
    }

    public function testCommandCanBeInstantiated(): void
    {
        $command = self::getContainer()->get(AutoRenewTlsCertCommand::class);
        $this->assertInstanceOf(AutoRenewTlsCertCommand::class, $command);
    }

    public function testCommandTesterCanBeCreated(): void
    {
        $command = self::getContainer()->get(AutoRenewTlsCertCommand::class);
        self::assertInstanceOf(Command::class, $command);
        $commandTester = new CommandTester($command);

        $this->assertInstanceOf(CommandTester::class, $commandTester);
    }
}
