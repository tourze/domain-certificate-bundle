<?php

namespace DomainCertificateBundle\Entity;

use CloudflareDnsBundle\Entity\DnsDomain;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainCertificateBundle\Repository\TlsProxyRepository;
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
    private ?int $id = 0;

    #[ORM\Column(length: 128, unique: true, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private DnsDomain $domain;

    #[ORM\Column(options: ['comment' => '监听端口'])]
    private ?int $listenPort = null;

    #[ORM\Column(length: 100, options: ['comment' => '目标HOST'])]
    private ?string $targetHost = null;

    #[ORM\Column(options: ['comment' => '目标端口'])]
    private ?int $targetPort = null;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getId() !== null ? "{$this->getName()}" : '';
    }

    public function getDomain(): DnsDomain
    {
        return $this->domain;
    }

    public function setDomain(DnsDomain $domain): static
    {
        $this->domain = $domain;

        return $this;
    }

    public function getListenPort(): ?int
    {
        return $this->listenPort;
    }

    public function setListenPort(int $listenPort): static
    {
        $this->listenPort = $listenPort;

        return $this;
    }

    public function getTargetHost(): ?string
    {
        return $this->targetHost;
    }

    public function setTargetHost(string $targetHost): static
    {
        $this->targetHost = $targetHost;

        return $this;
    }

    public function getTargetPort(): ?int
    {
        return $this->targetPort;
    }

    public function setTargetPort(int $targetPort): static
    {
        $this->targetPort = $targetPort;

        return $this;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }}
