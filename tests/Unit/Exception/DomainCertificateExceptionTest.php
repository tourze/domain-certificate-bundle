<?php

namespace DomainCertificateBundle\Tests\Unit\Exception;

use DomainCertificateBundle\Exception\DomainCertificateException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DomainCertificateExceptionTest extends TestCase
{
    public function testExceptionExtendsRuntimeException(): void
    {
        $exception = new DomainCertificateException();
        
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Test exception message';
        $exception = new DomainCertificateException($message);
        
        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Test exception message';
        $code = 123;
        $exception = new DomainCertificateException($message, $code);
        
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }
}