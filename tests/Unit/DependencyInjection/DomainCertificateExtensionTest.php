<?php

namespace DomainCertificateBundle\Tests\Unit\DependencyInjection;

use DomainCertificateBundle\DependencyInjection\DomainCertificateExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DomainCertificateExtensionTest extends TestCase
{
    public function testExtensionCanBeInstantiated(): void
    {
        $extension = new DomainCertificateExtension();
        
        $this->assertInstanceOf(DomainCertificateExtension::class, $extension);
    }

    public function testExtensionLoad(): void
    {
        $extension = new DomainCertificateExtension();
        $container = new ContainerBuilder();
        
        $extension->load([], $container);
        
        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }
}