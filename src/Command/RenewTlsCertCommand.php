<?php

namespace DomainCertificateBundle\Command;

use CloudflareDnsBundle\Repository\DnsDomainRepository;
use DomainCertificateBundle\Service\TlsService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: self::NAME, description: '更新TLS证书')]
class RenewTlsCertCommand extends Command
{
    public const NAME = 'cloudflare:renew-tls-cert';

    public function __construct(
        private readonly DnsDomainRepository $domainRepository,
        private readonly TlsService $tlsService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('更新TLS证书')
            ->addArgument('domainId', InputArgument::OPTIONAL, '域名ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $domain = $this->domainRepository->find($input->getArgument('domainId'));
        if (!$domain) {
            throw new \Exception('找不到证书信息');
        }

        $this->tlsService->renew($domain, $output);

        return Command::SUCCESS;
    }
}
