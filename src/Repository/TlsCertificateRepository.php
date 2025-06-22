<?php

namespace DomainCertificateBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DomainCertificateBundle\Entity\TlsCertificate;

/**
 * @extends ServiceEntityRepository<TlsCertificate>
 */
class TlsCertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TlsCertificate::class);
    }
}