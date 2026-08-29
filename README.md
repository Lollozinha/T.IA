# T.IA

Aplicação web de apoio cognitivo gamificado para crianças de 2 a 7 anos com TEA Nível I.

Esta primeira entrega cobre cadastro, autenticação, recuperação de senha e 2FA do perfil Mediador, em um monólito Laravel 13 dockerizado.

## Stack

- PHP 8.3-FPM + Nginx
- Laravel 13 + Breeze (Blade + Tailwind)
- MySQL 8.4
- Mailpit (e-mails locais)
- Vite (assets)

## Requisitos

- Docker e Docker Compose
- Portas livres: 8080 (app), 5173 (Vite), 3306 (MySQL), 8025 e 1025 (Mailpit)

## Subir o ambiente

No WSL, na raiz do repositório:

```bash
cp .env.docker.example .env
docker compose build
docker compose up -d
docker compose exec php php artisan migrate
```

Aplicação: http://localhost:8080  
Mailpit: http://localhost:8025

## Contas de teste

1. Acesse `/register` e escolha **Responsável** ou **Mediador**.
2. Senha mínima: 8 caracteres, com maiúscula, minúscula e número.
3. Mediador é obrigado a ativar 2FA (QR + OTP de 6 dígitos) antes de usar o painel.
4. Após o 2FA ativo, o próximo login pede o código OTP.

## Segurança desta entrega

Documentação acadêmica (Markdown, para converter em PDF):

- [Requisito 1 — Autenticação](docs/REQUISITO-1-autenticacao.md)
- [Requisito 2 — Recuperação de senha](docs/REQUISITO-2-recuperacao-senha.md)

Pontos implementados:

- Hash de senha Argon2id (`HASH_DRIVER=argon2id`; custos em `ARGON_MEMORY` / `ARGON_TIME` / `ARGON_THREADS`)
- Bloqueio após 5 logins inválidos por IP ou e-mail, por 2 horas (`LOGIN_DECAY_SECONDS=7200`)
- Sessão em banco, criptografada, invalidada no logout e na troca de senha
- Token de reset de uso único, validade configurável (`PASSWORD_RESET_EXPIRE`, padrão 15 min)
- Auditoria em `storage/logs/audit.log` e na tabela `auth_audit_logs`

## Licença

MIT — ver [LICENSE](LICENSE).
