<?php

declare(strict_types=1);

namespace DomainCertificateBundle\Tests\Controller\Admin;

use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use DomainCertificateBundle\Controller\Admin\TlsCertificateCrudController;
use DomainCertificateBundle\Entity\TlsCertificate;
use DomainCertificateBundle\Tests\Controller\Admin\AbstractDomainCertificateControllerTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * @internal
 * @phpstan-ignore-next-line
 */
#[CoversClass(TlsCertificateCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TlsCertificateCrudControllerTest extends AbstractDomainCertificateControllerTestCase
{
    public function testEntityFqcn(): void
    {
        $controller = new TlsCertificateCrudController();
        $this->assertSame(TlsCertificate::class, $controller::getEntityFqcn());
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new TlsCertificateCrudController();
        $this->assertInstanceOf(TlsCertificateCrudController::class, $controller);
    }

    /**
     * 测试验证错误 - 提交空表单并验证错误信息
     */
    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrlWithDashboard('new'));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form();

        // 捕获预期的约束违规异常 - domain字段为必填且存在数据库约束
        $this->expectException(NotNullConstraintViolationException::class);
        $this->expectExceptionMessage('NOT NULL constraint failed: ims_tls_certificate.domain_id');

        $client->submit($form);
    }

    protected function getControllerService(): TlsCertificateCrudController
    {
        return new TlsCertificateCrudController();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield '域名字段' => ['domain'];
        yield '证书路径字段' => ['tlsCertPath'];
        yield '私钥路径字段' => ['tlsKeyPath'];
        yield '完整证书链路径字段' => ['tlsFullchainPath'];
        yield '证书链路径字段' => ['tlsChainPath'];
        yield '过期时间字段' => ['tlsExpireTime'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield '域名字段' => ['domain'];
        yield '证书路径字段' => ['tlsCertPath'];
        yield '私钥路径字段' => ['tlsKeyPath'];
        yield '完整证书链路径字段' => ['tlsFullchainPath'];
        yield '证书链路径字段' => ['tlsChainPath'];
        yield '过期时间字段' => ['tlsExpireTime'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID' => ['ID'],
            '域名' => ['域名'],
            '过期时间' => ['过期时间'],
            '创建时间' => ['创建时间'],
        ];
    }
}
