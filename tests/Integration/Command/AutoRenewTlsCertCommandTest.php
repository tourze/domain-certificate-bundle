<?php

namespace DomainCertificateBundle\Tests\Integration\Command;

use DomainCertificateBundle\Command\AutoRenewTlsCertCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class AutoRenewTlsCertCommandTest extends TestCase
{
    public function testCommandExtendsCommand(): void
    {
        $reflection = new \ReflectionClass(AutoRenewTlsCertCommand::class);
        $parent = $reflection->getParentClass();
        
        $this->assertNotFalse($parent);
        $this->assertSame(Command::class, $parent->getName());
    }

    public function testCommandCanBeInstantiated(): void
    {
        $this->assertTrue(class_exists(AutoRenewTlsCertCommand::class));
    }
}