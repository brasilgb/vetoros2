# Complemento — Dashboard template do rootAdmin

Além do command `root-admin:ensure`, criar uma estrutura própria de dashboard administrativo global.

## Objetivo

O painel `/admin` deve possuir um template/layout próprio, visualmente coerente com o dashboard do tenant, mas sem reutilizar contexto de tenant ou Company.

Não usar:

* `CurrentTenant`
* `CurrentCompany`
* `CompanySelector`
* sidebar operacional do tenant
* qualquer dependência de `tenant_id`

## Estrutura sugerida

Criar um layout administrativo, por exemplo:

```text
resources/js/layouts/admin-layout.tsx
```

ou seguir a convenção já existente no projeto.

O layout deve possuir:

* header;
* área principal de conteúdo;
* nome do usuário logado;
* identificação discreta de "Administração";
* botão/logout;
* estrutura preparada para sidebar futura, caso o layout tenant já utilize esse padrão.

Visualmente, aproveitar os mesmos primitives/componentes já usados no layout tenant para manter consistência.

Não criar uma segunda biblioteca visual nem duplicar componentes sem necessidade.

## Dashboard inicial

A rota:

```text
/admin
```

deve renderizar um dashboard mínimo, semelhante ao tenant:

```text
Olá, Root Admin

Administração do VetorOS

[Sair]
```

Se o layout tenant já possui shell/header/sidebar, usar a mesma linguagem visual, mas criar um contexto administrativo separado.

## Navegação inicial

Nesta etapa, não criar módulos administrativos fictícios.

A navegação pode conter apenas:

```text
Dashboard
```

Não adicionar ainda:

* Tenants
* Planos
* Assinaturas
* Usuários
* Financeiro
* Métricas
* Configurações globais

Esses itens entrarão quando os respectivos módulos existirem.

## Separação obrigatória

Deve existir separação clara:

```text
TenantLayout
→ contexto operacional
→ Tenant
→ CurrentCompany
```

e:

```text
AdminLayout
→ contexto global
→ rootAdmin
→ sem Tenant
→ sem CurrentCompany
```

Não colocar condicionais extensas dentro de um único layout para tentar atender os dois contextos.

Preferir dois layouts claros compartilhando apenas componentes visuais genéricos.

## Segurança

Garantir que:

* `/admin` exige autenticação;
* `/admin` exige `root.admin`;
* usuário comum não consegue renderizar `AdminLayout`;
* rootAdmin não precisa de tenant/company;
* `HandleInertiaRequests` não deve tentar fornecer contexto operacional obrigatório ao painel root;
* dados compartilhados globalmente não devem causar erro quando `tenant` e `currentCompany` forem `null`.

## Fluxo esperado

### Usuário comum

```text
/login
→ autenticação
→ Tenant
→ CurrentCompany
→ TenantLayout
→ /dashboard
```

### rootAdmin

```text
/login
→ autenticação
→ is_root_admin
→ AdminLayout
→ /admin
```

## Testes adicionais

Adicionar testes para comprovar:

* rootAdmin acessa `/admin`;
* usuário comum recebe bloqueio;
* guest é redirecionado para login;
* página admin renderiza o nome do rootAdmin;
* painel admin funciona com `tenant_id = null`;
* painel admin funciona sem `current_company_id`;
* dashboard tenant continua funcionando normalmente após criação do `AdminLayout`.

## Frontend

Executar:

```bash
npm run types:check
npm run build
```

e lint dos arquivos alterados.

## Critério de conclusão

Ao final devemos possuir duas fundações visuais independentes:

```text
Tenant
→ TenantLayout
→ Dashboard
→ nome do usuário
→ logout
```

e:

```text
rootAdmin
→ AdminLayout
→ Dashboard Admin
→ nome do usuário
→ logout
```

Ainda sem implementar módulos de negócio ou administração.

Esse dashboard administrativo será o template base para os futuros recursos globais do SaaS.
