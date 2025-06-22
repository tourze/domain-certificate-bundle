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
        $domainId = $input->getArgument('domainId');
        if ($domainId === null || $domainId === '') {
            throw new \Exception('请提供域名ID');
        }
        
        $domain = $this->domainRepository->find($domainId);
        if ($domain === null) {
            throw new \Exception('找不到证书信息');
        }

        $this->tlsService->renew($domain, $output);

        return Command::SUCCESS;
    }
}
