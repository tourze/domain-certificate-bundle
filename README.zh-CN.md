# 域名证书管理包

[English](README.md) | [中文](README.zh-CN.md)

[![最新版本](https://img.shields.io/packagist/v/tourze/domain-certificate-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/domain-certificate-bundle)
[![下载总数](https://img.shields.io/packagist/dt/tourze/domain-certificate-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/domain-certificate-bundle)
[![PHP 版本](https://img.shields.io/packagist/php-v/tourze/domain-certificate-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/domain-certificate-bundle)

[![许可证](https://img.shields.io/packagist/l/tourze/domain-certificate-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/domain-certificate-bundle)
[![构建状态](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![代码覆盖率](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

一个用于管理域名 TLS 证书的 Symfony Bundle，集成 Cloudflare DNS 验证和自动续期功能。

## 目录

- [功能特性](#功能特性)
- [系统要求](#系统要求)
- [安装](#安装)
- [快速开始](#快速开始)
  - [1. 注册 Bundle](#1-注册-bundle)
  - [2. 配置域名](#2-配置域名)
  - [3. 生成证书](#3-生成证书)
- [配置](#配置)
  - [Bundle 配置](#bundle-配置)
  - [环境变量](#环境变量)
- [安全](#安全)
  - [证书存储](#证书存储)
  - [最佳实践](#最佳实践)
- [控制台命令](#控制台命令)
  - [`cloudflare:renew-tls-cert`](#cloudflarerenew-tls-cert)
  - [`cloudflare:auto-renew-tls-cert`](#cloudflareauto-renew-tls-cert)
  - [`cloudflare:start-tls-proxy`](#cloudflarestart-tls-proxy)
- [实体](#实体)
  - [TlsCertificate](#tlscertificate)
  - [TlsProxy](#tlsproxy)
- [高级用法](#高级用法)
  - [自动证书续期](#自动证书续期)
  - [TLS 代理服务器](#tls-代理服务器)
- [错误处理](#错误处理)
- [贡献](#贡献)
- [许可证](#许可证)

## 功能特性

- 使用 Let's Encrypt 自动生成 TLS 证书
- Cloudflare DNS 集成用于域名验证
- 通过定时任务支持自动证书续期
- TLS 代理服务器用于 SSL 终止
- 通过 Doctrine 实体管理证书存储
- 手动证书操作的命令行工具

## 系统要求

- PHP 8.1 或更高版本
- Symfony 6.4 或更高版本
- 具有 DNS 管理权限的 Cloudflare 账户
- 服务器上安装 Let's Encrypt certbot

## 安装

```bash
composer require tourze/domain-certificate-bundle
```

## 快速开始

### 1. 注册 Bundle

如果您没有使用 Symfony Flex，请在 `config/bundles.php` 中注册 Bundle：

```php
return [
    // ...
    DomainCertificateBundle\DomainCertificateBundle::class => ['all' => true],
];
```

### 2. 配置域名

此 Bundle 与 `tourze/cloudflare-dns-bundle` 配合使用来管理域名。请确保您已配置有效的 Cloudflare 凭证的域名。

### 3. 生成证书

```bash
# 为特定域名续期证书
php bin/console cloudflare:renew-tls-cert <domainId>

# 自动续期所有有效域名的证书
php bin/console cloudflare:auto-renew-tls-cert
```

## 配置

### Bundle 配置

Bundle 使用开箱即用的默认配置。如果您需要自定义设置，可以创建配置文件：

```yaml
# config/packages/domain_certificate.yaml
domain_certificate:
    storage_path: '/data/cloudflare/tls-cert'
    certbot_email: 'your-email@example.com'
```

### 环境变量

确保您的 Cloudflare 凭证通过 `tourze/cloudflare-dns-bundle` 正确配置。

## 安全

### 证书存储

- 所有证书都存储在具有受限权限的安全目录中
- 私钥受到 600 文件权限保护
- Cloudflare API 凭证临时存储并在使用后删除

### 最佳实践

- 定期监控证书过期日期
- 使用自动续期防止服务中断
- 限制对证书存储目录的访问
- 监控日志以查看任何证书生成失败

## 控制台命令

### `cloudflare:renew-tls-cert`

为特定域名续期或生成 TLS 证书。

**用法：**
```bash
php bin/console cloudflare:renew-tls-cert <domainId>
```

**参数：**
- `domainId`：要续期证书的域名 ID

**描述：**
此命令使用 Let's Encrypt 配合 Cloudflare DNS 验证来生成或续期指定域名的 TLS 证书。

### `cloudflare:auto-renew-tls-cert`

自动续期系统中所有有效域名的证书。

**用法：**
```bash
php bin/console cloudflare:auto-renew-tls-cert
```

**功能：**
- 作为定时任务运行（默认每天 10:44 执行）
- 处理所有标记为有效的域名
- 包含错误处理和日志记录
- 串行处理以避免速率限制

### `cloudflare:start-tls-proxy`

启动 TLS 代理服务器用于 SSL 终止。

**用法：**
```bash
php bin/console cloudflare:start-tls-proxy <type> [--daemon|-d]
```

**参数：**
- `type`：Workerman 命令类型（start、stop、restart、reload、status）

**选项：**
- `--daemon` 或 `-d`：以守护进程模式运行

**功能：**
- 使用 Workerman 的 SSL 终止代理
- 自动检测 CPU 核心数用于工作进程
- 支持多个域名在不同端口
- 在客户端和目标服务器之间管道连接

## 实体

### TlsCertificate

存储域名的证书信息：
- 域名关联
- 证书和密钥文件路径
- 创建和更新时间戳
- 用户跟踪

### TlsProxy

配置 SSL 终止的代理设置：
- 监听端口配置
- 目标主机和端口映射
- 域名关联
- 启用/禁用功能

## 高级用法

### 自动证书续期

Bundle 通过使用 `#[AsCronTask]` 装饰的 `AutoRenewTlsCertCommand` 包含自动续期功能，每天运行。要启用自动续期：

1. 确保您的定时任务系统配置为运行 Symfony 命令
2. 命令将在每天 10:44 自动执行
3. 监控日志以了解续期状态和任何错误

### TLS 代理服务器

TLS 代理服务器为您的应用程序提供 SSL 终止：

```bash
# 启动代理服务器
php bin/console cloudflare:start-tls-proxy start

# 以守护进程模式启动
php bin/console cloudflare:start-tls-proxy start -d

# 检查状态
php bin/console cloudflare:start-tls-proxy status

# 停止服务器
php bin/console cloudflare:start-tls-proxy stop
```

## 错误处理

Bundle 包含自定义异常以提供更好的错误管理：
- `DomainNotFoundException`：当找不到域名时抛出
- `InvalidDomainIdException`：当提供无效的域名 ID 时抛出
- `CertificateGenerationException`：当证书生成失败时抛出

## 贡献

详情请参见 [CONTRIBUTING.md](CONTRIBUTING.md)。

## 许可证

MIT 许可证（MIT）。详情请参见[许可证文件](LICENSE)。