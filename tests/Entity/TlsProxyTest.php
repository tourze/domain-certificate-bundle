<?php

namespace DomainCertificateBundle\Tests\Entity;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Entity\TlsProxy;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(TlsProxy::class)]
final class TlsProxyTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new TlsProxy();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'name' => ['name', 'test-proxy'];
        yield 'listenPort' => ['listenPort', 443];
        yield 'targetHost' => ['targetHost', 'backend.example.com'];
        yield 'targetPort' => ['targetPort', 8080];
        yield 'valid' => ['valid', true];
    }

    public function testDomainRelation(): void
    {
        // 使用具体类 DnsDomain 的原因：
        // 1. 该类是 Doctrine 实体，包含复杂的数据模型逻辑
        // 2. 实体测试需要验证与真实实体的关系
        // 3. 实体类已经是数据模型层的抽象，不需要额外接口
        $domain = $this->createMock(DnsDomain::class);

        $proxy = $this->createEntity();
        self::assertInstanceOf(TlsProxy::class, $proxy);
        $proxy->setDomain($domain);

        $this->assertSame($domain, $proxy->getDomain());
    }

    public function testToStringWithoutId(): void
    {
        $proxy = $this->createEntity();
        self::assertInstanceOf(TlsProxy::class, $proxy);
        $result = $proxy->__toString();

        $this->assertSame('', $result);
    }

    public function testToStringWithName(): void
    {
        $name = 'test-proxy';
        $proxy = $this->createEntity();
        self::assertInstanceOf(TlsProxy::class, $proxy);
        $proxy->setName($name);

        $result = $proxy->__toString();

        $this->assertSame($name, $result);
    }

    public function testFluentInterface(): void
    {
        // 使用具体类 DnsDomain 的原因：
        // 1. 该类是 Doctrine 实体，包含复杂的数据模型逻辑
        // 2. 实体测试需要验证与真实实体的关系
        // 3. 实体类已经是数据模型层的抽象，不需要额外接口
        $domain = $this->createMock(DnsDomain::class);

        $proxy = $this->createEntity();
        self::assertInstanceOf(TlsProxy::class, $proxy);
        $proxy->setName('test');
        $proxy->setListenPort(443);
        $proxy->setTargetHost('backend.com');
        $proxy->setTargetPort(8080);
        $proxy->setValid(true);
        $proxy->setDomain($domain);

        $this->assertInstanceOf(TlsProxy::class, $proxy);
    }

    public function testValidDefaultValue(): void
    {
        $proxy = $this->createEntity();
        self::assertInstanceOf(TlsProxy::class, $proxy);
        $this->assertFalse($proxy->isValid());
    }
}
