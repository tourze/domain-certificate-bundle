<?php

namespace DomainCertificateBundle\Tests\Unit\Exception;

use DomainCertificateBundle\Exception\DomainCertificateException;
use DomainCertificateBundle\Exception\DomainNotFoundException;
use PHPUnit\Framework\TestCase;

class DomainNotFoundExceptionTest extends TestCase
{
    public function testExceptionExtendsDomainCertificateException(): void
    {
        $exception = new DomainNotFoundException();
        
        $this->assertInstanceOf(DomainCertificateException::class, $exception);
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Domain not found';
        $exception = new DomainNotFoundException($message);
        
        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Domain not found';
        $code = 404;
        $exception = new DomainNotFoundException($message, $code);
        
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }
}