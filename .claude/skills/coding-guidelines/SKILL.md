---
name: coding-guidelines
description: Diretrizes para reduzir erros de LLM ao codar. Use quando estiver escrevendo, modificando ou revisando código — implementação, refatoração, bug fixes. Não use para arquitetura, documentação ou tarefas não-código.
---

# Coding Guidelines

Fonte: [tech-leads-club/agent-skills](https://github.com/tech-leads-club/agent-skills) — CC-BY-4.0

## 1. Pense antes de codar

- Declare premissas explicitamente. Se incerto, pergunte.
- Se múltiplas interpretações existem, apresente-as — não escolha silenciosamente.
- Se uma abordagem mais simples existe, diga. Empurre de volta quando justificado.

## 2. Simplicidade primeiro

- Nenhuma feature além do que foi pedido.
- Nenhuma abstração para código de uso único.
- Se escreveu 200 linhas e poderia ser 50, reescreva.

## 3. Mudanças cirúrgicas

Ao editar código existente:

- Não "melhore" código adjacente, comentários ou formatação.
- Não refatore coisas que não estão quebradas.
- Siga o estilo existente, mesmo que faria diferente.

## 4. Execução orientada a objetivo

- "Adicionar validação" → "Escrever validação com regras, verificar que retorna erro"
- "Corrigir o bug" → "Reproduzir, identificar causa raiz, corrigir, verificar"

## 5. Camadas obrigatórias (Backend)

### Controller
- **NUNCA** contém lógica de negócio
- Responsabilidade: receber request, chamar action, retornar response
- Sempre injeta Action após injetar a FormRequest em cada um dos métodos
- Sempre usa validação na camada adequada (FormRequest, DTO, etc.)

### Action
- Contém a lógica para realizar uma ação
- A nomenclatura deve seguir os métodos de CRUD. Exemplo: CreateUser, DeleteUserAvatar, etc.

### Fluxo completo
```
Request → Validação → Controller → Action (lógica) → Model
Response ← Formatação (API Resources) ← Controller ← Action ← Model
```

## 6. Enums — zero hardcode

- Toda mensagem retornada ao usuário **deve** vir de um Enum ou constante
- Toda constante de negócio (status, tipos, categorias) **deve** ser Enum
- Se precisar de uma nova mensagem, adicionar ao Enum — nunca inline

## . Idioma

| O quê | Idioma |
| Código (variáveis, classes, métodos) | **Inglês** |
| Mensagens de resposta da API | **Inglês**|
| Commits | **Inglês** |
| Documentação | **Português** |
| Comentários no código | **Inglês** |