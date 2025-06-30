<?php

namespace DomainCertificateBundle\Tests\Integration\Service;

use CloudflareDnsBundle\Entity\DnsDomain;
use CloudflareDnsBundle\Entity\IamKey;
use Doctrine\ORM\EntityManagerInterface;
use DomainCertificateBundle\Entity\TlsCertificate;
use DomainCertificateBundle\Exception\CertificateGenerationException;
use DomainCertificateBundle\Repository\TlsCertificateRepository;
use DomainCertificateBundle\Service\TlsService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class TlsServiceTest extends TestCase
{
    private TlsService $tlsService;
    private EntityManagerInterface $entityManager;
    private TlsCertificateRepository $certificateRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->certificateRepository = $this->createMock(TlsCertificateRepository::class);
        
        $this->tlsService = new TlsService(
            $this->entityManager,
            $this->certificateRepository
        );
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(TlsService::class, $this->tlsService);
    }

    public function testRenewMethodExists(): void
    {
        $reflection = new \ReflectionMethod($this->tlsService, 'renew');
        $this->assertTrue($reflection->isPublic());
        
        $parameters = $reflection->getParameters();
        $this->assertCount(2, $parameters);
        $this->assertSame('domain', $parameters[0]->getName());
        $this->assertSame('output', $parameters[1]->getName());
    }

    public function testRenewThrowsExceptionWhenCertificateGenerationFails(): void
    {
        $domain = $this->createMock(DnsDomain::class);
        $iamKey = $this->createMock(IamKey::class);
        $output = $this->createMock(OutputInterface::class);

        $domain->method('getName')->willReturn('test.example.com');
        $domain->method('getId')->willReturn(1);
        $domain->method('getIamKey')->willReturn($iamKey);
        $iamKey->method('getAccessKey')->willReturn('test@example.com');
        $iamKey->method('getSecretKey')->willReturn('secret-key');

        $this->certificateRepository
            ->method('findOneBy')
            ->willReturn(null);

        $this->expectException(CertificateGenerationException::class);
        $this->expectExceptionMessage('找不到证书信息，申请失败');

        $this->tlsService->renew($domain, $output);
    }
}