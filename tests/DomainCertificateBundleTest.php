<?php

declare(strict_types=1);

namespace DomainCertificateBundle\Tests;

use DomainCertificateBundle\DomainCertificateBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(DomainCertificateBundle::class)]
#[RunTestsInSeparateProcesses]
final class DomainCertificateBundleTest extends AbstractBundleTestCase
{
}
