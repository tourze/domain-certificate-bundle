<?php

namespace DomainCertificateBundle\Tests\Exception;

use DomainCertificateBundle\Exception\DomainCertificateException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(DomainCertificateException::class)]
final class DomainCertificateExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return DomainCertificateException::class;
    }

    protected function getParentExceptionClass(): string
    {
        return \RuntimeException::class;
    }
}
