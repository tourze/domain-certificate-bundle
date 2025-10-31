<?php

declare(strict_types=1);

namespace DomainCertificateBundle\Controller\Admin;

use CloudflareDnsBundle\Entity\DnsDomain;
use DomainCertificateBundle\Entity\TlsProxy;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;

#[AdminCrud(
    routePath: '/domain-certificate/tls-proxy',
    routeName: 'domain_certificate_tls_proxy'
)]
final class TlsProxyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return TlsProxy::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('TLS代理')
            ->setEntityLabelInPlural('TLS代理管理')
            ->setPageTitle(Crud::PAGE_INDEX, 'TLS代理列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建TLS代理')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑TLS代理')
            ->setPageTitle(Crud::PAGE_DETAIL, 'TLS代理详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['name', 'domain.name', 'targetHost'])
            ->showEntityActionsInlined()
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield TextField::new('name', '代理名称')
            ->setHelp('TLS代理的名称标识，可选')
        ;

        yield AssociationField::new('domain', '域名')
            ->setRequired(true)
            ->autocomplete()
            ->formatValue(function ($value) {
                return $value instanceof DnsDomain ? $value->getName() : '';
            })
        ;

        yield IntegerField::new('listenPort', '监听端口')
            ->setRequired(true)
            ->setHelp('代理服务监听的端口号 (1-65535)')
        ;

        yield TextField::new('targetHost', '目标主机')
            ->setRequired(true)
            ->setHelp('代理转发的目标主机地址')
        ;

        yield IntegerField::new('targetPort', '目标端口')
            ->setRequired(true)
            ->setHelp('代理转发的目标端口号 (1-65535)')
        ;

        yield BooleanField::new('valid', '状态')
            ->setHelp('代理是否有效/启用')
            ->renderAsSwitch(false)
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
            ->add(BooleanFilter::new('valid', '状态'))
            ->add(NumericFilter::new('listenPort', '监听端口'))
            ->add(NumericFilter::new('targetPort', '目标端口'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
