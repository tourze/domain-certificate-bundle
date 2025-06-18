<?php

namespace DomainCertificateBundle\Entity;

use CloudflareDnsBundle\Entity\DnsDomain;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use DomainCertificateBundle\Repository\TlsProxyRepository;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: 'TLS代理')]
#[Creatable]
#[Editable]
#[Deletable]
#[ORM\Entity(repositoryClass: TlsProxyRepository::class)]
#[ORM\Table(name: 'ims_cloudflare_tls_proxy', options: ['comment' => 'TLS代理'])]
class TlsProxy implements \Stringable
{
    use TimestampableAware;
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[Keyword]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 128, unique: true, options: ['comment' => '名称'])]
    private ?string $name = null;

    #[ListColumn(title: '关联域名')]
    #[FormField(title: '关联域名')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private DnsDomain $domain;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(options: ['comment' => '监听端口'])]
    private ?int $listenPort = null;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 100, options: ['comment' => '目标HOST'])]
    private ?string $targetHost = null;

    #[ListColumn]
    #[FormField]
    #[ORM\Column(options: ['comment' => '目标端口'])]
    private ?int $targetPort = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

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
        return $this->getId() ? "{$this->getName()}" : '';
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
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
    }}
