<?php

namespace DomainCertificateBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DomainCertificateBundle\Entity\TlsProxy;

/**
 * @method TlsProxy|null find($id, $lockMode = null, $lockVersion = null)
 * @method TlsProxy|null findOneBy(array $criteria, array $orderBy = null)
 * @method TlsProxy[] findAll()
 * @method TlsProxy[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TlsProxyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TlsProxy::class);
    }
}
