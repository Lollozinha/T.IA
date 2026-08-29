# Documentação acadêmica — T.IA (Parte 1 e Parte 2)

Textos em Markdown nesta pasta devem ser convertidos para PDF na entrega da disciplina. Prints de tela ficam a cargo da equipe (não versionamos imagens): cada documento tem uma lista numerada com o marcador *colar print*.

| Documento | Conteúdo |
|---|---|
| [REQUISITO-1-autenticacao.md](REQUISITO-1-autenticacao.md) | Autenticação, senhas (Argon2id), 2FA do Mediador, sessões, força bruta, justificativas e evidências (itens 1.1–1.12) |
| [REQUISITO-2-recuperacao-senha.md](REQUISITO-2-recuperacao-senha.md) | Recuperação de senha: token de uso único, expiração, hash, anti-enumeração, auditoria e invalidação de sessão (itens 2.1–2.9) |

## Conversão sugerida para PDF

Qualquer ferramenta que aceite Markdown + Mermaid serve. Exemplos:

- VS Code / Cursor: extensão “Markdown PDF” ou “Markdown Preview Mermaid Support” + imprimir
- Pandoc: `pandoc REQUISITO-1-autenticacao.md -o REQUISITO-1-autenticacao.pdf`

Cole os prints nas seções 1.8 e 2.9 **antes** de gerar o PDF final.
