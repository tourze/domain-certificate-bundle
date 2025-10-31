<?php

declare(strict_types=1);

namespace DomainCertificateBundle\Tests\Controller\Admin;

use DomainCertificateBundle\Controller\Admin\TlsProxyCrudController;
use DomainCertificateBundle\Entity\TlsProxy;
use DomainCertificateBundle\Tests\Controller\Admin\AbstractDomainCertificateControllerTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * @internal
 */
#[CoversClass(TlsProxyCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TlsProxyCrudControllerTest extends AbstractDomainCertificateControllerTestCase
{
    public function testEntityFqcn(): void
    {
        $controller = new TlsProxyCrudController();
        $this->assertSame(TlsProxy::class, $controller::getEntityFqcn());
    }

    public function testControllerCanBeInstantiated(): void
    {
        $controller = new TlsProxyCrudController();
        $this->assertInstanceOf(TlsProxyCrudController::class, $controller);
    }

    public function testValidationErrors(): void
    {
        $client = $this->createAuthenticatedClient();
        $crawler = $client->request('GET', $this->generateAdminUrlWithDashboard('new'));
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form();
        $crawler = $client->submit($form);

        // 检查是否返回了验证错误状态或包含错误信息
        $response = $client->getResponse();
        $isError = $response->isServerError() || $response->isClientError() || 422 === $response->getStatusCode();
        $responseContent = $response->getContent();
        if (false === $responseContent) {
            $responseContent = '';
        }
        $hasValidationErrors = $crawler->filter('.invalid-feedback')->count() > 0
                              || $crawler->filter('.form-error')->count() > 0
                              || str_contains($responseContent, 'should not be blank')
                              || str_contains($responseContent, 'This value should not be blank');

        self::assertTrue($isError || $hasValidationErrors, 'Expected validation errors or error status');
    }

    protected function getControllerService(): TlsProxyCrudController
    {
        return new TlsProxyCrudController();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideIndexPageHeaders(): iterable
    {
        return [
            'ID' => ['ID'],
            '代理名称' => ['代理名称'],
            '域名' => ['域名'],
            '监听端口' => ['监听端口'],
            '目标主机' => ['目标主机'],
            '目标端口' => ['目标端口'],
            '状态' => ['状态'],
            '创建时间' => ['创建时间'],
        ];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideNewPageFields(): iterable
    {
        yield '代理名称字段' => ['name'];
        yield '域名字段' => ['domain'];
        yield '监听端口字段' => ['listenPort'];
        yield '目标主机字段' => ['targetHost'];
        yield '目标端口字段' => ['targetPort'];
        yield '状态字段' => ['valid'];
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEditPageFields(): iterable
    {
        yield '代理名称字段' => ['name'];
        yield '域名字段' => ['domain'];
        yield '监听端口字段' => ['listenPort'];
        yield '目标主机字段' => ['targetHost'];
        yield '目标端口字段' => ['targetPort'];
        yield '状态字段' => ['valid'];
    }
}
