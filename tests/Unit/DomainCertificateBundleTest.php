<?php

namespace DomainCertificateBundle\Tests\Unit;

use DomainCertificateBundle\DomainCertificateBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DomainCertificateBundleTest extends TestCase
{
    public function testBundleCanBeInstantiated(): void
    {
        $bundle = new DomainCertificateBundle();
        
        $this->assertInstanceOf(DomainCertificateBundle::class, $bundle);
    }

    public function testBundleBuild(): void
    {
        $bundle = new DomainCertificateBundle();
        $container = new ContainerBuilder();
        
        $bundle->build($container);
        
        $this->assertInstanceOf(ContainerBuilder::class, $container);
    }
}