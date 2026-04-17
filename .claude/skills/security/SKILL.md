---
name: security
description: Práticas de segurança para API e frontend. Use quando revisar segurança, criar endpoints, lidar com dados pessoais, ou proteger contra ataques. Não use para SEO ou acessibilidade.
---

# Security Best Practices

Fonte: [tech-leads-club/agent-skills](https://github.com/tech-leads-club/agent-skills) — CC-BY-4.0

## Backend

### Auth e permissões
- Middleware de autenticação em rotas protegidas
- Verificar permissões do usuário antes de operações sensíveis

### Validação
- Sempre validar input na camada adequada
- Sanitizar inputs antes de processar
- Validar uploads (tipo, tamanho) antes de processar

### IDs públicos
- UUIDs para recursos expostos na API, nunca IDs auto-incrementais

### Rate limiting
- Rotas sensíveis: rate limit agressivo
- Uploads: limitar tamanho e frequência
- Webhooks: validar assinatura/origem

### Dados sensíveis
- Tokens e secrets: apenas em `.env`, nunca no código
- Logs: nunca logar dados pessoais completos

### CORS
- Aceitar apenas domínios autorizados, nunca `*` em produção

## Frontend

- Nunca usar `v-html` / `dangerouslySetInnerHTML` com dados do usuário
- Tokens via httpOnly cookies ou Bearer token seguro

## Prompt injection (se usar LLM)

- Validar e sanitizar todos os campos ANTES de montar o prompt
- Limitar tamanho dos inputs do usuário
- System prompt fixo, nunca editável pelo usuário
- Conteúdo de uploads: sanitizar antes de enviar pra LLM