<?php

namespace DomainCertificateBundle\Tests\Integration\Repository;

use DomainCertificateBundle\Repository\TlsProxyRepository;
use PHPUnit\Framework\TestCase;

class TlsProxyRepositoryTest extends TestCase
{
    public function testRepositoryExtendsServiceEntityRepository(): void
    {
        $reflection = new \ReflectionClass(TlsProxyRepository::class);
        $parent = $reflection->getParentClass();
        
        $this->assertNotFalse($parent);
        $this->assertSame('Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository', $parent->getName());
    }

    public function testRepositoryHasCorrectEntityClass(): void
    {
        $reflection = new \ReflectionClass(TlsProxyRepository::class);
        $constructor = $reflection->getConstructor();
        
        $this->assertNotNull($constructor);
        
        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertSame('registry', $parameters[0]->getName());
    }
}