<?php

namespace DomainCertificateBundle\Tests\Unit\Exception;

use DomainCertificateBundle\Exception\CertificateGenerationException;
use DomainCertificateBundle\Exception\DomainCertificateException;
use PHPUnit\Framework\TestCase;

class CertificateGenerationExceptionTest extends TestCase
{
    public function testExceptionExtendsDomainCertificateException(): void
    {
        $exception = new CertificateGenerationException();
        
        $this->assertInstanceOf(DomainCertificateException::class, $exception);
    }

    public function testExceptionWithMessage(): void
    {
        $message = 'Certificate generation failed';
        $exception = new CertificateGenerationException($message);
        
        $this->assertSame($message, $exception->getMessage());
    }

    public function testExceptionWithMessageAndCode(): void
    {
        $message = 'Certificate generation failed';
        $code = 500;
        $exception = new CertificateGenerationException($message, $code);
        
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }
}