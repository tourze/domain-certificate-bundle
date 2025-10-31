<?php

namespace DomainCertificateBundle\Entity;

use CloudflareDnsBundle\Entity\DnsDomain;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainCertificateBundle\Repository\TlsProxyRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

#[ORM\Entity(repositoryClass: TlsProxyRepository::class)]
#[ORM\Table(name: 'ims_cloudflare_tls_proxy', options: ['comment' => 'TLS代理'])]
class TlsProxy implements \Stringable
{
    use TimestampableAware;
    use BlameableAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = null;

    #[ORM\Column(length: 128, unique: true, nullable: true, options: ['comment' => '名称'])]
    #[Assert\Length(max: 128)]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private DnsDomain $domain;

    #[ORM\Column(options: ['comment' => '监听端口'])]
    #[Assert\NotNull]
    #[Assert\Range(min: 1, max: 65535)]
    private ?int $listenPort = null;

    #[ORM\Column(length: 100, options: ['comment' => '目标HOST'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    private ?string $targetHost = null;

    #[ORM\Column(options: ['comment' => '目标端口'])]
    #[Assert\NotNull]
    #[Assert\Range(min: 1, max: 65535)]
    private ?int $targetPort = null;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[Assert\Type(type: 'bool')]
    private ?bool $valid = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

    public function getDomain(): DnsDomain
    {
        return $this->domain;
    }

    public function setDomain(DnsDomain $domain): void
    {
        $this->domain = $domain;
    }

    public function getListenPort(): ?int
    {
        return $this->listenPort;
    }

    public function setListenPort(int $listenPort): void
    {
        $this->listenPort = $listenPort;
    }

    public function getTargetHost(): ?string
    {
        return $this->targetHost;
    }

    public function setTargetHost(string $targetHost): void
    {
        $this->targetHost = $targetHost;
    }

    public function getTargetPort(): ?int
    {
        return $this->targetPort;
    }

    public function setTargetPort(int $targetPort): void
    {
        $this->targetPort = $targetPort;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }
}
