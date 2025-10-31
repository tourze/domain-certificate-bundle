<?php

namespace DomainCertificateBundle\Tests\Exception;

use DomainCertificateBundle\Exception\DomainCertificateException;
use DomainCertificateBundle\Exception\InvalidDomainIdException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(InvalidDomainIdException::class)]
final class InvalidDomainIdExceptionTest extends AbstractExceptionTestCase
{
    protected function getExceptionClass(): string
    {
        return InvalidDomainIdException::class;
    }

    protected function getParentExceptionClass(): string
    {
        return DomainCertificateException::class;
    }
}
