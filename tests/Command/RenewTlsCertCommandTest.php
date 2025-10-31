<?php

namespace DomainCertificateBundle\Tests\Command;

use DomainCertificateBundle\Command\RenewTlsCertCommand;
use DomainCertificateBundle\Exception\InvalidDomainIdException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(RenewTlsCertCommand::class)]
#[RunTestsInSeparateProcesses]
final class RenewTlsCertCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试设置
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getContainer()->get(RenewTlsCertCommand::class);
        self::assertInstanceOf(Command::class, $command);

        return new CommandTester($command);
    }

    public function testCommandExtendsCommand(): void
    {
        $command = self::getContainer()->get(RenewTlsCertCommand::class);
        $this->assertInstanceOf(Command::class, $command);
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertSame('cloudflare:renew-tls-cert', RenewTlsCertCommand::NAME);
    }

    public function testCommandTesterCanBeCreated(): void
    {
        $command = self::getContainer()->get(RenewTlsCertCommand::class);
        self::assertInstanceOf(Command::class, $command);
        $commandTester = new CommandTester($command);

        $this->assertInstanceOf(CommandTester::class, $commandTester);
    }

    public function testArgumentDomainId(): void
    {
        $commandTester = $this->getCommandTester();

        // 测试缺少参数的情况
        $this->expectException(InvalidDomainIdException::class);
        $this->expectExceptionMessage('请提供域名ID');

        $commandTester->execute([]);
    }
}
