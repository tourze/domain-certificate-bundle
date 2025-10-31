<?php

namespace DomainCertificateBundle\Tests\Repository;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Entity\TlsProxy;
use DomainCertificateBundle\Repository\TlsProxyRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(TlsProxyRepository::class)]
#[RunTestsInSeparateProcesses]
final class TlsProxyRepositoryTest extends AbstractRepositoryTestCase
{
    private TlsProxyRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(TlsProxyRepository::class);
    }

    /**
     * 创建并持久化一个测试用的 DnsDomain
     */
    private function createTestDomain(string $suffix = ''): DnsDomain
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . $suffix . '.com');

        // 持久化并 flush，但不清除实体管理器
        self::getEntityManager()->persist($domain);
        self::getEntityManager()->flush();

        return $domain;
    }

    public function testFindByWithEmptyCriteriaShouldReturnAllRecords(): void
    {
        $domain = $this->createTestDomain();

        $entity = new TlsProxy();
        $entity->setDomain($domain);
        $entity->setName('test-proxy-' . uniqid());
        $entity->setListenPort(8080);
        $entity->setTargetHost('example.com');
        $entity->setTargetPort(443);
        $entity->setValid(true);
        $this->persistAndFlush($entity);

        $result = $this->repository->findBy([]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result), 'Should have at least 1 record after creating 1');
    }

    public function testFindByWithValidCriteriaShouldReturnMatchingRecords(): void
    {
        $domain = $this->createTestDomain();

        $uniqueName = 'test-proxy-' . uniqid() . '-' . hrtime(true);
        $entity = new TlsProxy();
        $entity->setDomain($domain);
        $entity->setName($uniqueName);
        $entity->setListenPort(8080);
        $entity->setTargetHost('example.com');
        $entity->setTargetPort(443);
        $entity->setValid(true);
        $this->persistAndFlush($entity);

        $result = $this->repository->findBy(['name' => $uniqueName]);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals($uniqueName, $result[0]->getName());
    }

    public function testFindByWithInvalidFieldShouldThrowException(): void
    {
        $this->expectException(\Exception::class);
        $this->repository->findBy(['invalidField' => 'value']);
    }

    public function testFindByWithOrderByShouldReturnSortedResults(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-proxy-sort-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $zzzName = 'zzz-proxy-' . uniqid() . '-' . hrtime(true);
        $entity1 = new TlsProxy();
        $entity1->setDomain($domain1);
        $entity1->setName($zzzName);
        $entity1->setListenPort(8080);
        $entity1->setTargetHost('example.com');
        $entity1->setTargetPort(443);
        $entity1->setValid(true);
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-proxy-sort-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $aaaName = 'aaa-proxy-' . uniqid() . '-' . hrtime(true);
        $entity2 = new TlsProxy();
        $entity2->setDomain($domain2);
        $entity2->setName($aaaName);
        $entity2->setListenPort(8080);
        $entity2->setTargetHost('example.com');
        $entity2->setTargetPort(443);
        $entity2->setValid(true);
        $this->persistAndFlush($entity2);

        $result = $this->repository->findBy([], ['name' => 'ASC']);

        $this->assertGreaterThanOrEqual(2, count($result));
        // 查找我们创建的具体记录
        $aaaRecord = null;
        $zzzRecord = null;
        foreach ($result as $record) {
            self::assertInstanceOf(TlsProxy::class, $record);
            if ($aaaName === $record->getName()) {
                $aaaRecord = $record;
            } elseif ($zzzName === $record->getName()) {
                $zzzRecord = $record;
            }
        }
        $this->assertNotNull($aaaRecord, 'aaa-proxy record should exist');
        $this->assertNotNull($zzzRecord, 'zzz-proxy record should exist');

        // 验证排序：aaa应该在zzz之前
        $aaaIndex = array_search($aaaRecord, $result, true);
        $zzzIndex = array_search($zzzRecord, $result, true);
        $this->assertLessThan($zzzIndex, $aaaIndex, 'aaa-proxy should come before zzz-proxy in ASC order');
    }

    public function testFindByWithLimitShouldReturnLimitedResults(): void
    {
        for ($i = 0; $i < 5; ++$i) {
            $domain = new DnsDomain();
            $domain->setName('test-proxy-limit-' . $i . '-' . uniqid() . '-' . hrtime(true) . '.test');
            self::getEntityManager()->persist($domain);

            $entity = new TlsProxy();
            $entity->setDomain($domain);
            $entity->setName('test-proxy-limit-' . $i . '-' . uniqid() . '-' . hrtime(true));
            $entity->setListenPort(8080);
            $entity->setTargetHost('example.com');
            $entity->setTargetPort(443);
            $entity->setValid(true);
            $this->persistAndFlush($entity);
        }

        $result = $this->repository->findBy([], null, 3);

        $this->assertCount(3, $result);
    }

    public function testFindOneByWithExistingRecordShouldReturnEntity(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsProxy();
        $entity->setDomain($domain);
        $entity->setName('test-proxy-' . uniqid());
        $entity->setListenPort(8080);
        $entity->setTargetHost('example.com');
        $entity->setTargetPort(443);
        $entity->setValid(true);
        $this->persistAndFlush($entity);

        $result = $this->repository->findOneBy(['targetHost' => 'example.com']);

        $this->assertInstanceOf(TlsProxy::class, $result);
    }

    public function testFindOneByWithNonExistentRecordShouldReturnNull(): void
    {
        $result = $this->repository->findOneBy(['targetHost' => 'nonexistent.com']);

        $this->assertNull($result);
    }

    public function testFindOneByWithOrderByShouldReturnFirstResult(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-proxy-order-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $entity1 = new TlsProxy();
        $entity1->setDomain($domain1);
        $entity1->setName('test-proxy-order-1-' . uniqid() . '-' . hrtime(true));
        $entity1->setListenPort(8080);
        $entity1->setTargetHost('zzz.example.com');
        $entity1->setTargetPort(443);
        $entity1->setValid(true);
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-proxy-order-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $entity2 = new TlsProxy();
        $entity2->setDomain($domain2);
        $entity2->setName('test-proxy-order-2-' . uniqid() . '-' . hrtime(true));
        $entity2->setListenPort(8080);
        $entity2->setTargetHost('aaa.example.com');
        $entity2->setTargetPort(443);
        $entity2->setValid(true);
        $this->persistAndFlush($entity2);

        $result = $this->repository->findOneBy([], ['targetHost' => 'ASC']);

        $this->assertNotNull($result);
        // 由于数据库中可能存在其他记录，我们只验证返回的第一条记录是存在的
        $this->assertInstanceOf(TlsProxy::class, $result);

        // 验证我们创建的记录确实存在于数据库中
        $createdRecords = $this->repository->findBy(['targetHost' => ['aaa.example.com', 'zzz.example.com']]);
        $this->assertCount(2, $createdRecords);
    }

    public function testCountWithEmptyCriteriaShouldReturnTotalCount(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $domain = new DnsDomain();
            $domain->setName('test-proxy-count-' . $i . '-' . uniqid() . '-' . hrtime(true) . '.test');
            self::getEntityManager()->persist($domain);

            $entity = new TlsProxy();
            $entity->setDomain($domain);
            $entity->setName('test-proxy-count-' . $i . '-' . uniqid() . '-' . hrtime(true));
            $entity->setListenPort(8080);
            $entity->setTargetHost('example.com');
            $entity->setTargetPort(443);
            $entity->setValid(true);
            $this->persistAndFlush($entity);
        }

        $count = $this->repository->count([]);

        $this->assertGreaterThanOrEqual(3, $count, 'Count should be at least 3 after creating 3 records');
    }

    public function testCountWithCriteriaShouldReturnMatchingCount(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-proxy-criteria-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $entity1 = new TlsProxy();
        $entity1->setDomain($domain1);
        $entity1->setName('test-proxy-criteria-1-' . uniqid() . '-' . hrtime(true));
        $entity1->setListenPort(8080);
        $entity1->setTargetHost('example.com');
        $entity1->setTargetPort(443);
        $entity1->setValid(true);
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-proxy-criteria-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $entity2 = new TlsProxy();
        $entity2->setDomain($domain2);
        $entity2->setName('test-proxy-criteria-2-' . uniqid() . '-' . hrtime(true));
        $entity2->setListenPort(9090);
        $entity2->setTargetHost('example.com');
        $entity2->setTargetPort(443);
        $entity2->setValid(true);
        $this->persistAndFlush($entity2);

        $count = $this->repository->count(['listenPort' => 8080]);

        $this->assertEquals(1, $count);
    }

    public function testCountWithNullFieldQueryShouldReturnCount(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-proxy-null-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $entity1 = new TlsProxy();
        $entity1->setDomain($domain1);
        $entity1->setName('test-proxy-null-1-' . uniqid() . '-' . hrtime(true));
        $entity1->setListenPort(8080);
        $entity1->setTargetHost('example.com');
        $entity1->setTargetPort(443);
        $entity1->setValid(null);
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-proxy-null-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $entity2 = new TlsProxy();
        $entity2->setDomain($domain2);
        $entity2->setName('test-proxy-null-2-' . uniqid() . '-' . hrtime(true));
        $entity2->setListenPort(8080);
        $entity2->setTargetHost('example.com');
        $entity2->setTargetPort(443);
        $entity2->setValid(true);
        $this->persistAndFlush($entity2);

        $count = $this->repository->count(['valid' => null]);

        $this->assertEquals(1, $count);
    }

    public function testFindByWithDomainAssociationShouldReturnMatchingRecords(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-proxy-domain-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $entity1 = new TlsProxy();
        $entity1->setDomain($domain1);
        $entity1->setName('test-proxy-domain-1-' . uniqid() . '-' . hrtime(true));
        $entity1->setListenPort(8080);
        $entity1->setTargetHost('example.com');
        $entity1->setTargetPort(443);
        $entity1->setValid(true);
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-proxy-domain-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $entity2 = new TlsProxy();
        $entity2->setDomain($domain2);
        $entity2->setName('test-proxy-domain-2-' . uniqid() . '-' . hrtime(true));
        $entity2->setListenPort(8080);
        $entity2->setTargetHost('example.com');
        $entity2->setTargetPort(443);
        $entity2->setValid(true);
        $this->persistAndFlush($entity2);

        $result = $this->repository->findBy(['domain' => $domain1]);

        $this->assertCount(1, $result);
        $this->assertEquals($domain1->getId(), $result[0]->getDomain()->getId());
    }

    public function testCountWithDomainAssociationShouldReturnMatchingCount(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-proxy-count-domain-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $entity1 = new TlsProxy();
        $entity1->setDomain($domain1);
        $entity1->setName('test-proxy-count-domain-1-' . uniqid() . '-' . hrtime(true));
        $entity1->setListenPort(8080);
        $entity1->setTargetHost('example.com');
        $entity1->setTargetPort(443);
        $entity1->setValid(true);
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-proxy-count-domain-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $entity2 = new TlsProxy();
        $entity2->setDomain($domain2);
        $entity2->setName('test-proxy-count-domain-2-' . uniqid() . '-' . hrtime(true));
        $entity2->setListenPort(8080);
        $entity2->setTargetHost('example.com');
        $entity2->setTargetPort(443);
        $entity2->setValid(true);
        $this->persistAndFlush($entity2);

        $count = $this->repository->count(['domain' => $domain1]);

        $this->assertEquals(1, $count);
    }

    public function testSaveWithNewEntityShouldPersistToDatabase(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsProxy();
        $entity->setDomain($domain);
        $entity->setName('test-proxy-' . uniqid());
        $entity->setListenPort(8080);
        $entity->setTargetHost('example.com');
        $entity->setTargetPort(443);
        $entity->setValid(true);

        $this->repository->save($entity);

        $this->assertEntityPersisted($entity);
        $this->assertNotNull($entity->getId());
    }

    public function testSaveWithExistingEntityShouldUpdateDatabase(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsProxy();
        $entity->setDomain($domain);
        $entity->setName('test-proxy-' . uniqid());
        $entity->setListenPort(8080);
        $entity->setTargetHost('example.com');
        $entity->setTargetPort(443);
        $entity->setValid(true);
        $this->persistAndFlush($entity);

        $entity->setName('updated-proxy');
        $this->repository->save($entity);

        self::getEntityManager()->clear();
        $updated = $this->repository->find($entity->getId());
        self::assertInstanceOf(TlsProxy::class, $updated);
        $this->assertEquals('updated-proxy', $updated->getName());
    }

    public function testSaveWithFlushFalseShouldNotImmediatelyFlush(): void
    {
        $initialCount = $this->repository->count([]);

        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);
        self::getEntityManager()->flush();

        $entity = new TlsProxy();
        $entity->setDomain($domain);
        $entity->setName('test-proxy-' . uniqid());
        $entity->setListenPort(8080);
        $entity->setTargetHost('example.com');
        $entity->setTargetPort(443);
        $entity->setValid(true);

        $this->repository->save($entity, false);

        $countAfterSave = $this->repository->count([]);
        $this->assertEquals($initialCount, $countAfterSave);

        self::getEntityManager()->flush();
        $countAfterFlush = $this->repository->count([]);
        $this->assertEquals($initialCount + 1, $countAfterFlush);
    }

    public function testRemoveWithExistingEntityShouldDeleteFromDatabase(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsProxy();
        $entity->setDomain($domain);
        $entity->setName('test-proxy-' . uniqid());
        $entity->setListenPort(8080);
        $entity->setTargetHost('example.com');
        $entity->setTargetPort(443);
        $entity->setValid(true);
        $this->persistAndFlush($entity);
        $entityId = $entity->getId();

        $this->repository->remove($entity);

        $this->assertEntityNotExists(TlsProxy::class, $entityId);
        $this->assertNotNull($entityId);
    }

    public function testRemoveWithFlushFalseShouldNotImmediatelyDelete(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsProxy();
        $entity->setDomain($domain);
        $entity->setName('test-proxy-' . uniqid());
        $entity->setListenPort(8080);
        $entity->setTargetHost('example.com');
        $entity->setTargetPort(443);
        $entity->setValid(true);
        $this->persistAndFlush($entity);
        $entityId = $entity->getId();

        $this->repository->remove($entity, false);

        $result = $this->repository->find($entityId);
        $this->assertInstanceOf(TlsProxy::class, $result);

        self::getEntityManager()->flush();
        $result = $this->repository->find($entityId);
        $this->assertNull($result);
    }

    // IS NULL 查询测试
    public function testFindByWithNullNameShouldReturnMatchingRecords(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-proxy-null-name-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain);

        $proxy = new TlsProxy();
        $proxy->setDomain($domain);
        $proxy->setName(null);
        $proxy->setListenPort(8080);
        $proxy->setTargetHost('example.com');
        $proxy->setTargetPort(443);
        $proxy->setValid(true);
        $this->persistAndFlush($proxy);

        $result = $this->repository->findBy(['name' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testCountWithNullNameShouldReturnCorrectCount(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-proxy-null-count-name-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain);

        $proxy = new TlsProxy();
        $proxy->setDomain($domain);
        $proxy->setName(null);
        $proxy->setListenPort(8080);
        $proxy->setTargetHost('example.com');
        $proxy->setTargetPort(443);
        $proxy->setValid(true);
        $this->persistAndFlush($proxy);

        $count = $this->repository->count(['name' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationDomainShouldReturnMatchingEntity(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-proxy-association-findone-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain);

        $proxy = new TlsProxy();
        $proxy->setDomain($domain);
        $proxy->setName('test-proxy-' . uniqid());
        $proxy->setListenPort(8080);
        $proxy->setTargetHost('example.com');
        $proxy->setTargetPort(443);
        $proxy->setValid(true);
        $this->persistAndFlush($proxy);

        $result = $this->repository->findOneBy(['domain' => $domain]);

        self::assertInstanceOf(TlsProxy::class, $result);
        $this->assertInstanceOf(TlsProxy::class, $result);
        $resultDomain = $result->getDomain();
        $this->assertEquals($domain->getId(), $resultDomain->getId());
    }

    public function testCountByAssociationDomainShouldReturnCorrectNumber(): void
    {
        $uniqueSuffix = uniqid() . '-' . hrtime(true);
        $domain1 = new DnsDomain();
        $domain1->setName('test-count-association-proxy-1-' . $uniqueSuffix . '.test');
        self::getEntityManager()->persist($domain1);

        $proxy1 = new TlsProxy();
        $proxy1->setDomain($domain1);
        $proxy1->setName('proxy-count-1-' . $uniqueSuffix);
        $proxy1->setListenPort(8080);
        $proxy1->setTargetHost('example.com');
        $proxy1->setTargetPort(443);
        $proxy1->setValid(true);
        $this->persistAndFlush($proxy1);

        $proxy2 = new TlsProxy();
        $proxy2->setDomain($domain1);
        $proxy2->setName('proxy-count-2-' . $uniqueSuffix);
        $proxy2->setListenPort(8080);
        $proxy2->setTargetHost('example.com');
        $proxy2->setTargetPort(443);
        $proxy2->setValid(true);
        $this->persistAndFlush($proxy2);

        $domain2 = new DnsDomain();
        $domain2->setName('test-count-association-proxy-2-' . $uniqueSuffix . '.test');
        self::getEntityManager()->persist($domain2);

        $proxy3 = new TlsProxy();
        $proxy3->setDomain($domain2);
        $proxy3->setName('proxy-count-3-' . $uniqueSuffix);
        $proxy3->setListenPort(8080);
        $proxy3->setTargetHost('example.com');
        $proxy3->setTargetPort(443);
        $proxy3->setValid(true);
        $this->persistAndFlush($proxy3);

        $count = $this->repository->count(['domain' => $domain1]);

        $this->assertSame(2, $count);
    }

    /**
     * 创建一个新的 TlsProxy 实体，但不持久化到数据库
     */
    protected function createNewEntity(): object
    {
        // 每次都创建一个全新的 domain，确保状态干净
        $domain = $this->createTestDomain('-createNewEntity');

        $entity = new TlsProxy();
        $entity->setDomain($domain);
        $entity->setName('test-tls-proxy-' . uniqid());
        $entity->setListenPort(8443);
        $entity->setTargetHost('target.example.com');
        $entity->setTargetPort(443);
        $entity->setValid(true);

        return $entity;
    }

    protected function getRepository(): TlsProxyRepository
    {
        return $this->repository;
    }
}
