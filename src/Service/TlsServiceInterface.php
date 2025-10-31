<?php

declare(strict_types=1);

namespace DomainCertificateBundle\Service;

use CloudflareDnsBundle\Entity\DnsDomain;
use Symfony\Component\Console\Output\OutputInterface;

interface TlsServiceInterface
{
    public function renew(DnsDomain $domain, OutputInterface $output): void;
}
