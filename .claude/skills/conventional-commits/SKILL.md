---
name: conventional-commits
description: Padrão de mensagens de commit do projeto. Use quando sugerir commits ou criar mensagens de commit. Não use para code review ou documentação.
---

# Conventional Commits

## Formato

```
<tipo>(<escopo>): <descrição curta>
```

## Tipos

| Tipo | Quando usar |
|---|---|
| `feat` | Nova funcionalidade |
| `fix` | Correção de bug |
| `refactor` | Mudança de código sem alterar comportamento |
| `chore` | Manutenção (deps, config, CI) |
| `docs` | Apenas documentação |
| `test` | Adição ou correção de testes |
| `perf` | Melhoria de performance |

## Escopos

| Escopo | Domínio |
|---|---|
| `user` | Auth, perfil |
| `infra` | Docker, CI/CD, deploy |
| ... | ... |

## Regras

- Descrição em imperativo, lowercase, sem ponto final
- Máximo 72 caracteres na primeira linha
- Breaking changes: `feat(user)!: alterar schema de autenticação`

## Branches

```
feature/{numero-da-issue}-{nome-curto}
```

- **NUNCA commitar direto em `develop` ou `master`**
- Fluxo obrigatório: `feature/* → PR → develop → PR → master`
- Merge: squash merge com referência à issue