<?php

namespace DomainCertificateBundle\Tests\Exception;

use DomainCertificateBundle\Exception\CertificateGenerationException;
use DomainCertificateBundle\Exception\DomainCertificateException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(CertificateGenerationException::class)]
final class CertificateGenerationExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return CertificateGenerationException::class;
    }

    protected function getParentExceptionClass(): string
    {
        return DomainCertificateException::class;
    }
}
