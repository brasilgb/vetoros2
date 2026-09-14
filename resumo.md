# Resumo — Fundação Multitenant do VetorOS 2

## Arquitetura definitiva

- Multi-tenant em banco único.
- Cada tenant possui uma única empresa matriz e várias filiais.
- Matriz e filiais ficam na tabela `companies`.
- Não deve existir uma tabela ou modelo paralelo `branches`.

### Hierarquia em `companies`

- `tenant_id`: identifica o tenant.
- `type = headquarters`: matriz.
- `type = branch`: filial.
- Matriz: `parent_id = null`.
- Filial: `parent_id` aponta para uma matriz do mesmo tenant.
- A hierarquia possui no máximo dois níveis.

## Consolidação necessária

Auditar e adaptar migrations, models, factories, services, middlewares, rotas e testes para:

- remover `Branch`, `branches`, `branch_user`, `BranchAccessService`, `CurrentBranch` e `EnsureCurrentBranch`;
- reaproveitar suas funcionalidades usando `Company`;
- renomear conceitos para `CompanyAccessService`, `CurrentCompany` e `EnsureCurrentCompany`;
- manter seleção de empresa, empresa ativa, contexto operacional e validação de acesso;
- usar `company_user` para acesso de usuários à matriz e às filiais;
- preservar `is_default` quando aplicável.

## Segurança e integridade

- `Company` deve usar `BelongsToTenant` e `TenantScope`.
- `tenant_id` deve ser preenchido pelo tenant atual e não ser aceito da requisição.
- Consultas Eloquent comuns devem isolar os tenants.
- Deve existir somente uma matriz por tenant, com proteção no banco e na aplicação.
- Filiais devem apontar para matriz ativa do mesmo tenant.
- Não permitir filial de filial nem associação de usuário/empresa entre tenants.
- O contexto atual deve aceitar somente empresa ativa e autorizada.
- Manipulação de `current_company_id` deve ser rejeitada com segurança.
- O middleware deve seguir a sequência: autenticação, tenant atual, empresa atual e autorização.
- Rotas operacionais e dashboard devem exigir tenant e empresa válidos, exceto fluxos explícitos de onboarding.

## Cadastro inicial

Preparar a arquitetura para criar atomicamente:

`User + Tenant + Company matriz + vínculo company_user + usuário administrador/default`

As filiais futuras serão novas linhas em `companies`.

## Migrations

- Consolidar o schema sem deixar tabelas mortas ou arquiteturas concorrentes.
- Preferir ajustar migrations ainda não compartilhadas; caso já existam dados relevantes, criar migração explícita e não destruir dados silenciosamente.
- Usar constraints e índices compostos quando tecnicamente viável.

## Testes obrigatórios

Cobrir:

- preenchimento automático e isolamento de `tenant_id`;
- rejeição de ownership externo;
- unicidade da matriz;
- regras de `parent_id`, tipo, tenant e atividade;
- isolamento de `company_user`;
- acesso à matriz e às filiais;
- resolução e troca da empresa padrão;
- bloqueio de empresa sem permissão ou inativa;
- proteção contra manipulação de `current_company_id`;
- exigência de tenant/empresa válidos nos middlewares e dashboard;
- funcionamento do `TenantScope`.

Não implementar ainda CRM, clientes, equipamentos, ordens de serviço, vendas, estoque ou outros módulos.

## Execução realizada

- `Company` agora é tenant-aware com `BelongsToTenant` e `TenantScope`.
- `tenant_id` foi removido do `$fillable` de `Company` e é atribuído pelo tenant atual, inclusive em atualizações.
- A hierarquia matriz/filial é validada na aplicação: uma matriz por tenant, matriz sem parent, filial com parent matriz ativa do mesmo tenant e sem filial de filial.
- Foi adicionada proteção de banco para uma única matriz por tenant usando coluna gerada e índice único.
- `company_user` agora possui `tenant_id` e chaves estrangeiras compostas para impedir vínculos entre tenants.
- `CompanyAccessService` substituiu o serviço de Branch e preserva concessão, revogação e empresa padrão.
- `CurrentCompany` usa `current_company_id`, resolve empresa padrão e rejeita empresa ausente, inativa ou não autorizada.
- `EnsureCurrentCompany` foi registrado e o dashboard passou a exigir tenant e empresa válidos.
- O fluxo de rotas foi ajustado para a API compatível com Inertia Laravel 3.3.4.
- Foi criada uma migração explícita que converte dados existentes de `branches`/`branch_user` para `companies`/`company_user` e remove as tabelas paralelas ao final.
- Os testes antigos de Branch foram substituídos por testes de isolamento, hierarquia, acesso e contexto de Company.
- Foram adicionadas `CompanyFactory` e `TenantFactory`.

## Verificações

- Pint: passou nos arquivos alterados.
- PHPStan direcionado aos modelos, serviço, contexto e middleware: passou sem erros.
- Sintaxe PHP: passou em 49 arquivos.
- Rotas: carregadas com sucesso, total de 10 rotas.
- Testes de feature: não puderam executar por falta do driver SQLite; o MySQL em `172.20.70.200:3306` também não ficou acessível neste ambiente.

## Etapa adicional do `executar.md` — Front Admin e Dashboard

- Criado o endpoint `POST /companies/current`, protegido por autenticação, verificação e tenant atual.
- O backend valida que a Company existe no tenant, está ativa e pertence ao usuário antes de atualizar `current_company_id` na sessão.
- O middleware `HandleInertiaRequests` agora compartilha `tenant`, `currentCompany`, `availableCompanies` e `permissions`.
- Criado o componente React `CompanySelector` no header do layout do tenant.
- O seletor lista somente Companies autorizadas e ativas, identifica matriz/filial e troca o contexto via Wayfinder/Inertia.
- A sidebar foi renomeada para `Workspace`; nenhum módulo futuro falso foi criado.
- Foram adicionados testes HTTP para troca autorizada, bloqueio de troca não autorizada e exigência de Company no dashboard.
- Wayfinder foi regenerado após a criação da rota.

### Status das validações da etapa adicional

- PASS — TypeScript (`npm run types:check`).
- PASS — lint direcionado dos componentes React alterados.
- PASS — Pint nos arquivos PHP alterados.
- PASS — PHPStan nos controller/middlewares alterados.
- PASS — build frontend (`npm run build`).
- BLOCKED — testes HTTP: MySQL de teste em `127.0.0.1:3306`/`vetoros2_test` indisponível neste executor.
- PASS — nenhuma referência de naming legado (`BranchSelector`, `CurrentBranch`, `availableBranches`, `currentBranch`, `branchId` ou `branchAccess`) encontrada no código ativo.

## Correção das falhas restantes do `executar.md`

O arquivo foi atualizado com uma nova etapa para corrigir seis falhas da fundação. A causa raiz foi identificada e corrigida:

- `CompanyFactory` não possuía os states explícitos `headquarters()` e `branch()`.
- A validação de hierarquia consultava `getRawOriginal('type')`, que não contém o valor correto antes da primeira persistência do model.
- A validação agora lê o atributo em memória e converte corretamente para `CompanyType`.
- O state `branch()` define `type = branch` e aceita uma matriz `Company` ou ID como parent.
- O state `headquarters()` força `parent_id = null`.
- As invariantes continuam rejeitando matriz com parent, filial sem parent, filial de filial, parent de outro tenant e matriz inativa.
- O teste de hierarquia passou a exercer também o state `CompanyFactory::branch()`.

### Status da etapa

- PASS — Pint nos arquivos PHP alterados.
- PASS — PHPStan em `Company` e `CompanyFactory`.
- BLOCKED — `php artisan test --filter=Multitenancy`: 20 testes não iniciaram por indisponibilidade do MySQL em `172.20.70.200:3306`.
- BLOCKED — `php artisan test`: 56 erros de conexão com `vetoros2_test`; 1 teste sem banco passou.

Os testes não foram declarados como verdes porque a infraestrutura de banco impediu sua execução efetiva.

## Ajuste de Login, cadastro público e rootAdmin

`executar.md` recebeu uma nova etapa de autenticação pública e onboarding multitenant. Foram implementados:

- cadastro público sem middleware de tenant/company;
- campo `company_name` no formulário de registro;
- criação transacional de `Tenant`, usuário, `Company` matriz e vínculo `company_user` padrão;
- inicialização da matriz como Company padrão/current após cadastro;
- migration `is_root_admin` e método `User::isRootAdmin()`;
- bypass controlado de tenant/company somente para rootAdmin;
- resposta de login que inicializa tenant e Company padrão para usuários comuns;
- redirecionamento de rootAdmin para `/admin` sem `rootTenant` ou `rootCompany`;
- middleware `root.admin` protegendo o painel global;
- painel global mínimo separado do layout tenant;
- telas públicas de login e cadastro mantidas no layout Fortify/Inertia, sem seletor ou sidebar tenant;
- testes de cadastro transacional e login rootAdmin adicionados/ajustados.

### Status da etapa de autenticação

- PASS — Pint nos arquivos PHP alterados.
- PASS — PHPStan em ações, modelo, middlewares, response e provider alterados.
- PASS — TypeScript (`npm run types:check`).
- PASS — lint direcionado das telas React alteradas.
- PASS — build frontend (`npm run build`).
- PASS — rotas públicas e `/admin` carregadas; `/login`, `/register`, reset e verificação permanecem sob middleware `web`, sem `current.tenant`/`current.company`.
- BLOCKED — testes de autenticação: 31 casos não iniciaram por indisponibilidade do MySQL em `172.20.70.200:3306` (`vetoros2_test`).

## Execução da nova etapa do `executar.md` — Dashboard Admin global

A análise confirmou que `executar.md` havia sido atualizado após o último `resumo.md`. A nova etapa foi executada:

- criado `AdminLayout`, separado do layout tenant e sem `CurrentTenant`, `CurrentCompany`, `CompanySelector` ou `tenant_id`;
- adicionado header administrativo com nome do usuário, identificação "Administração", logout e navegação inicial somente para `Dashboard`;
- dashboard `/admin` ajustado para exibir o nome do rootAdmin e "Administração do VetorOS";
- mantida a proteção de autenticação, verificação e `root.admin` na rota `/admin`;
- adicionados testes HTTP para guest, usuário comum e rootAdmin sem tenant/company.

### Validações da nova etapa

- PASS — TypeScript (`npm run types:check`).
- PASS — lint direcionado dos arquivos React alterados.
- PASS — build frontend (`npm run build`).
- PASS — Pint executado sem filtro de Git; o modo `--dirty` não está disponível porque o diretório `.git` do workspace está vazio.
- BLOCKED — `php artisan test --compact tests/Feature/AdminDashboardTest.php`: os 3 testes não iniciaram por indisponibilidade do MySQL de teste em `127.0.0.1:3306` (`vetoros2_test`).
