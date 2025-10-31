<?php

declare(strict_types=1);

namespace DomainCertificateBundle\Controller\Admin;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Entity\TlsCertificate;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

#[AdminCrud(
    routePath: '/domain-certificate/tls-certificate',
    routeName: 'domain_certificate_tls_certificate'
)]
final class TlsCertificateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TlsCertificate::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('TLS证书')
            ->setEntityLabelInPlural('TLS证书管理')
            ->setPageTitle(Crud::PAGE_INDEX, 'TLS证书列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建TLS证书')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑TLS证书')
            ->setPageTitle(Crud::PAGE_DETAIL, 'TLS证书详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['domain.name', 'tlsCertPath', 'tlsKeyPath'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield AssociationField::new('domain', '域名')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value) {
                return $value instanceof DnsDomain ? $value->getName() : '';
            })
        ;

        yield TextField::new('tlsCertPath', '证书路径')
            ->setHelp('TLS证书文件的存储路径')
            ->hideOnIndex()
        ;

        yield TextField::new('tlsKeyPath', '私钥路径')
            ->setHelp('TLS私钥文件的存储路径')
            ->hideOnIndex()
        ;

        yield TextField::new('tlsFullchainPath', '完整证书链路径')
            ->setHelp('包含完整证书链的文件路径')
            ->hideOnIndex()
        ;

        yield TextField::new('tlsChainPath', '证书链路径')
            ->setHelp('证书链文件的存储路径')
            ->hideOnIndex()
        ;

        yield DateTimeField::new('tlsExpireTime', '过期时间')
            ->setHelp('TLS证书的过期时间')
        ;

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;

        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('domain', '域名'))
            ->add(DateTimeFilter::new('tlsExpireTime', '过期时间'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
