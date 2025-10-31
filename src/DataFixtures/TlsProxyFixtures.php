<?php

namespace DomainCertificateBundle\DataFixtures;

use CloudflareDnsBundle\DataFixtures\DnsDomainFixtures;
use CloudflareDnsBundle\Entity\DnsDomain;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DomainCertificateBundle\Entity\TlsProxy;

class TlsProxyFixtures extends Fixture implements DependentFixtureInterface
{
    public const TLS_PROXY_REFERENCE = 'tls-proxy-example';

    public function load(ObjectManager $manager): void
    {
        $domain = $this->getReference(DnsDomainFixtures::EXAMPLE_DOMAIN_REFERENCE, DnsDomain::class);

        $proxy = new TlsProxy();
        $proxy->setName('test-proxy');
        $proxy->setDomain($domain);
        $proxy->setListenPort(443);
        $proxy->setTargetHost('localhost');
        $proxy->setTargetPort(8080);
        $proxy->setValid(true);

        $manager->persist($proxy);
        $manager->flush();

        $this->addReference(self::TLS_PROXY_REFERENCE, $proxy);
    }

    public function getDependencies(): array
    {
        return [
            DnsDomainFixtures::class,
        ];
    }
}
