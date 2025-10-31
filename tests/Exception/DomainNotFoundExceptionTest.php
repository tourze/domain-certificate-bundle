<?php

namespace DomainCertificateBundle\Tests\Exception;

use DomainCertificateBundle\Exception\DomainCertificateException;
use DomainCertificateBundle\Exception\DomainNotFoundException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(DomainNotFoundException::class)]
final class DomainNotFoundExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return DomainNotFoundException::class;
    }

    protected function getParentExceptionClass(): string
    {
        return DomainCertificateException::class;
    }
}
