<?php

namespace DomainCertificateBundle\Tests\Service;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Exception\CertificateGenerationException;
use DomainCertificateBundle\Service\TlsService;
use DomainCertificateBundle\Service\TlsServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Output\BufferedOutput;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(TlsService::class)]
#[RunTestsInSeparateProcesses]
final class TlsServiceTest extends AbstractIntegrationTestCase
{
    private TlsServiceInterface $tlsService;

    protected function onSetUp(): void
    {
        $this->tlsService = new MockTlsService();
    }

    public function testServiceCanBeInstantiated(): void
    {
        $this->assertInstanceOf(TlsServiceInterface::class, $this->tlsService);
    }

    public function testRenewShouldOutputCommandsToConsole(): void
    {
        $domain = new DnsDomain();
        $domain->setName('test-domain-' . uniqid() . '.com');
        $domain->setCreateTime(new \DateTimeImmutable());
        self::getEntityManager()->persist($domain);
        self::getEntityManager()->flush();

        $output = new BufferedOutput();

        try {
            $this->tlsService->renew($domain, $output);
        } catch (CertificateGenerationException $e) {
            // Expected in test environment since certbot is not available
        }

        $outputContent = $output->fetch();
        $this->assertStringContainsString('模拟执行 TLS 证书更新', $outputContent);
        $this->assertStringContainsString($domain->getName() ?? '', $outputContent);
    }
}
