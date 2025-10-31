<?php

namespace DomainCertificateBundle\Entity;

use CloudflareDnsBundle\Entity\DnsDomain;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainCertificateBundle\Repository\TlsCertificateRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: TlsCertificateRepository::class)]
#[ORM\Table(name: 'ims_tls_certificate', options: ['comment' => 'TLS证书'])]
class TlsCertificate implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?DnsDomain $domain = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '证书路径'])]
    #[Assert\Length(max: 255)]
    private ?string $tlsCertPath = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '密钥路径'])]
    #[Assert\Length(max: 255)]
    private ?string $tlsKeyPath = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '完整证书链路径'])]
    #[Assert\Length(max: 255)]
    private ?string $tlsFullchainPath = null;

    #[ORM\Column(length: 255, nullable: true, options: ['comment' => '证书链路径'])]
    #[Assert\Length(max: 255)]
    private ?string $tlsChainPath = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '证书过期时间'])]
    #[Assert\Type(type: \DateTimeInterface::class)]
    private ?\DateTimeInterface $tlsExpireTime = null;

    public function getTlsCertPath(): ?string
    {
        return $this->tlsCertPath;
    }

    public function setTlsCertPath(?string $tlsCertPath): void
    {
        $this->tlsCertPath = $tlsCertPath;
    }

    public function getTlsKeyPath(): ?string
    {
        return $this->tlsKeyPath;
    }

    public function setTlsKeyPath(?string $tlsKeyPath): void
    {
        $this->tlsKeyPath = $tlsKeyPath;
    }

    public function getTlsFullchainPath(): ?string
    {
        return $this->tlsFullchainPath;
    }

    public function setTlsFullchainPath(?string $tlsFullchainPath): void
    {
        $this->tlsFullchainPath = $tlsFullchainPath;
    }

    public function getTlsChainPath(): ?string
    {
        return $this->tlsChainPath;
    }

    public function setTlsChainPath(?string $tlsChainPath): void
    {
        $this->tlsChainPath = $tlsChainPath;
    }

    public function getTlsExpireTime(): ?\DateTimeInterface
    {
        return $this->tlsExpireTime;
    }

    public function setTlsExpireTime(?\DateTimeInterface $tlsExpireTime): void
    {
        $this->tlsExpireTime = $tlsExpireTime;
    }

    public function __toString(): string
    {
        return null !== $this->getId()
            ? sprintf('TLS证书#%d (%s)', $this->getId(), $this->getDomain()?->getName() ?? 'N/A')
            : '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDomain(): ?DnsDomain
    {
        return $this->domain;
    }

    public function setDomain(DnsDomain $domain): void
    {
        $this->domain = $domain;
    }
}
