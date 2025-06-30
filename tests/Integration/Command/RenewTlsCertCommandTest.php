<?php

namespace DomainCertificateBundle\Tests\Integration\Command;

use CloudflareDnsBundle\Entity\DnsDomain;
use CloudflareDnsBundle\Repository\DnsDomainRepository;
use DomainCertificateBundle\Command\RenewTlsCertCommand;
use DomainCertificateBundle\Exception\DomainNotFoundException;
use DomainCertificateBundle\Exception\InvalidDomainIdException;
use DomainCertificateBundle\Service\TlsService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RenewTlsCertCommandTest extends TestCase
{
    private RenewTlsCertCommand $command;
    private DnsDomainRepository $domainRepository;
    private TlsService $tlsService;

    protected function setUp(): void
    {
        $this->domainRepository = $this->createMock(DnsDomainRepository::class);
        $this->tlsService = $this->createMock(TlsService::class);
        
        $this->command = new RenewTlsCertCommand(
            $this->domainRepository,
            $this->tlsService
        );
    }

    public function testCommandExtendsCommand(): void
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testCommandHasCorrectName(): void
    {
        $this->assertSame('cloudflare:renew-tls-cert', RenewTlsCertCommand::NAME);
    }

    public function testExecuteThrowsInvalidDomainIdExceptionWhenDomainIdIsNull(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->method('getArgument')->with('domainId')->willReturn(null);

        $this->expectException(InvalidDomainIdException::class);
        $this->expectExceptionMessage('请提供域名ID');

        $reflection = new \ReflectionMethod($this->command, 'execute');
        $reflection->setAccessible(true);
        $reflection->invoke($this->command, $input, $output);
    }

    public function testExecuteThrowsInvalidDomainIdExceptionWhenDomainIdIsEmpty(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->method('getArgument')->with('domainId')->willReturn('');

        $this->expectException(InvalidDomainIdException::class);
        $this->expectExceptionMessage('请提供域名ID');

        $reflection = new \ReflectionMethod($this->command, 'execute');
        $reflection->setAccessible(true);
        $reflection->invoke($this->command, $input, $output);
    }

    public function testExecuteThrowsDomainNotFoundExceptionWhenDomainNotFound(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->method('getArgument')->with('domainId')->willReturn('123');
        $this->domainRepository->method('find')->with('123')->willReturn(null);

        $this->expectException(DomainNotFoundException::class);
        $this->expectExceptionMessage('找不到域名信息');

        $reflection = new \ReflectionMethod($this->command, 'execute');
        $reflection->setAccessible(true);
        $reflection->invoke($this->command, $input, $output);
    }

    public function testExecuteCallsTlsServiceRenewWhenDomainExists(): void
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $domain = $this->createMock(DnsDomain::class);

        $input->method('getArgument')->with('domainId')->willReturn('123');
        $this->domainRepository->method('find')->with('123')->willReturn($domain);

        $this->tlsService
            ->expects($this->once())
            ->method('renew')
            ->with($domain, $output);

        $reflection = new \ReflectionMethod($this->command, 'execute');
        $reflection->setAccessible(true);
        $result = $reflection->invoke($this->command, $input, $output);

        $this->assertSame(Command::SUCCESS, $result);
    }
}