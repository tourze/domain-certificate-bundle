<?php

namespace DomainCertificateBundle\Command;

use DomainCertificateBundle\Repository\TlsProxyRepository;
use Fidry\CpuCoreCounter\CpuCoreCounter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Workerman\Connection\AsyncTcpConnection;
use Workerman\Connection\TcpConnection;
use Workerman\Worker;

#[AsCommand(name: StartTlsProxyServerCommand::NAME)]
class StartTlsProxyServerCommand extends Command
{
    public const NAME = 'cloudflare:start-tls-proxy';

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly TlsProxyRepository $proxyRepository,
        private readonly CpuCoreCounter $counter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('type', InputArgument::REQUIRED, 'Workerman命令');
        $this->addOption('daemon', 'd');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cpuCount = $this->counter->getCount();

        Worker::$pidFile = $this->kernel->getProjectDir() . '/tls-proxy.pid';
        Worker::$logFile = $this->kernel->getProjectDir() . '/tls-proxy.log';

        foreach ($this->proxyRepository->findBy(['valid' => true]) as $proxy) {
            // 证书最好是申请的证书
            $context = [
                'ssl' => [
                    'local_cert' => $proxy->getDomain()->getTlsCertPath(), // 也可以是crt文件
                    'local_pk' => $proxy->getDomain()->getTlsKeyPath(),
                    'verify_peer' => false,
                    'allow_self_signed' => true,
                ],
            ];
            // 这里设置的是websocket协议，也可以http协议或者其它协议
            $worker = new Worker("http://0.0.0.0:{$proxy->getListenPort()}", $context);
            $worker->count = $cpuCount;
            $worker->name = "tls_proxy_{$proxy->getDomain()->getName()}_{$proxy->getId()}";
            // 设置transport开启ssl
            $worker->transport = 'ssl';
            $worker->onMessage = function (TcpConnection $connection, string $buffer) use ($proxy) {
                var_dump($connection->id, $buffer);
                $remoteConn = new AsyncTcpConnection("tcp://{$proxy->getTargetHost()}:{$proxy->getTargetPort()}");
                $connection->pipe($remoteConn);
                $this->pipe($connection, $remoteConn);
                $this->pipe($remoteConn, $connection);
                $remoteConn->connect();
                $remoteConn->send($buffer);
            };
        }

        Worker::runAll();

        return Command::SUCCESS;
    }

    protected function pipe(TcpConnection $source, TcpConnection $dest): void
    {
        $source->onMessage = function ($source, $data) use ($dest) {
            $dest->send($data, true);
        };
        $source->onClose = function () use ($dest) {
            $dest->close();
        };
        $dest->onBufferFull = function () use ($source) {
            $source->pauseRecv();
        };
        $dest->onBufferDrain = function () use ($source) {
            $source->resumeRecv();
        };
    }
}
