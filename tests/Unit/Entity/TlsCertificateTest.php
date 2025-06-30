<?php

namespace DomainCertificateBundle\Tests\Unit\Entity;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Entity\TlsCertificate;
use PHPUnit\Framework\TestCase;

class TlsCertificateTest extends TestCase
{
    private TlsCertificate $certificate;

    protected function setUp(): void
    {
        $this->certificate = new TlsCertificate();
    }

    public function testGettersAndSetters(): void
    {
        $certPath = '/path/to/cert.pem';
        $keyPath = '/path/to/key.pem';
        $fullchainPath = '/path/to/fullchain.pem';
        $chainPath = '/path/to/chain.pem';
        $expireTime = new \DateTimeImmutable('2025-12-31 23:59:59');

        $this->certificate->setTlsCertPath($certPath);
        $this->certificate->setTlsKeyPath($keyPath);
        $this->certificate->setTlsFullchainPath($fullchainPath);
        $this->certificate->setTlsChainPath($chainPath);
        $this->certificate->setTlsExpireTime($expireTime);

        $this->assertSame($certPath, $this->certificate->getTlsCertPath());
        $this->assertSame($keyPath, $this->certificate->getTlsKeyPath());
        $this->assertSame($fullchainPath, $this->certificate->getTlsFullchainPath());
        $this->assertSame($chainPath, $this->certificate->getTlsChainPath());
        $this->assertSame($expireTime, $this->certificate->getTlsExpireTime());
    }

    public function testDomainRelation(): void
    {
        $domain = $this->createMock(DnsDomain::class);
        $domain->method('getName')->willReturn('example.com');

        $this->certificate->setDomain($domain);

        $this->assertSame($domain, $this->certificate->getDomain());
    }

    public function testToStringWithoutId(): void
    {
        $result = $this->certificate->__toString();

        $this->assertSame('', $result);
    }

    public function testFluentInterface(): void
    {
        $domain = $this->createMock(DnsDomain::class);
        
        $result = $this->certificate
            ->setTlsCertPath('/cert.pem')
            ->setTlsKeyPath('/key.pem')
            ->setTlsFullchainPath('/fullchain.pem')
            ->setTlsChainPath('/chain.pem')
            ->setTlsExpireTime(new \DateTimeImmutable())
            ->setDomain($domain);

        $this->assertInstanceOf(TlsCertificate::class, $result);
        $this->assertSame($this->certificate, $result);
    }
}