<?php

namespace DomainCertificateBundle\Tests\Command;

use DomainCertificateBundle\Command\StartTlsProxyServerCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(StartTlsProxyServerCommand::class)]
#[RunTestsInSeparateProcesses]
final class StartTlsProxyServerCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getContainer()->get(StartTlsProxyServerCommand::class);
        self::assertInstanceOf(Command::class, $command);

        return new CommandTester($command);
    }

    public function testCommandExtendsCommand(): void
    {
        $reflection = new \ReflectionClass(StartTlsProxyServerCommand::class);
        $parent = $reflection->getParentClass();

        $this->assertNotFalse($parent);
        $this->assertSame(Command::class, $parent->getName());
    }

    public function testCommandCanBeInstantiated(): void
    {
        $command = self::getContainer()->get(StartTlsProxyServerCommand::class);
        $this->assertInstanceOf(StartTlsProxyServerCommand::class, $command);
    }

    public function testCommandTesterCanBeCreated(): void
    {
        $command = self::getContainer()->get(StartTlsProxyServerCommand::class);
        self::assertInstanceOf(Command::class, $command);
        $commandTester = new CommandTester($command);

        $this->assertInstanceOf(CommandTester::class, $commandTester);
    }

    public function testArgumentType(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试 type 参数是必需的
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "type")');

        $commandTester->execute([]);
    }

    public function testOptionDaemon(): void
    {
        // 测试 daemon 选项存在且可用，因为这是一个不会实际运行的简单测试
        $commandTester = $this->getCommandTester();

        // 测试使用 daemon 选项，提供 required type 参数但不实际运行Workerman
        $command = self::getContainer()->get(StartTlsProxyServerCommand::class);
        self::assertInstanceOf(Command::class, $command);
        $definition = $command->getDefinition();

        $this->assertTrue($definition->hasOption('daemon'));
        $this->assertSame('d', $definition->getOption('daemon')->getShortcut());
    }
}
