<?php

namespace DomainCertificateBundle\Service;

use CloudflareDnsBundle\Entity\DnsDomain;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TlsService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    private function execCommand(string $command, OutputInterface $output): void
    {
        $output->writeln("执行命令: {$command}");
        exec($command);
    }

    public function renew(DnsDomain $domain, OutputInterface $output): void
    {
        // 安装依赖
        $this->execCommand('apt-get update', $output);
        $this->execCommand('apt-get install certbot python3-certbot-dns-cloudflare', $output);

        $storagePath = "/data/cloudflare/tls-cert/{$domain->getId()}";
        @mkdir($storagePath, 0o777, true);

        // 创建一个文件来保存 Cloudflare 的凭证
        $iniFile = "{$storagePath}/cloudflare.ini";
        file_put_contents($iniFile, "dns_cloudflare_email = {$domain->getIamKey()->getAccessKey()}
dns_cloudflare_api_key = {$domain->getIamKey()->getSecretKey()}");
        $this->execCommand("chmod 600 {$iniFile}", $output);

        // 让我们加密证书自动签发
        $certPath = "/etc/letsencrypt/live/{$domain->getName()}/cert.pem";
        $keyPath = "/etc/letsencrypt/live/{$domain->getName()}/privkey.pem";
        $fullchainPath = "/etc/letsencrypt/live/{$domain->getName()}/fullchain.pem";
        $chainPath = "/etc/letsencrypt/live/{$domain->getName()}/chain.pem";
        $command = <<<EOT
            certbot certonly \
              --dns-cloudflare \
              --dns-cloudflare-credentials {$iniFile} \
              --dns-cloudflare-propagation-seconds 60 \
              -d "*.{$domain->getName()}" \
              -d {$domain->getName()} \
              --agree-tos \
              --non-interactive \
              --email {$domain->getIamKey()->getAccessKey()}
            EOT;
        $this->execCommand($command, $output);

        if (!is_file($certPath)) {
            throw new \Exception('找不到证书信息，申请失败');
        }

        $domain->setTlsCertPath($certPath);
        $domain->setTlsKeyPath($keyPath);
        $domain->setTlsFullchainPath($fullchainPath);
        $domain->setTlsChainPath($chainPath);

        exec("openssl x509 -noout -dates -in {$certPath}", $res, $result_code);
        $res = implode("\n", $res);
        // notBefore=May  9 03:20:30 2024 GMT
        // notAfter=Aug  7 03:20:29 2024 GMT
        preg_match('@notAfter=(.*?) GMT@', $res, $match);
        if ($match) {
            $expireTime = "$match[1] GMT";
            $output->writeln('过期时间 => ' . $expireTime);
            $expireTime = new \DateTimeImmutable($expireTime);
            $output->writeln('过期时间 => ' . $expireTime->format('Y-m-d H:i:s'));
            $domain->setTlsExpireTime($expireTime);
        }

        $this->entityManager->persist($domain);
        $this->entityManager->flush();
    }
}
