# ORD-08.3 — Validação definitiva da fundação CRM + Ordens

Você está trabalhando no VetorOS 2 em Laravel.

O objetivo desta rodada é **encerrar definitivamente a fundação CRM + Ordens antes de iniciar ORC-01 — Orçamento da Ordem de Serviço**.

Não implemente orçamento ainda.

## Arquitetura vigente — NÃO ALTERAR

A arquitetura oficial atual é:

* single database multitenant;
* `Tenant` é o limite de isolamento SaaS;
* `Company` representa a matriz/empresa do tenant;
* `Branch` representa a unidade operacional/filial;
* **Company e Branch coexistem**;
* não consolidar novamente Branch dentro de Company;
* `Customer`, `CustomerEquipment` e `EquipmentType` são cadastros masters compartilhados no Tenant;
* Customer e equipamentos não pertencem diretamente a Company ou Branch;
* a Ordem de Serviço pertence obrigatoriamente ao Tenant, Company e Branch;
* `OrderCreationService` é o fluxo oficial de abertura da OS;
* `OrderSnapshotService` cria explicitamente o snapshot inicial;
* snapshot inicial é imutável;
* `OrderStatusService` é responsável pelas transições de status;
* `OrderAssignmentService` é responsável pela atribuição de técnico;
* `TenantSequenceService` é responsável pela numeração transacional;
* FKs compostas devem continuar preservando isolamento cross-tenant;
* não remover scopes, constraints, eventos de proteção ou validações apenas para fazer testes passarem.

Decisões antigas incompatíveis com isso estão superadas.

## Situação atual

As últimas correções trataram principalmente:

* fixtures tenant-aware;
* coerência entre Company headquarters e Branch;
* CustomerEquipment usando Customer e EquipmentType do mesmo Tenant;
* ChecklistTemplate usando EquipmentType do Tenant correto;
* identificadores de índices compatíveis com o limite do MySQL;
* FKs compostas;
* tipagem PHPStan;
* factories multitenant;
* fluxo oficial de criação de Order;
* snapshots;
* status;
* atribuição;
* visibilidade;
* sequência transacional.

As execuções automáticas anteriores do agente não conseguiram concluir as suítes porque o MySQL `vetoros2_test` em `127.0.0.1:3306` não estava disponível para o executor.

Portanto, **não tente transformar banco indisponível em erro funcional**.

## IMPORTANTE — banco de testes

Os testes devem usar exclusivamente:

```bash
APP_ENV=testing
```

e o banco configurado para testes:

```text
vetoros2_test
```

Nunca use o banco de desenvolvimento para limpar, migrar ou executar testes destrutivos.

Antes de concluir que existe problema de código, confira:

```bash
php artisan about --env=testing
php artisan config:show database
```

ou equivalente seguro, garantindo que:

```text
APP_ENV=testing
DB_DATABASE=vetoros2_test
```

Não altere `.env` de desenvolvimento apenas para satisfazer o executor.

## Estratégia desta rodada

A intenção é evitar várias passadas pequenas.

Quando os resultados dos testes estiverem disponíveis, faça uma análise global das falhas e:

1. agrupe-as por causa raiz;
2. identifique problemas comuns em factories/helpers/fixtures antes de corrigir testes individualmente;
3. corrija **todas as ocorrências da mesma classe de problema**;
4. execute ou deixe preparados os testes de regressão;
5. só depois procure a próxima causa raiz.

Não faça:

> corrigir primeiro teste → parar → entregar.

Faça:

> encontrar causa raiz → localizar todos os locais afetados → corrigir o conjunto → validar o conjunto.

## Validação obrigatória

A validação manual que será executada é:

```bash
APP_ENV=testing php artisan migrate:fresh --force

APP_ENV=testing php artisan test --compact tests/Feature/CRM

APP_ENV=testing php artisan test --compact tests/Feature/Orders

APP_ENV=testing php artisan test --compact

APP_ENV=testing vendor/bin/phpstan analyse

./vendor/bin/pint --dirty --test --format agent

git diff --check
```

### Se houver falhas

Analise a saída completa.

Não corrija somente o primeiro erro.

Procure padrões como:

* Tenant ausente;
* factory criando relacionamento em Tenant diferente;
* Company não sendo headquarters;
* Branch vinculada à Company errada;
* CustomerEquipment incompatível com Customer;
* CustomerEquipment incompatível com EquipmentType;
* ChecklistTemplate usando EquipmentType cross-tenant;
* usuário/técnico sem vínculo operacional adequado;
* criação direta de Order burlando `OrderCreationService`;
* snapshot duplicado ou criado implicitamente;
* status alterado fora de `OrderStatusService`;
* atribuição alterada fora de `OrderAssignmentService`;
* sequência criada fora de `TenantSequenceService`;
* problemas de FK composta;
* problema de enum/cast;
* problema real apontado pelo PHPStan.

Se uma causa afetar vários arquivos, corrija todos antes de entregar.

## Regras de factories e fixtures

Factories devem produzir registros válidos por padrão.

Não relaxar as invariantes dos Models para adaptar testes.

### BranchFactory

Uma `Branch` válida deve sempre:

* pertencer ao Tenant correto;
* estar vinculada a uma `Company` do mesmo Tenant;
* usar uma Company headquarters válida.

### CustomerEquipment

Um equipamento válido deve possuir:

* Customer do Tenant atual;
* EquipmentType do mesmo Tenant;
* `tenant_id` coerente nos três registros.

### ChecklistTemplate

Quando possuir `equipment_type_id`:

* EquipmentType deve pertencer ao mesmo Tenant.

### Order

Fixtures positivas devem assegurar:

* Tenant coerente;
* Company headquarters correta;
* Branch pertencente à Company;
* Customer do mesmo Tenant;
* CustomerEquipment, quando utilizado, pertencente ao Customer;
* EquipmentType compatível;
* criador e técnico, quando presentes, pertencentes ao Tenant;
* técnico compatível com a unidade quando a regra exigir.

Testes negativos podem deliberadamente violar essas relações para provar as proteções.

## Proibições

Para fazer testes passarem, não utilize:

* `withoutEvents()`;
* `withoutGlobalScopes()`, exceto onde já faça parte legítima da implementação interna de validação;
* `DB::table(...)->insert()` para contornar Models;
* remoção de FKs;
* remoção de FKs compostas;
* remoção de TenantScope;
* relaxamento de validações cross-tenant;
* troca de RESTRICT por comportamento que destrua histórico;
* remoção de testes válidos;
* alteração de expectation só para tornar o teste verde;
* mocks para substituir comportamento de banco que deve ser validado de verdade.

## Auditoria adicional

Antes de encerrar, faça busca no código operacional por criações ou alterações que possam burlar os serviços oficiais.

Procure pelo menos por:

```text
Order::create
Order::forceCreate
new Order
->status =
assigned_to
order_number
MAX(
max(
OrderSnapshot::create
```

Classifique cada ocorrência.

Factories e testes podem criar entidades diretamente quando necessário ao cenário.

Código operacional não deve possuir fluxo concorrente ao serviço oficial sem justificativa explícita.

## Critério de encerramento

ORD-08 só pode ser declarado **DONE** quando:

* CRM estiver verde;
* Orders estiver verde;
* suíte completa estiver verde;
* PHPStan estiver verde;
* Pint estiver verde;
* `git diff --check` estiver verde;

OU, caso o executor continue impossibilitado de acessar o MySQL:

* não declarar testes como PASS;
* registrar exatamente o bloqueio de infraestrutura;
* deixar todas as correções possíveis concluídas;
* fornecer os comandos manuais acima;
* não inventar resultado.

## Depois do fechamento

Se toda a fundação estiver validada, registrar no relatório:

```text
ORD-08 — DONE
CRM FOUNDATION — STABLE
ORDER FOUNDATION — STABLE
READY FOR ORC-01
```

Não implementar ORC-01 nesta rodada.

## Entrega

Atualize `resumo.md` com:

1. diferenças encontradas;
2. causas raiz das falhas;
3. arquivos corrigidos;
4. correções realizadas;
5. resultado real de cada suíte;
6. resultado do PHPStan;
7. resultado do Pint;
8. resultado de `git diff --check`;
9. auditoria dos fluxos oficiais;
10. riscos remanescentes;
11. estado final:

```text
ORD-08 — DONE
```

ou:

```text
ORD-08 — MANUAL VALIDATION REQUIRED
```

Não declare DONE sem evidência real.
