<?php

declare(strict_types=1);

namespace DomainCertificateBundle\Tests\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitBase\TestCaseHelper;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * 域名证书包专用的测试基类，确保正确的Dashboard配置
 * @internal
 */
#[CoversClass(AbstractDomainCertificateControllerTestCase::class)]
#[RunTestsInSeparateProcesses]
abstract class AbstractDomainCertificateControllerTestCase extends AbstractEasyAdminControllerTestCase
{
    /**
     * 构建带有明确Dashboard配置的 EasyAdmin URL
     *
     * @param string $action CRUD 操作
     * @param array<string, mixed> $parameters 额外参数
     */
    protected function generateAdminUrlWithDashboard(string $action, array $parameters = []): string
    {
        $reflection = new \ReflectionClass($this);
        $controllerClass = TestCaseHelper::extractCoverClass($reflection);

        if (null === $controllerClass) {
            throw new \LogicException('Test class must declare a CoversClass attribute');
        }

        /** @var AdminUrlGenerator $generator */
        $generator = clone self::getService(AdminUrlGenerator::class);

        return $generator
            ->unsetAll()
            ->setDashboard('SymfonyTestingFramework\Controller\Admin\DashboardController')
            ->setController($controllerClass)
            ->setAction($action)
            ->setAll($parameters)
            ->generateUrl()
        ;
    }
}
