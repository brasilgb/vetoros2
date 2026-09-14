# VetorOS 2

## Instalação inicial

Após configurar o banco e executar as migrations, crie o administrador global:

```bash
php artisan migrate
php artisan root-admin:ensure \
  --name="Root Admin" \
  --email="admin@example.com"
```

O command solicita a senha de forma oculta. O `rootAdmin` é global, não pertence a tenant, não possui Company atual e não recebe vínculo em `company_user`.
