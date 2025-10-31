<?php

namespace DomainCertificateBundle\Tests\Entity;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Entity\TlsCertificate;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(TlsCertificate::class)]
final class TlsCertificateTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new TlsCertificate();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'tlsCertPath' => ['tlsCertPath', '/path/to/cert.pem'];
        yield 'tlsKeyPath' => ['tlsKeyPath', '/path/to/key.pem'];
        yield 'tlsFullchainPath' => ['tlsFullchainPath', '/path/to/fullchain.pem'];
        yield 'tlsChainPath' => ['tlsChainPath', '/path/to/chain.pem'];
        yield 'tlsExpireTime' => ['tlsExpireTime', new \DateTimeImmutable('2025-12-31 23:59:59')];
    }

    public function testDomainRelation(): void
    {
        // 使用具体类 DnsDomain 的原因：
        // 1. 该类是 Doctrine 实体，包含复杂的数据模型逻辑
        // 2. 实体测试需要验证与真实实体的关系
        // 3. 实体类已经是数据模型层的抽象，不需要额外接口
        $domain = $this->createMock(DnsDomain::class);
        $domain->method('getName')->willReturn('example.com');

        $certificate = $this->createEntity();
        self::assertInstanceOf(TlsCertificate::class, $certificate);
        $certificate->setDomain($domain);

        $this->assertSame($domain, $certificate->getDomain());
    }

    public function testToStringWithoutId(): void
    {
        $certificate = $this->createEntity();
        self::assertInstanceOf(TlsCertificate::class, $certificate);
        $result = $certificate->__toString();

        $this->assertSame('', $result);
    }

    public function testFluentInterface(): void
    {
        // 使用具体类 DnsDomain 的原因：
        // 1. 该类是 Doctrine 实体，包含复杂的数据模型逻辑
        // 2. 实体测试需要验证与真实实体的关系
        // 3. 实体类已经是数据模型层的抽象，不需要额外接口
        $domain = $this->createMock(DnsDomain::class);

        $certificate = $this->createEntity();
        self::assertInstanceOf(TlsCertificate::class, $certificate);
        $certificate->setTlsCertPath('/cert.pem');
        $certificate->setTlsKeyPath('/key.pem');
        $certificate->setTlsFullchainPath('/fullchain.pem');
        $certificate->setTlsChainPath('/chain.pem');
        $certificate->setTlsExpireTime(new \DateTimeImmutable());
        $certificate->setDomain($domain);

        $this->assertInstanceOf(TlsCertificate::class, $certificate);
    }
}
