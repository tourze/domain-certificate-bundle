<?php

namespace DomainCertificateBundle\DataFixtures;

use CloudflareDnsBundle\DataFixtures\DnsDomainFixtures;
use CloudflareDnsBundle\Entity\DnsDomain;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DomainCertificateBundle\Entity\TlsCertificate;

class TlsCertificateFixtures extends Fixture implements DependentFixtureInterface
{
    public const TLS_CERTIFICATE_REFERENCE = 'tls-certificate-example';

    public function load(ObjectManager $manager): void
    {
        $domain = $this->getReference(DnsDomainFixtures::EXAMPLE_DOMAIN_REFERENCE, DnsDomain::class);

        $certificate = new TlsCertificate();
        $certificate->setDomain($domain);
        $certificate->setTlsCertPath('/etc/ssl/certs/testdomain.local.crt');
        $certificate->setTlsKeyPath('/etc/ssl/private/testdomain.local.key');
        $certificate->setTlsFullchainPath('/etc/ssl/certs/testdomain.local.fullchain.crt');
        $certificate->setTlsChainPath('/etc/ssl/certs/testdomain.local.chain.crt');
        $certificate->setTlsExpireTime(new \DateTimeImmutable('+90 days'));

        $manager->persist($certificate);
        $manager->flush();

        $this->addReference(self::TLS_CERTIFICATE_REFERENCE, $certificate);
    }

    public function getDependencies(): array
    {
        return [
            DnsDomainFixtures::class,
        ];
    }
}
