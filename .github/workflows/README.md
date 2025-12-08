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
Workflow para executar os testes do **php-runware-sdk** com resumo de resultados.

**Características:**
- Executa testes do php-runware-sdk
- Gera um resumo final dos resultados dos testes
- Similar ao `tests.yml`, mas com um job adicional de resumo

## Como Usar

### Para php-runware-sdk:
Os workflows `tests.yml` e `tests-combined.yml` serão executados automaticamente em pushes e PRs.

## Configuração

Os workflows estão configurados para:
- ✅ Validar arquivos composer
- ✅ Cachear dependências para builds mais rápidos
- ✅ Executar testes com PHPUnit
- ✅ Suportar múltiplas versões do PHP (quando aplicável)

## Requisitos

- PHP 8.4+ para php-runware-sdk
- Composer instalado
- Extensões PHP: json, mbstring

