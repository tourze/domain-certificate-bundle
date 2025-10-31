<?php

declare(strict_types=1);

namespace DomainCertificateBundle\Tests\Service;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Exception\CertificateGenerationException;
use DomainCertificateBundle\Service\TlsServiceInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 用于测试的模拟 TlsService，不执行真实命令
 */
class MockTlsService implements TlsServiceInterface
{
    public function renew(DnsDomain $domain, OutputInterface $output): void
    {
        $output->writeln("模拟执行 TLS 证书更新，域名: {$domain->getName()}");

        // 模拟证书生成失败的场景
        throw new CertificateGenerationException('找不到证书信息，申请失败');
    }
}
