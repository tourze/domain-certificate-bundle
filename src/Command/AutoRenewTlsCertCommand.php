<?php

namespace DomainCertificateBundle\Command;

use CloudflareDnsBundle\Repository\DnsDomainRepository;
use DomainCertificateBundle\Service\TlsService;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask(expression: '44 10 * * *')]
#[AsCommand(name: self::NAME, description: '自动更新所有TLS证书')]
#[Autoconfigure(public: true)]
#[WithMonologChannel(channel: 'domain_certificate')]
class AutoRenewTlsCertCommand extends Command
{
    public const NAME = 'cloudflare:auto-renew-tls-cert';

    public function __construct(
        private readonly DnsDomainRepository $domainRepository,
        private readonly TlsService $tlsService,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('自动更新所有TLS证书');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 要注意，下面必须要串行，因为远程不允许并发申请
        foreach ($this->domainRepository->findBy(['valid' => true]) as $item) {
            try {
                $this->tlsService->renew($item, $output);
                sleep(10);
            } catch (\Throwable $exception) {
                $output->writeln("[{$item->getName()}]证书更新失败：" . $exception);
                $this->logger->error('自动更新证书失败', [
                    'exception' => $exception,
                    'domain' => $item,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
