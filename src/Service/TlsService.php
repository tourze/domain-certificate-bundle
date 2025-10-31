<?php

namespace DomainCertificateBundle\Service;

use CloudflareDnsBundle\Entity\DnsDomain;
use Doctrine\ORM\EntityManagerInterface;
use DomainCertificateBundle\Entity\TlsCertificate;
use DomainCertificateBundle\Exception\CertificateGenerationException;
use DomainCertificateBundle\Repository\TlsCertificateRepository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TlsService implements TlsServiceInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TlsCertificateRepository $certificateRepository,
    ) {
    }

    protected function execCommand(string $command, OutputInterface $output): void
    {
        $output->writeln("执行命令: {$command}");
        $process = Process::fromShellCommandline($command);
        $process->run();
    }

    public function renew(DnsDomain $domain, OutputInterface $output): void
    {
        // 安装依赖
        $this->execCommand('apt-get update', $output);
        $this->execCommand('apt-get install certbot python3-certbot-dns-cloudflare', $output);

        $storagePath = "/data/cloudflare/tls-cert/{$domain->getId()}";
        if (!is_dir($storagePath) && !@mkdir($storagePath, 0o777, true) && !is_dir($storagePath)) {
            throw new CertificateGenerationException("无法创建存储目录: {$storagePath}");
        }

        // 创建一个文件来保存 Cloudflare 的凭证
        $iniFile = "{$storagePath}/cloudflare.ini";
        $iamKey = $domain->getIamKey();
        if (null === $iamKey) {
            throw new CertificateGenerationException("域名 {$domain->getName()} 没有配置 IAM Key");
        }
        $iniContent = "dns_cloudflare_email = {$iamKey->getAccessKey()}
dns_cloudflare_api_key = {$iamKey->getSecretKey()}";
        if (false === @file_put_contents($iniFile, $iniContent)) {
            throw new CertificateGenerationException("无法创建凭证文件: {$iniFile}");
        }
        $this->execCommand("chmod 600 {$iniFile}", $output);

        // 让我们加密证书自动签发
        $certPath = "/etc/letsencrypt/live/{$domain->getName()}/cert.pem";
        $keyPath = "/etc/letsencrypt/live/{$domain->getName()}/privkey.pem";
        $fullchainPath = "/etc/letsencrypt/live/{$domain->getName()}/fullchain.pem";
        $chainPath = "/etc/letsencrypt/live/{$domain->getName()}/chain.pem";
        $command = <<<EOT
            certbot certonly \\
              --dns-cloudflare \\
              --dns-cloudflare-credentials {$iniFile} \\
              --dns-cloudflare-propagation-seconds 60 \\
              -d "*.{$domain->getName()}" \\
              -d {$domain->getName()} \\
              --agree-tos \\
              --non-interactive \\
              --email {$iamKey->getAccessKey()}
            EOT;
        $this->execCommand($command, $output);

        if (!is_file($certPath)) {
            throw new CertificateGenerationException('找不到证书信息，申请失败');
        }

        // 查找或创建 TlsCertificate 实体
        $certificate = $this->certificateRepository->findOneBy(['domain' => $domain]);
        if (null === $certificate) {
            $certificate = new TlsCertificate();
            $certificate->setDomain($domain);
        }

        $certificate->setTlsCertPath($certPath);
        $certificate->setTlsKeyPath($keyPath);
        $certificate->setTlsFullchainPath($fullchainPath);
        $certificate->setTlsChainPath($chainPath);

        $opensslProcess = Process::fromShellCommandline("openssl x509 -noout -dates -in {$certPath}");
        $opensslProcess->run();
        $res = $opensslProcess->getOutput();
        // notBefore=May  9 03:20:30 2024 GMT
        // notAfter=Aug  7 03:20:29 2024 GMT
        preg_match('@notAfter=(.*?) GMT@', $res, $match);
        if (count($match) > 1) {
            $expireTime = "{$match[1]} GMT";
            $output->writeln('过期时间 => ' . $expireTime);
            $expireTime = new \DateTimeImmutable($expireTime);
            $output->writeln('过期时间 => ' . $expireTime->format('Y-m-d H:i:s'));
            $certificate->setTlsExpireTime($expireTime);
        }

        $this->entityManager->persist($certificate);
        $this->entityManager->flush();
    }
}
