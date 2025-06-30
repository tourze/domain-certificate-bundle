<?php

namespace DomainCertificateBundle\Tests\Unit\Exception;

use DomainCertificateBundle\Exception\DomainCertificateException;
use DomainCertificateBundle\Exception\InvalidDomainIdException;
use PHPUnit\Framework\TestCase;

class InvalidDomainIdExceptionTest extends TestCase
{
    public function testExceptionExtendsDomainCertificateException(): void
    {
        $exception = new InvalidDomainIdException();
        
        $this->assertInstanceOf(DomainCertificateException::class, $exception);
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Invalid domain ID provided';
        $exception = new InvalidDomainIdException($message);
        
        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Invalid domain ID provided';
        $code = 400;
        $exception = new InvalidDomainIdException($message, $code);
        
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }
}