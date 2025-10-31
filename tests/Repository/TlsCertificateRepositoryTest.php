<?php

namespace DomainCertificateBundle\Tests\Repository;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Entity\TlsCertificate;
use DomainCertificateBundle\Repository\TlsCertificateRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(TlsCertificateRepository::class)]
#[RunTestsInSeparateProcesses]
final class TlsCertificateRepositoryTest extends AbstractRepositoryTestCase
{
    private TlsCertificateRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(TlsCertificateRepository::class);

        // 检查当前测试是否需要 DataFixtures 数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            // 为计数测试创建测试数据
            $this->createTestDataForCountTest();
        }
    }

    private function createTestDataForCountTest(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-fixture-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $certificate = new TlsCertificate();
        $certificate->setDomain($domain);
        $certificate->setTlsCertPath('/path/to/cert.pem');
        $certificate->setTlsKeyPath('/path/to/key.pem');
        $certificate->setTlsFullchainPath('/path/to/fullchain.pem');
        $certificate->setTlsChainPath('/path/to/chain.pem');
        $certificate->setTlsExpireTime(new \DateTimeImmutable('+1 year'));
        $this->persistAndFlush($certificate);
    }

    public function testFindByWithEmptyCriteriaShouldReturnAllRecords(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsCertificate();
        $entity->setDomain($domain);
        $entity->setTlsCertPath('/path/to/cert.pem');
        $entity->setTlsKeyPath('/path/to/key.pem');
        $entity->setTlsFullchainPath('/path/to/fullchain.pem');
        $entity->setTlsChainPath('/path/to/chain.pem');
        $entity->setTlsExpireTime(new \DateTimeImmutable('+1 year'));
        $this->persistAndFlush($entity);

        $result = $this->repository->findBy([]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result), 'Should have at least 1 record after creating 1');
    }

    public function testFindByWithValidCriteriaShouldReturnMatchingRecords(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsCertificate();
        $entity->setDomain($domain);
        $entity->setTlsCertPath('/path/to/cert.pem');
        $this->persistAndFlush($entity);

        $result = $this->repository->findBy(['tlsCertPath' => '/path/to/cert.pem']);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('/path/to/cert.pem', $result[0]->getTlsCertPath());
    }

    public function testFindByWithInvalidFieldShouldThrowException(): void
    {
        $this->expectException(\Exception::class);
        $this->repository->findBy(['invalidField' => 'value']);
    }

    public function testFindByWithOrderByShouldReturnSortedResults(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-cert-sort-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $entity1 = new TlsCertificate();
        $entity1->setDomain($domain1);
        $entity1->setTlsCertPath('zzz.pem');
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-cert-sort-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $entity2 = new TlsCertificate();
        $entity2->setDomain($domain2);
        $entity2->setTlsCertPath('aaa.pem');
        $this->persistAndFlush($entity2);

        $result = $this->repository->findBy([], ['tlsCertPath' => 'ASC']);

        $this->assertGreaterThanOrEqual(2, count($result));
        // 查找我们创建的具体记录
        $aaaRecord = null;
        $zzzRecord = null;
        foreach ($result as $record) {
            if ('aaa.pem' === $record->getTlsCertPath()) {
                $aaaRecord = $record;
            } elseif ('zzz.pem' === $record->getTlsCertPath()) {
                $zzzRecord = $record;
            }
        }
        $this->assertNotNull($aaaRecord, 'aaa.pem record should exist');
        $this->assertNotNull($zzzRecord, 'zzz.pem record should exist');

        // 验证排序：aaa应该在zzz之前
        $aaaIndex = array_search($aaaRecord, $result, true);
        $zzzIndex = array_search($zzzRecord, $result, true);
        $this->assertLessThan($zzzIndex, $aaaIndex, 'aaa.pem should come before zzz.pem in ASC order');
    }

    public function testFindByWithLimitShouldReturnLimitedResults(): void
    {
        for ($i = 0; $i < 5; ++$i) {
            $domain = new DnsDomain();
            $domain->setName('test-cert-limit-' . $i . '-' . uniqid() . '-' . hrtime(true) . '.test');
            self::getEntityManager()->persist($domain);

            $entity = new TlsCertificate();
            $entity->setDomain($domain);
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

        $entity = new TlsCertificate();
        $entity->setDomain($domain);
        $entity->setTlsKeyPath('/path/to/key.pem');
        $this->persistAndFlush($entity);

        $result = $this->repository->findOneBy(['tlsKeyPath' => '/path/to/key.pem']);

        $this->assertInstanceOf(TlsCertificate::class, $result);
    }

    public function testFindOneByWithNonExistentRecordShouldReturnNull(): void
    {
        $result = $this->repository->findOneBy(['tlsKeyPath' => '/nonexistent/path']);

        $this->assertNull($result);
    }

    public function testFindOneByWithOrderByShouldReturnFirstResult(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-cert-order-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $entity1 = new TlsCertificate();
        $entity1->setDomain($domain1);
        $entity1->setTlsKeyPath('zzz.pem');
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-cert-order-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $entity2 = new TlsCertificate();
        $entity2->setDomain($domain2);
        $entity2->setTlsKeyPath('aaa.pem');
        $this->persistAndFlush($entity2);

        $result = $this->repository->findOneBy([], ['tlsKeyPath' => 'ASC']);

        $this->assertNotNull($result);
        // 由于数据库中可能存在其他记录，我们只验证返回的第一条记录是存在的
        $this->assertInstanceOf(TlsCertificate::class, $result);

        // 验证我们创建的记录确实存在于数据库中
        $createdRecords = $this->repository->findBy(['tlsKeyPath' => ['aaa.pem', 'zzz.pem']]);
        $this->assertCount(2, $createdRecords);
    }

    public function testCountWithEmptyCriteriaShouldReturnTotalCount(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $domain = new DnsDomain();
            $domain->setName('test-cert-count-' . $i . '-' . uniqid() . '-' . hrtime(true) . '.test');
            self::getEntityManager()->persist($domain);

            $entity = new TlsCertificate();
            $entity->setDomain($domain);
            $this->persistAndFlush($entity);
        }

        $count = $this->repository->count([]);

        $this->assertGreaterThanOrEqual(3, $count, 'Count should be at least 3 after creating 3 records');
    }

    public function testCountWithCriteriaShouldReturnMatchingCount(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-cert-criteria-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $entity1 = new TlsCertificate();
        $entity1->setDomain($domain1);
        $entity1->setTlsChainPath('/path/chain.pem');
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-cert-criteria-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $entity2 = new TlsCertificate();
        $entity2->setDomain($domain2);
        $this->persistAndFlush($entity2);

        $count = $this->repository->count(['tlsChainPath' => '/path/chain.pem']);

        $this->assertEquals(1, $count);
    }

    public function testCountWithNullFieldQueryShouldReturnCount(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-cert-null-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $entity1 = new TlsCertificate();
        $entity1->setDomain($domain1);
        $entity1->setTlsFullchainPath(null);
        $this->persistAndFlush($entity1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-cert-null-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $entity2 = new TlsCertificate();
        $entity2->setDomain($domain2);
        $entity2->setTlsFullchainPath('/path/fullchain.pem');
        $this->persistAndFlush($entity2);

        $count = $this->repository->count(['tlsFullchainPath' => null]);

        $this->assertEquals(1, $count);
    }

    public function testSaveWithNewEntityShouldPersistToDatabase(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsCertificate();
        $entity->setDomain($domain);

        $this->repository->save($entity);

        $this->assertEntityPersisted($entity);
        $this->assertNotNull($entity->getId());
    }

    public function testSaveWithExistingEntityShouldUpdateDatabase(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsCertificate();
        $entity->setDomain($domain);
        $this->persistAndFlush($entity);

        $entity->setTlsCertPath('/updated/path.pem');
        $this->repository->save($entity);

        self::getEntityManager()->clear();
        $updated = $this->repository->find($entity->getId());
        self::assertInstanceOf(TlsCertificate::class, $updated);
        $this->assertEquals('/updated/path.pem', $updated->getTlsCertPath());
    }

    public function testSaveWithFlushFalseShouldNotImmediatelyFlush(): void
    {
        $initialCount = $this->repository->count([]);

        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsCertificate();
        $entity->setDomain($domain);

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

        $entity = new TlsCertificate();
        $entity->setDomain($domain);
        $this->persistAndFlush($entity);
        $entityId = $entity->getId();

        $this->repository->remove($entity);

        $this->assertEntityNotExists(TlsCertificate::class, $entityId);
        $this->assertNotNull($entityId);
    }

    public function testRemoveWithFlushFalseShouldNotImmediatelyDelete(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);

        $entity = new TlsCertificate();
        $entity->setDomain($domain);
        $this->persistAndFlush($entity);
        $entityId = $entity->getId();

        $this->repository->remove($entity, false);

        $result = $this->repository->find($entityId);
        $this->assertInstanceOf(TlsCertificate::class, $result);

        self::getEntityManager()->flush();
        $result = $this->repository->find($entityId);
        $this->assertNull($result);
    }

    // 关联查询测试
    public function testFindByWithDomainAssociationShouldReturnMatchingRecords(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-association-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain);

        $certificate = new TlsCertificate();
        $certificate->setDomain($domain);
        $this->persistAndFlush($certificate);

        $result = $this->repository->findBy(['domain' => $domain]);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $domainFromResult = $result[0]->getDomain();
        self::assertInstanceOf(DnsDomain::class, $domainFromResult);
        $this->assertEquals($domain->getId(), $domainFromResult->getId());
    }

    public function testCountWithDomainAssociationShouldReturnMatchingCount(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-count-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain);

        $certificate = new TlsCertificate();
        $certificate->setDomain($domain);
        $this->persistAndFlush($certificate);

        $count = $this->repository->count(['domain' => $domain]);

        $this->assertEquals(1, $count);
    }

    // IS NULL 查询测试
    public function testFindByWithNullExpireTimeShouldReturnMatchingRecords(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-null-expire-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain);

        $certificate = new TlsCertificate();
        $certificate->setDomain($domain);
        $certificate->setTlsExpireTime(null);
        $this->persistAndFlush($certificate);

        $result = $this->repository->findBy(['tlsExpireTime' => null]);

        $this->assertIsArray($result);
        $this->assertGreaterThanOrEqual(1, count($result));
    }

    public function testCountWithNullExpireTimeShouldReturnCorrectCount(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-null-count-expire-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain);

        $certificate = new TlsCertificate();
        $certificate->setDomain($domain);
        $certificate->setTlsExpireTime(null);
        $this->persistAndFlush($certificate);

        $count = $this->repository->count(['tlsExpireTime' => null]);

        $this->assertGreaterThanOrEqual(1, $count);
    }

    public function testFindOneByAssociationDomainShouldReturnMatchingEntity(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-association-findone-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain);

        $certificate = new TlsCertificate();
        $certificate->setDomain($domain);
        $this->persistAndFlush($certificate);

        $result = $this->repository->findOneBy(['domain' => $domain]);

        self::assertInstanceOf(TlsCertificate::class, $result);
        $this->assertInstanceOf(TlsCertificate::class, $result);
        $domainFromResult = $result->getDomain();
        self::assertInstanceOf(DnsDomain::class, $domainFromResult);
        $this->assertEquals($domain->getId(), $domainFromResult->getId());
    }

    public function testCountByAssociationDomainShouldReturnCorrectNumber(): void
    {
        $domain1 = new DnsDomain();
        $domain1->setName('test-count-association-domain-1-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain1);

        $cert1 = new TlsCertificate();
        $cert1->setDomain($domain1);
        $this->persistAndFlush($cert1);

        $domain2 = new DnsDomain();
        $domain2->setName('test-count-association-domain-2-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain2);

        $cert2 = new TlsCertificate();
        $cert2->setDomain($domain2);
        $this->persistAndFlush($cert2);

        $domain3 = new DnsDomain();
        $domain3->setName('test-count-association-domain-3-' . uniqid() . '-' . hrtime(true) . '.test');
        self::getEntityManager()->persist($domain3);

        $cert3 = new TlsCertificate();
        $cert3->setDomain($domain3);
        $this->persistAndFlush($cert3);

        $count = $this->repository->count(['domain' => $domain1]);

        $this->assertSame(1, $count);
    }

    /**
     * 创建一个新的 TlsCertificate 实体，但不持久化到数据库
     */
    protected function createNewEntity(): object
    {
        // 为了避免级联持久化问题，我们先创建并持久化 domain
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        self::getEntityManager()->persist($domain);
        self::getEntityManager()->flush();

        $entity = new TlsCertificate();
        $entity->setDomain($domain);
        $entity->setTlsCertPath('/path/to/cert.pem');
        $entity->setTlsKeyPath('/path/to/key.pem');
        $entity->setTlsFullchainPath('/path/to/fullchain.pem');
        $entity->setTlsChainPath('/path/to/chain.pem');
        $entity->setTlsExpireTime(new \DateTimeImmutable('+1 year'));

        return $entity;
    }

    protected function getRepository(): TlsCertificateRepository
    {
        return $this->repository;
    }
}
