# Domain Certificate Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/domain-certificate-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/domain-certificate-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/domain-certificate-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/domain-certificate-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/domain-certificate-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/domain-certificate-bundle)

[![License](https://img.shields.io/packagist/l/tourze/domain-certificate-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/domain-certificate-bundle)
[![Build Status](https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?style=flat-square)](https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

A Symfony bundle for managing domain TLS certificates with Cloudflare DNS integration 
and automated renewal capabilities.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
  - [1. Register the Bundle](#1-register-the-bundle)
  - [2. Configure Your Domain](#2-configure-your-domain)
  - [3. Generate a Certificate](#3-generate-a-certificate)
- [Configuration](#configuration)
  - [Bundle Configuration](#bundle-configuration)
  - [Environment Variables](#environment-variables)
- [Security](#security)
  - [Certificate Storage](#certificate-storage)
  - [Best Practices](#best-practices)
- [Console Commands](#console-commands)
  - [`cloudflare:renew-tls-cert`](#cloudflarerenew-tls-cert)
  - [`cloudflare:auto-renew-tls-cert`](#cloudflareauto-renew-tls-cert)
  - [`cloudflare:start-tls-proxy`](#cloudflarestart-tls-proxy)
- [Entities](#entities)
  - [TlsCertificate](#tlscertificate)
  - [TlsProxy](#tlsproxy)
- [Advanced Usage](#advanced-usage)
  - [Automated Certificate Renewal](#automated-certificate-renewal)
  - [TLS Proxy Server](#tls-proxy-server)
- [Error Handling](#error-handling)
- [Contributing](#contributing)
- [License](#license)

## Features

- Automated TLS certificate generation using Let's Encrypt
- Cloudflare DNS integration for domain validation
- Automatic certificate renewal with cron job support
- TLS proxy server for SSL termination
- Certificate storage and management via Doctrine entities
- Command-line tools for manual certificate operations

## Requirements

- PHP 8.1 or higher
- Symfony 6.4 or higher
- Cloudflare account with DNS management
- Let's Encrypt certbot installed on the server

## Installation

```bash
composer require tourze/domain-certificate-bundle
```

## Quick Start

### 1. Register the Bundle

If you're not using Symfony Flex, register the bundle in your `config/bundles.php`:

```php
return [
    // ...
    DomainCertificateBundle\DomainCertificateBundle::class => ['all' => true],
];
```

### 2. Configure Your Domain

This bundle works with the `tourze/cloudflare-dns-bundle` to manage domains. 
Ensure you have domains configured with valid Cloudflare credentials.

### 3. Generate a Certificate

```bash
# Renew certificate for a specific domain
php bin/console cloudflare:renew-tls-cert <domainId>

# Automatically renew all valid domain certificates
php bin/console cloudflare:auto-renew-tls-cert
```

## Configuration

### Bundle Configuration

The bundle uses default configuration that works out of the box. 
If you need custom settings, you can create a configuration file:

```yaml
# config/packages/domain_certificate.yaml
domain_certificate:
    storage_path: '/data/cloudflare/tls-cert'
    certbot_email: 'your-email@example.com'
```

### Environment Variables

Ensure your Cloudflare credentials are properly configured through the 
`tourze/cloudflare-dns-bundle`.

## Security

### Certificate Storage

- All certificates are stored in a secure directory with restricted permissions
- Private keys are protected with 600 file permissions
- Cloudflare API credentials are temporarily stored and removed after use

### Best Practices

- Regularly monitor certificate expiration dates
- Use automated renewal to prevent service interruptions
- Restrict access to certificate storage directories
- Monitor logs for any certificate generation failures

## Console Commands

### `cloudflare:renew-tls-cert`

Renews or generates a TLS certificate for a specific domain.

**Usage:**
```bash
php bin/console cloudflare:renew-tls-cert <domainId>
```

**Arguments:**
- `domainId`: The ID of the domain to renew the certificate for

**Description:**
This command uses Let's Encrypt with Cloudflare DNS validation to generate 
or renew TLS certificates for the specified domain.

### `cloudflare:auto-renew-tls-cert`

Automatically renews certificates for all valid domains in the system.

**Usage:**
```bash
php bin/console cloudflare:auto-renew-tls-cert
```

**Features:**
- Runs as a cron job (scheduled for 10:44 daily by default)
- Processes all domains marked as valid
- Includes error handling and logging
- Serial processing to avoid rate limiting

### `cloudflare:start-tls-proxy`

Starts a TLS proxy server for SSL termination.

**Usage:**
```bash
php bin/console cloudflare:start-tls-proxy <type> [--daemon|-d]
```

**Arguments:**
- `type`: Workerman command type (start, stop, restart, reload, status)

**Options:**
- `--daemon` or `-d`: Run in daemon mode

**Features:**
- SSL termination proxy using Workerman
- Automatic CPU core detection for worker processes
- Support for multiple domains on different ports
- Pipe connections between client and target server

## Entities

### TlsCertificate

Stores certificate information for domains:
- Domain association
- Certificate and key file paths
- Timestamps for creation and updates
- User tracking

### TlsProxy

Configures proxy settings for SSL termination:
- Listen port configuration
- Target host and port mapping
- Domain association
- Enable/disable functionality

## Advanced Usage

### Automated Certificate Renewal

The bundle includes automatic renewal capabilities through the `AutoRenewTlsCertCommand` 
which is decorated with `#[AsCronTask]` to run daily. To enable automatic renewal:

1. Ensure your cron job system is configured to run Symfony commands
2. The command will automatically execute at 10:44 daily
3. Monitor logs for renewal status and any errors

### TLS Proxy Server

The TLS proxy server provides SSL termination for your applications:

```bash
# Start the proxy server
php bin/console cloudflare:start-tls-proxy start

# Start in daemon mode
php bin/console cloudflare:start-tls-proxy start -d

# Check status
php bin/console cloudflare:start-tls-proxy status

# Stop the server
php bin/console cloudflare:start-tls-proxy stop
```

## Error Handling

The bundle includes custom exceptions for better error management:
- `DomainNotFoundException`: Thrown when a domain cannot be found
- `InvalidDomainIdException`: Thrown when an invalid domain ID is provided
- `CertificateGenerationException`: Thrown when certificate generation fails

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.