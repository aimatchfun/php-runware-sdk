# GitHub Actions Workflows

Este diretório contém os workflows do GitHub Actions para automação de testes.

## Workflows Disponíveis

### `tests.yml`
Workflow para executar os testes do **php-runware-sdk**.

**Características:**
- Executa em PHP 8.4
- Valida composer.json e composer.lock
- Cache de dependências do Composer
- Executa todos os testes com PHPUnit

**Triggers:**
- Push para branches `main` ou `develop`
- Pull requests para `main` ou `develop`
- Execução manual via `workflow_dispatch`

### `tests-combined.yml`
Workflow combinado que executa testes de **ambos os projetos** (php-runware-sdk e laravel-runware).

**Características:**
- Executa testes do php-runware-sdk
- Executa testes do laravel-runware em múltiplas versões do PHP e Laravel
- Gera um resumo final dos resultados

**Nota:** Este workflow assume que o repositório do laravel-runware está no mesmo workspace ou acessível via checkout.

## Como Usar

### Para php-runware-sdk apenas:
O workflow `tests.yml` será executado automaticamente em pushes e PRs.

### Para ambos os projetos:
1. Certifique-se de que ambos os projetos estão acessíveis
2. O workflow `tests-combined.yml` executará testes em ambos

## Configuração

Os workflows estão configurados para:
- ✅ Validar arquivos composer
- ✅ Cachear dependências para builds mais rápidos
- ✅ Executar testes com PHPUnit
- ✅ Suportar múltiplas versões do PHP (quando aplicável)

## Requisitos

- PHP 8.4+ para php-runware-sdk
- PHP 8.2+ para laravel-runware
- Composer instalado
- Extensões PHP: json, mbstring

