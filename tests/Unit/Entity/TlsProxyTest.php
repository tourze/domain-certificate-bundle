<?php

namespace DomainCertificateBundle\Tests\Unit\Entity;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Entity\TlsProxy;
use PHPUnit\Framework\TestCase;

class TlsProxyTest extends TestCase
{
    private TlsProxy $proxy;

    protected function setUp(): void
    {
        $this->proxy = new TlsProxy();
    }

    public function testGettersAndSetters(): void
    {
        $name = 'test-proxy';
        $listenPort = 443;
        $targetHost = 'backend.example.com';
        $targetPort = 8080;
        $valid = true;

        $this->proxy->setName($name);
        $this->proxy->setListenPort($listenPort);
        $this->proxy->setTargetHost($targetHost);
        $this->proxy->setTargetPort($targetPort);
        $this->proxy->setValid($valid);

        $this->assertSame($name, $this->proxy->getName());
        $this->assertSame($listenPort, $this->proxy->getListenPort());
        $this->assertSame($targetHost, $this->proxy->getTargetHost());
        $this->assertSame($targetPort, $this->proxy->getTargetPort());
        $this->assertTrue($this->proxy->isValid());
    }

    public function testDomainRelation(): void
    {
        $domain = $this->createMock(DnsDomain::class);
        
        $this->proxy->setDomain($domain);

        $this->assertSame($domain, $this->proxy->getDomain());
    }

    public function testToStringWithoutId(): void
    {
        $result = $this->proxy->__toString();

        $this->assertSame('', $result);
    }

    public function testToStringWithName(): void
    {
        $name = 'test-proxy';
        $this->proxy->setName($name);

        $result = $this->proxy->__toString();

        $this->assertSame($name, $result);
    }

    public function testFluentInterface(): void
    {
        $domain = $this->createMock(DnsDomain::class);
        
        $result = $this->proxy
            ->setName('test')
            ->setListenPort(443)
            ->setTargetHost('backend.com')
            ->setTargetPort(8080)
            ->setValid(true)
            ->setDomain($domain);

        $this->assertInstanceOf(TlsProxy::class, $result);
        $this->assertSame($this->proxy, $result);
    }

    public function testValidDefaultValue(): void
    {
        $this->assertFalse($this->proxy->isValid());
    }
}