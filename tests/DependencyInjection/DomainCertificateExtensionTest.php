<?php

namespace DomainCertificateBundle\Tests\DependencyInjection;

use DomainCertificateBundle\DependencyInjection\DomainCertificateExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(DomainCertificateExtension::class)]
final class DomainCertificateExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
