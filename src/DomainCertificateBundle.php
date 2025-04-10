<?php

namespace DomainCertificateBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '域名证书模块')]
class DomainCertificateBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            \AntdCpBundle\AntdCpBundle::class => ['all' => true],
            \Tourze\Symfony\CronJob\CronJobBundle::class => ['all' => true],
            \CloudflareDnsBundle\CloudflareDnsBundle::class => ['all' => true],
        ];
    }
}
