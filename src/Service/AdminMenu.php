<?php

declare(strict_types=1);

namespace DomainCertificateBundle\Service;

use DomainCertificateBundle\Entity\TlsCertificate;
use DomainCertificateBundle\Entity\TlsProxy;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('安全管理')) {
            $item->addChild('安全管理');
        }

        $securityMenu = $item->getChild('安全管理');

        // 添加空值检查，确保安全菜单存在后再添加子菜单
        if (null === $securityMenu) {
            return;
        }

        $securityMenu
            ->addChild('TLS证书管理')
            ->setUri($this->linkGenerator->getCurdListPage(TlsCertificate::class))
            ->setAttribute('icon', 'fas fa-certificate')
        ;

        $securityMenu
            ->addChild('TLS代理管理')
            ->setUri($this->linkGenerator->getCurdListPage(TlsProxy::class))
            ->setAttribute('icon', 'fas fa-network-wired')
        ;
    }
}
