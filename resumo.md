# Resumo da execução do executar.md

Data: 2026-09-15

## Arquitetura vigente

- single database multitenant;
- Tenant é o limite de isolamento SaaS;
- Company representa a matriz/empresa do tenant;
- Branch representa a unidade operacional/filial;
- Company e Branch coexistem;
- Customer, CustomerEquipment e EquipmentType são masters compartilhados no Tenant;
- a OS pertence à Company e à Branch;
- OrderCreationService é o fluxo oficial de abertura;
- o snapshot inicial é imutável;
- status e atribuição são tratados por serviços explícitos;
- FKs compostas preservam o isolamento cross-tenant.

## ORD-08 — Correção de identificadores MySQL

### ORD-08.3 — Fixtures multitenant da suíte Orders

O executar.md estava diferente e apontava a rodada de correção definitiva das fixtures após a suíte Orders reportar 34 falhas e 4 sucessos. A causa descrita era a criação inconsistente de CustomerEquipment e Order entre tenants.

## Correções realizadas

- Contextos positivos de Orders passaram a informar explicitamente tenant_id em Company, Branch, Customer, EquipmentType, CustomerEquipment e Order.
- OrderFoundationTest, OrderLifecycleTest, OrderReceptionTest, OrderOperationalVisibilityTest e OrderSnapshotTest foram auditados e ajustados em conjunto.
- BranchFactory continua criando somente Company headquarters válida.
- Fixtures negativas continuam usando relações cross-tenant, wrong customer, wrong equipment type ou Branch inválida somente quando esse é o cenário que o teste pretende validar.
- As invariantes de CustomerEquipment e Order não foram relaxadas.
- Não foram usados withoutEvents, inserções diretas, skips, remoção de FKs ou alteração de expectations para mascarar falhas.

## Validações

- APP_ENV=testing php artisan test --compact tests/Feature/Orders — executado; 38 erros ocorreram antes das assertions por conexão indisponível com MySQL em 127.0.0.1:3306/vetoros2_test.
- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — ./vendor/bin/pint --dirty --test --format agent.
- PASS — PHP syntax check nos arquivos PHP do projeto.
- PASS — git diff --check.
- MANUAL VALIDATION REQUIRED — executar novamente Orders, CRM e a suíte completa com o banco vetoros2_test acessível.

Comandos manuais:

    APP_ENV=testing php artisan test --compact tests/Feature/Orders
    APP_ENV=testing php artisan test --compact tests/Feature/CRM
    APP_ENV=testing php artisan test --compact
    APP_ENV=testing vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

### ORD-08.2 — Fixtures tenant-aware da suíte Orders

O executar.md estava diferente e reportava uma nova rodada da suíte Orders: após a correção de Company/Branch, restavam 34 falhas e 4 sucessos, concentradas em CustomerEquipment e Order.

## Correções

- Helpers de OrderAssignmentTest, OrderFoundationTest, OrderLifecycleTest, OrderOperationalVisibilityTest, OrderReceptionTest e OrderSnapshotTest agora passam explicitamente tenant_id para Customer, EquipmentType, CustomerEquipment, Company, Branch e Order quando o cenário é positivo.
- CustomerEquipment positivo sempre usa Customer e EquipmentType do mesmo tenant.
- Os testes negativos continuam criando relações de outro tenant de forma intencional para validar as exceções.
- BranchFactory continua exigindo Company headquarters válida e não foi relaxada.
- Order permanece validando Company, Branch, Customer, CustomerEquipment, EquipmentType, criador e técnico no tenant correto.
- Nenhum withoutEvents, insert direto, remoção de TenantScope ou remoção de FK foi utilizado.

## Execução e validação

- APP_ENV=testing php artisan test --compact tests/Feature/Orders foi executado; a suíte não chegou às assertions porque o MySQL em 127.0.0.1:3306/vetoros2_test está indisponível. Resultado observado: 38 errors de conexão, 0 assertions.
- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — ./vendor/bin/pint --dirty --test --format agent.
- PASS — PHP syntax check nos arquivos alterados.
- PASS — git diff --check.
- MANUAL VALIDATION REQUIRED — repetir Orders, CRM e suíte completa em ambiente com o banco vetoros2_test acessível.

Comandos manuais:

    APP_ENV=testing php artisan test --compact tests/Feature/Orders
    APP_ENV=testing php artisan test --compact tests/Feature/CRM
    APP_ENV=testing php artisan test --compact
    APP_ENV=testing vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

### ORD-08.1 — Correção final das fixtures Company/Branch

O executar.md estava diferente e repetia a investigação das fixtures multitenant, com foco na garantia de que BranchFactory e todos os helpers produzam Company headquarters e Branch no mesmo Tenant.

## Ajustes realizados

- BranchFactory passou a usar CompanyFactory::headquarters() e, quando necessário, cria uma matriz coerente com o tenant informado.
- Helpers de Orders mantêm tenant_id explícito em Company e Branch.
- Fixtures de CRM reutilizam o EquipmentType do tenant atual ao criar ChecklistTemplate.
- Testes antigos de multitenancy que representavam filial como Company foram adaptados para Branch e branch_user.
- O teste HTTP de Company continua trabalhando com Company matriz; a tentativa cross-tenant usa uma Company de outro tenant.
- As invariantes de Branch, CompanyType, tenant e FKs não foram removidas ou relaxadas.

## Validação executada

- APP_ENV=testing foi conferido com DB_DATABASE=vetoros2_test.
- APP_ENV=testing php artisan test --compact tests/Feature/Orders foi executado; 38 testes não chegaram às assertions porque RefreshDatabase não conseguiu conectar ao MySQL em 127.0.0.1:3306. Resultado: 0 passed, 38 errors de infraestrutura.
- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — ./vendor/bin/pint --dirty --test --format agent.
- PASS — PHP syntax check nos arquivos PHP do projeto.
- PASS — git diff --check.
- BLOCKED — vendor/bin/phpstan analyse e tentativa --debug: o executor falhou no servidor TCP interno, sem novo diagnóstico de código.
- MANUAL VALIDATION REQUIRED — repetir a suíte CRM, Orders e completa quando o MySQL de vetoros2_test estiver acessível.

Comandos manuais:

    APP_ENV=testing php artisan migrate:fresh --force
    APP_ENV=testing php artisan test --compact tests/Feature/CRM
    APP_ENV=testing php artisan test --compact tests/Feature/Orders
    APP_ENV=testing php artisan test --compact
    APP_ENV=testing vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

### ORD-08.1 — Fechamento da fundação CRM + Ordens

O executar.md mudou para exigir uma rodada completa de validação e correção. A configuração foi conferida: APP_ENV=testing, conexão mysql, database vetoros2_test, usuário root sem senha registrada no relatório.

## Correções realizadas

- Confirmado que migrations e fixtures não devem representar filiais como Company com type=branch; os testes de multitenancy foram ajustados para usar Branch.
- CompanyAccessTest passou a validar acesso à matriz via Company e acesso operacional à unidade via branch_user.
- CompanyContextHttpTest passou a trocar o contexto pela Company matriz.
- CompanyHierarchyTest passou a validar Branch vinculada a uma Company headquarters e rejeitar uma Branch apontando para outra Branch.
- Preservadas as invariantes de CompanyType, tenant, Company/Branch, CustomerEquipment/Customer e EquipmentType/ChecklistTemplate.
- O fluxo operacional continua usando OrderCreationService; não foram encontrados bypasses por Order::forceCreate, new Order ou MAX(order_number).
- As decisões históricas superadas permanecem identificadas: Branch consolidada em Company está SUPERADA PELO ORD-04 e snapshot por Order::created está SUPERADA PELO ORD-06.

## Validações reais e bloqueios

- CRM — executado, mas bloqueado antes das assertions por SQLSTATE[HY000] [2002] ao conectar em 127.0.0.1:3306/vetoros2_test.
- Orders — não repetido após a mesma falha de infraestrutura do banco de testes.
- Suíte completa — não iniciada, pois depende do mesmo MySQL indisponível.
- Migrations — o executar.md informa PASS anterior no MySQL real; não foram executadas novamente nesta rodada para não usar o banco de desenvolvimento.
- PHPStan — a análise normal e a tentativa com --debug foram investigadas; o executor apresentou falha/ausência de saída do servidor TCP interno. Não foi reduzido o nível nem adicionados ignores.
- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — ./vendor/bin/pint --dirty --test --format agent.
- PASS — PHP syntax check em app, migrations, factories, routes e tests.
- PASS — git diff --check.

O MySQL foi comprovadamente configurado para vetoros2_test, porém indisponível. Por isso CRM, Orders, suíte completa e PHPStan não foram declarados como PASS.

Comandos para validação manual:

    APP_ENV=testing php artisan migrate:fresh --force
    APP_ENV=testing php artisan test --compact tests/Feature/CRM
    APP_ENV=testing php artisan test --compact tests/Feature/Orders
    APP_ENV=testing php artisan test --compact
    APP_ENV=testing vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

### ORD-08 — Fechamento completo da fundação

O executar.md estava diferente e exigia corrigir as fixtures, os tipos estáticos e os pequenos problemas de infraestrutura de código encontrados após a validação das migrations.

Correções realizadas:

- Company/Branch e ChecklistTemplate/EquipmentType foram estabilizados nas fixtures com tenant e tipo headquarters explícitos.
- BelongsToTenant e TenantScope receberam generics compatíveis.
- Models com atributos Eloquent usados nas invariantes receberam PHPDoc de propriedades, sem criar propriedades PHP duplicadas.
- Branch passou a normalizar CompanyType antes da comparação.
- OrderStatus mantém representação enum coerente para o PHPStan.
- OrderSnapshot deixou de chamar helper privado via static e recebeu tipagem dos nomes de atributos.
- UserFactory::withTwoFactor() agora retorna uma factory válida.
- Migration de tenants recebeu return type void.
- routes/web.php passou a usar expressão booleana explícita em abort_unless.
- O índice curto criado no ORD-08 anterior e todas as FKs multitenant foram preservados.

Decisões históricas superadas:

- A decisão antiga de consolidar Branch em Company está SUPERADA PELO ORD-04.
- A decisão antiga de criar snapshot por Order::created está SUPERADA PELO ORD-06.
- A arquitetura vigente está registrada no topo deste documento.

Validação informada no executar.md:

- migrations no MySQL real: PASS antes deste ciclo;
- CRM antes das fixtures: 6 passed / 1 failed;
- Orders antes das fixtures: 3 passed / 35 failed;
- falhas eram causadas por RefreshDatabase/fixtures e foram tratadas no código.

Validação realizada nesta execução:

- PASS — ./vendor/bin/pint --dirty --format agent;
- PASS — ./vendor/bin/pint --dirty --test --format agent;
- PASS — php -l nos arquivos PHP alterados;
- PASS — git diff --check;
- BLOCKED — vendor/bin/phpstan analyse: o executor falhou ao abrir o servidor TCP interno e não produziu resultado analisável;
- MANUAL VALIDATION REQUIRED — MySQL em 127.0.0.1:3306 não está acessível nesta execução; não foi possível confirmar CRM/Orders nem o banco vetoros2_test.

Comandos para validação final manual:

    php artisan migrate:fresh --force
    php artisan test --compact tests/Feature/CRM
    php artisan test --compact tests/Feature/Orders
    php artisan test --compact
    vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

### ORD-08 — Correção das fixtures/factories multitenant

O executar.md estava diferente e reportava a etapa seguinte após as migrations passarem no MySQL real. A falha remanescente era de dados de teste inconsistentes, não do CRM ou das regras de domínio.

Correções:

- BranchFactory passou a usar CompanyFactory::headquarters() por padrão.
- Helpers de OrderAssignmentTest, OrderFoundationTest, OrderLifecycleTest, OrderOperationalVisibilityTest, OrderReceptionTest e OrderSnapshotTest passaram a criar Company explicitamente como headquarters antes da Branch.
- CrmBaseTest passou a criar o EquipmentType de cada tenant e reutilizar seu ID ao criar o ChecklistTemplate válido.
- A factory de ChecklistTemplate continua produzindo relacionamento no tenant atual; cenários cross-tenant permanecem explícitos nos testes negativos.
- Nenhum Model foi flexibilizado, nenhum withoutEvents foi usado e nenhuma inserção direta foi adicionada para contornar invariantes.

Resultado informado pela validação manual anterior:

- migrations: PASS no MySQL real;
- CRM: 6 passed / 1 failed antes da correção;
- Orders: 3 passed / 35 failed antes da correção, predominantemente por fixtures de Branch inválidas.

Validações executadas nesta etapa:

- PASS — ./vendor/bin/pint --dirty --format agent;
- PASS — ./vendor/bin/pint --dirty --test --format agent;
- PASS — php -l nos arquivos PHP alterados;
- PASS — git diff --check;
- MANUAL VALIDATION REQUIRED — repetir migrations e testes no MySQL real para confirmar os resultados após as correções.

Comandos manuais:

    php artisan migrate:fresh --force
    php artisan test --compact tests/Feature/CRM
    php artisan test --compact tests/Feature/Orders
    vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check


O executar.md estava diferente e reportava uma falha real no MySQL: o índice automático de order_checklist_items usava o nome order_checklist_items_tenant_id_order_checklist_id_sort_order_index, com 68 caracteres, acima do limite de 64.

O índice foi mantido com as mesmas colunas e recebeu o nome explícito oci_tenant_checklist_sort_idx, curto, legível e sem colisão. Nenhuma funcionalidade ou constraint multitenant foi removida.

Foi feita auditoria preventiva das migrations CRM e ORD, incluindo índices, uniques e FKs simples/compostas. Os demais identificadores avaliados permanecem dentro do limite ou usam nomes explícitos adequados.

O erro anterior de tests/Feature/CRM/CrmBaseTest com 7 failed e 0 assertions é consequência da falha do RefreshDatabase durante as migrations, não uma falha funcional do CRM.

Validações:

- PASS — auditoria dos identificadores;
- PASS — Pint;
- PASS — PHP syntax check;
- PASS — git diff --check;
- MANUAL VALIDATION REQUIRED — php artisan migrate:fresh --force e testes devem ser confirmados no MySQL real.

Comandos manuais:

    php artisan migrate:fresh --force
    php artisan test --compact tests/Feature/CRM
    php artisan test --compact tests/Feature/Orders
    vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

## Diferença identificada

O executar.md atual está diferente da versão anterior do Git. A tarefa anterior de auditoria global foi substituída pela implementação do primeiro bloco estrutural do CRM do VetorOS 2.

## Estrutura criada

Foi implementado o bloco:

- customers
- equipment_types
- customer_equipments
- checklist_templates
- checklist_template_items

### Customers

- cadastro PF/PJ com type=individual|company;
- CPF, CNPJ, CEP, telefones e WhatsApp normalizados para somente dígitos;
- number do endereço como string;
- UNIQUE(tenant_id, customer_number);
- índices de busca por nome, documento, telefone, WhatsApp e e-mail;
- sem company_id, pois o cliente é cadastro mestre compartilhado entre matriz e filiais.

### Equipment types

- separação entre catálogo de tipos e equipamentos físicos;
- legado equipment.equipment_number → equipment_type_number;
- legado equipment.equipment → name;
- legado equipment.chart → uses_chart;
- UNIQUE(tenant_id, equipment_type_number).

### Customer equipments

- equipamento físico vinculado a customer_id e equipment_type_id;
- identificadores serial_number, imei e asset_tag indexados;
- UNIQUE(tenant_id, equipment_number);
- sem company_id, pois pertence ao cadastro mestre do tenant;
- não inclui senha, acessórios, defeito, diagnóstico, solução ou estado de conservação.

### Checklist templates

- template genérico ou vinculado a equipment_type_id;
- tipos evolutivos como entry, exit, preventive, installation e field_service podem ser armazenados;
- itens com input_type, obrigatoriedade e ordenação;
- relação ChecklistTemplate::items() ordenada por sort_order.

## Multitenancy e integridade

Os quatro Models diretamente pertencentes ao tenant usam BelongsToTenant e TenantScope, seguindo o padrão existente no VetorOS 2. ChecklistTemplateItem herda o tenant por meio do template.

Além da validação nos Models, as migrations criam chaves estrangeiras compostas:

- customer_equipments(tenant_id, customer_id) → customers(tenant_id, id);
- customer_equipments(tenant_id, equipment_type_id) → equipment_types(tenant_id, id);
- checklist_templates(tenant_id, equipment_type_id) → equipment_types(tenant_id, id).

Assim, uma relação cross-tenant é rejeitada tanto na aplicação quanto pelo banco. O acesso sem tenant atual continua bloqueado pelo scope oficial.

## Arquivos alterados/criados

- database/migrations/2026_09_15_114606_create_crm_base_tables.php
- app/Models/Customer.php
- app/Models/EquipmentType.php
- app/Models/CustomerEquipment.php
- app/Models/ChecklistTemplate.php
- app/Models/ChecklistTemplateItem.php
- app/Models/Tenant.php — relações CRM
- database/factories/CustomerFactory.php
- database/factories/EquipmentTypeFactory.php
- database/factories/CustomerEquipmentFactory.php
- database/factories/ChecklistTemplateFactory.php
- database/factories/ChecklistTemplateItemFactory.php
- tests/Feature/CRM/CrmBaseTest.php

Não foram criados seeders, controllers, UI ou tabelas de histórico/contatos/endereço. Ordens de Serviço, estoque, vendas, financeiro e agenda não foram alterados.

## Compatibilidade com o legado

| VetorOS 1 | VetorOS 2 | Transformação |
|---|---|---|
| customers.cpfcnpj | customers.cpf / customers.cnpj | 11 dígitos para CPF; 14 para CNPJ; remover máscara |
| customers.birth | customers.birth_date | renomear |
| customers.zipcode | customers.zip_code | renomear e normalizar |
| customers.contactname | customers.contact_name | renomear |
| customers.contactphone | customers.contact_phone | renomear e normalizar |
| customers.number inteiro | customers.number string | preservar valores como 12A, SN e KM 4 |
| equipment | equipment_types | transformar categoria em tipo |
| equipment.equipment_number | equipment_types.equipment_type_number | renomear |
| equipment.equipment | equipment_types.name | renomear |
| equipment.chart | equipment_types.uses_chart | renomear |
| checklists | checklist_templates + itens | substituir estrutura fixa por templates |

O arquivo dump-legado-vetoros1.sql foi usado como referência e confirma os campos legados de customers/equipment. O dump não foi importado e vetoros1 não foi alterado.

## Testes criados

Cobertura adicionada para:

- normalização de CPF e CNPJ;
- unicidade de customer number no tenant;
- mesmo customer number em tenants diferentes;
- isolamento de Customer, EquipmentType e ChecklistTemplate;
- rejeição de CustomerEquipment cross-tenant;
- rejeição de ChecklistTemplate ligado a tipo de outro tenant;
- ordenação de itens de checklist.

## Validações executadas

- php artisan make:model: concluído para os cinco Models e factories.
- ./vendor/bin/pint --dirty --format agent: passou e formatou os arquivos alterados.
- php -l nos PHP alterados: passou.
- ./vendor/bin/pint --dirty --test --format agent: passou.
- git diff --check: passou.
- php artisan migrate:fresh --force: bloqueado porque MySQL 127.0.0.1:3306/vetoros2 está indisponível.
- SQLite em memória: bloqueado porque o PHP não possui driver SQLite.
- php artisan test --compact tests/Feature/CRM/CrmBaseTest.php: não iniciou; MySQL vetoros2_test indisponível.
- PHPStan: não iniciou por falha do servidor TCP interno do executor (TcpServer.php).

Os testes não foram declarados como verdes porque não puderam executar contra um banco disponível.

## Pendências do próximo marco — ORD-01

Não implementadas neste marco:

- orders.customer_id;
- orders.customer_equipment_id;
- orders.equipment_type_id;
- orders.company_id;
- checklists operacionais preenchidos em uma Ordem de Serviço;
- regras de unidade operacional matriz/filial.

---

# ORD-01 — Fundação Operacional da Ordem de Serviço

## Estrutura criada

Foi implementada a primeira fundação operacional:

- orders;
- order_status_history;
- Order;
- OrderStatusHistory;
- OrderStatus e OrderPriority;
- OrderFactory e OrderStatusHistoryFactory;
- relações reversas em Tenant, Company, Customer, EquipmentType, CustomerEquipment e User;
- testes em tests/Feature/Orders/OrderFoundationTest.php.

## Decisões

- orders.tenant_id e orders.company_id são obrigatórios.
- company_id identifica a matriz ou filial responsável pela operação.
- customer_id e equipment_type_id são obrigatórios.
- customer_equipment_id é nullable para permitir atendimentos sem equipamento físico.
- A numeração usa UNIQUE(tenant_id, order_number) e não usa MAX + 1.
- O status foi organizado em strings compatíveis com o legado: open, cancelled, budget_generated, budget_approved, budget_rejected, in_progress, completed, not_executed, waiting_customer e delivered.
- A prioridade usa low, normal, high e urgent, com default normal.
- reported_issue, technical_diagnosis e solution ficam na OS.
- orders usa SoftDeletes.
- O histórico registra from_status, to_status, changed_by, changed_at e note, e é imutável após criação.

## Integridade multitenant

Order e OrderStatusHistory usam BelongsToTenant e TenantScope. Os Models validam que company, customer, customer equipment, equipment type e usuários pertencem ao tenant atual.

Também foram criadas FKs compostas:

- orders(tenant_id, company_id) → companies(tenant_id, id);
- orders(tenant_id, customer_id) → customers(tenant_id, id);
- orders(tenant_id, customer_equipment_id) → customer_equipments(tenant_id, id);
- orders(tenant_id, equipment_type_id) → equipment_types(tenant_id, id);
- orders(tenant_id, created_by/assigned_to) → users(tenant_id, id);
- order_status_history(tenant_id, order_id) → orders(tenant_id, id);
- order_status_history(tenant_id, changed_by) → users(tenant_id, id).

Além disso, o Model rejeita uma OS cujo customer_equipment_id pertença a outro cliente, mesmo dentro do mesmo tenant.

## Compatibilidade VetorOS 1 → VetorOS 2

| VetorOS 1 | VetorOS 2 | Ação |
|---|---|---|
| orders.tenant_id nullable | orders.tenant_id obrigatório | normalizar |
| orders.customer_id | orders.customer_id | manter, agora obrigatório |
| orders.equipment_id | orders.customer_equipment_id | substituir pelo equipamento físico do cliente |
| equipment.equipment_number | equipment_types.equipment_type_number | usar catálogo de tipos |
| orders.user_id | orders.created_by | renomear semanticamente |
| técnico implícito/legado | orders.assigned_to | normalizar responsável atual |
| orders.service_status tinyint | orders.status string/enum | converter semântica legada para estados nomeados |
| orders.defect | orders.reported_issue | renomear |
| technician_diagnosis | technical_diagnosis | renomear |
| technician_solution/services_performed | solution | consolidar no domínio básico |
| delivery_date | delivered_at | normalizar |
| order_status_history.status tinyint | from_status/to_status | reconstruir transições |
| password, accessories, state_conservation | marcos ORD-02+ | não copiar para orders |
| orçamento, peças, pagamentos, fiscal, garantia e mensagens | marcos futuros | não implementar neste marco |

Os status numéricos observados no legado incluem abertura, cancelamento, orçamento gerado/aprovado/rejeitado, reparo, concluído, não executado, cliente avisado e entregue. A tabela nova preserva esses conceitos com nomes explícitos e não replica os status de agenda.

## Testes criados

OrderFoundationTest cobre:

- isolamento entre tenants;
- ausência de acesso sem tenant por meio do scope;
- company, customer, customer equipment, equipment type e histórico cross-tenant;
- unicidade de order_number por tenant e reutilização em tenants diferentes;
- rejeição de equipamento pertencente a outro cliente;
- relacionamentos principais;
- ordenação cronológica do histórico;
- equipamento físico nullable.

## Validações

- PASS — ./vendor/bin/pint --dirty --format agent;
- PASS — ./vendor/bin/pint --dirty --test --format agent;
- PASS — php -l nos PHP alterados;
- PASS — git diff --check;
- BLOCKED — php artisan migrate:fresh --force: MySQL 127.0.0.1:3306/vetoros2 indisponível;
- BLOCKED — php artisan test --compact tests/Feature/Orders: MySQL vetoros2_test indisponível; 9 testes não iniciaram;
- BLOCKED — PHPStan: servidor TCP interno do executor não iniciou;
- Não houve alteração em interface, controllers, requests, rotas, orçamento, estoque, vendas, financeiro ou agenda.

Os testes não foram declarados verdes porque não executaram contra um banco disponível.

## Pendências para ORD-02

Não implementar ainda:

- order_equipment_accessories;
- order_equipment_conditions;
- order_checklists;
- order_checklist_items;
- order_media;
- snapshot de cliente/equipamento;
- máquina completa de transição de status;
- regras de visibilidade matriz/filial.

---

# ORD-02 — Recepção Operacional da Ordem de Serviço

## Estrutura criada

Implementadas as entidades:

- order_equipment_accessories;
- order_equipment_conditions;
- order_checklists;
- order_checklist_items;
- order_media.

Também foram criados os Models e factories correspondentes e relações no Model Order:
equipmentAccessories(), equipmentConditions(), checklists() e media().

## Decisões arquiteturais

- Acessórios e condições são registros filhos da OS, com texto livre e sem catálogo global.
- A condição possui severity nullable, permitindo evolução sem impor enum desnecessário.
- ChecklistTemplate é apenas o modelo reutilizável; OrderChecklist guarda name e type como snapshot.
- OrderChecklistItem copia label, input_type, is_required e sort_order do template.
- OrderChecklist::createFromTemplate() materializa o checklist e seus itens em uma transação.
- Alterações futuras no template não alteram checklists já aplicados.
- Entidades filhas usam tenant_id e order_id; não duplicam company_id, que é derivado da OS.
- Senhas não foram implementadas: o legado usa password como dado de acesso operacional, mas não há solução segura/necessidade suficiente para armazená-la neste marco.
- order_media foi implementada porque o legado possui tabela images e uso operacional real de imagens da OS. O armazenamento físico e upload continuam fora do escopo.

## Compatibilidade VetorOS 1 → VetorOS 2

| VetorOS 1 | VetorOS 2 | Ação |
|---|---|---|
| orders.accessories | order_equipment_accessories.name/quantity/notes | mover para entidade filha |
| orders.state_conservation | order_equipment_conditions.description/severity/notes | mover para entidade filha |
| checklists.checklist | checklist_templates + checklist_template_items | substituir estrutura fixa por template materializado |
| checklists.equipment_id | checklist_templates.equipment_type_id | normalizar para tipo de equipamento |
| images.order_id | order_media.order_id | mover para mídia operacional |
| images.filename | order_media.path/original_name | separar caminho e nome original |
| orders.password | pendência futura | não copiar nem armazenar em texto puro |
| technician_checklist_items | order_checklists/order_checklist_items | tratar em checklist operacional futuro |

## Integridade e isolamento

Todos os Models diretamente pertencentes ao tenant usam BelongsToTenant e TenantScope. Os Models validam o tenant da OS, template, usuários e entidades pai antes de salvar.

Foram adicionadas FKs compostas para impedir vínculos cross-tenant entre:

- acessórios/condições/media e orders;
- order_checklists e orders/checklist_templates/users;
- order_checklist_items e order_checklists;
- media e users.

## Testes criados

OrderReceptionTest cobre:

- múltiplos acessórios e condições na mesma OS;
- isolamento de registros de recepção entre tenants;
- aplicação de template e cópia dos itens;
- preservação do snapshot após alteração do template;
- rejeição de template de outro tenant;
- rejeição de filho ligado a OS de outro tenant;
- associação de mídia operacional à OS.

## Validações

- PASS — ./vendor/bin/pint --dirty --format agent;
- PASS — ./vendor/bin/pint --dirty --test --format agent;
- PASS — php -l nos arquivos PHP alterados;
- PASS — git diff --check;
- MANUAL VALIDATION REQUIRED — php artisan migrate:fresh --force: MySQL 127.0.0.1:3306/vetoros2 indisponível;
- MANUAL VALIDATION REQUIRED — php artisan test --compact tests/Feature/Orders: MySQL vetoros2_test indisponível; 15 testes não iniciaram;
- PHPStan não foi repetido, pois o executor já havia demonstrado falha do servidor TCP interno;
- Não houve alteração em UI, controllers, requests, rotas, orçamento, peças, estoque, pagamentos, caixa, fiscal, garantia, contratos ou agenda.

## VALIDAÇÃO MANUAL

Executar no ambiente com banco disponível:

    php artisan migrate:fresh --force
    php artisan test --compact tests/Feature/CRM
    php artisan test --compact tests/Feature/Orders
    vendor/bin/phpstan analyse

## Riscos e pendências para ORD-03

- Validar no MySQL as FKs compostas e a migration completa.
- Definir política segura para senha temporária, caso ainda seja necessária.
- Definir permissões e visibilidade operacional entre matriz e filiais.
- Avaliar snapshots de cliente/equipamento.
- Criar fluxos de checklist e mídia somente nos próximos marcos, sem ampliar a tabela orders.

---

# ORD-03 — Ciclo Operacional da Ordem de Serviço

## Estrutura criada

- app/Services/OrderStatusService.php;
- app/Enums/OrderStatus.php e app/Enums/OrderPriority.php já existentes, revisados;
- proteção no Model Order contra alteração direta de status;
- testes em tests/Feature/Orders/OrderLifecycleTest.php;
- relações Order com statusHistory, creator e assignee mantidas;
- nenhuma tabela paralela de Branch foi criada.

## Máquina de estados

Transições permitidas:

- open → budget_generated, in_progress, waiting_customer, not_executed, cancelled;
- budget_generated → budget_approved, budget_rejected, waiting_customer, cancelled;
- budget_approved → in_progress, cancelled;
- budget_rejected → open, not_executed, cancelled;
- waiting_customer → open, budget_generated, in_progress, not_executed, cancelled;
- in_progress → waiting_customer, completed, not_executed, cancelled;
- completed → delivered;
- not_executed → delivered;
- delivered e cancelled são estados finais.

## Regras operacionais

OrderStatusService executa a transição em transaction, bloqueia a OS com lockForUpdate, valida o ator no tenant, rejeita transições inválidas e cria exatamente um registro em order_status_history.

As transições preenchem automaticamente:

- in_progress → started_at;
- completed → completed_at;
- delivered → delivered_at;
- cancelled → cancelled_at.

Cancelamento exige note textual. O status não pode ser alterado diretamente com order->status = ...; a alteração deve passar pelo serviço. O histórico é append-only e imutável.

## Company e Branch

O executar.md desta etapa descreve Company e Branch como conceitos distintos, mas o estado real do VetorOS 2 mantém a decisão arquitetural anterior: a migração de consolidação transforma branches em companies e remove as tabelas paralelas. Por isso:

- Order continua usando company_id;
- não foi adicionado branch_id;
- não foi criado Model Branch;
- clientes e equipamentos permanecem cadastros mestres do Tenant;
- a visibilidade futura matriz/filial deve usar a hierarquia oficial de Company.

Essa divergência foi preservada e documentada para evitar uma alteração destrutiva ou a reintrodução de arquitetura concorrente.

## Compatibilidade VetorOS 1 → VetorOS 2

| VetorOS 1 | VetorOS 2 | Transformação |
|---|---|---|
| service_status numérico | status string/enum | converter códigos para estados nomeados |
| 1 — Ordem aberta | open | manter semântica |
| 2 — Cancelada | cancelled | operação explícita com motivo |
| 3/4/5 — orçamento | budget_generated/budget_approved/budget_rejected | preservar ciclo sem implementar orçamento |
| 6 — reparo | in_progress | normalizar |
| 7 — serviço concluído | completed | separar conclusão técnica de entrega |
| 8 — não executado | not_executed | manter |
| 9 — cliente avisado | waiting_customer | organizar como espera |
| 10 — entregue | delivered | estado final |
| orders.user_id | orders.created_by | usuário que abriu a OS |
| responsável técnico legado | orders.assigned_to | responsável atual |
| technician_diagnosis | technical_diagnosis | renomear |
| technician_solution/services_performed | solution | consolidar no domínio básico |
| delivery_date | delivered_at | normalizar |
| order_status_history.status | from_status/to_status | preservar transições com estados explícitos |

Não foram copiados orçamento, peças, pagamentos, fiscal, garantia, mensagens, fotos físicas ou outros módulos futuros para a tabela orders.

## Testes criados

OrderLifecycleTest cobre:

- fluxos válidos open → in_progress → completed → delivered;
- fluxo de orçamento até execução;
- transição inválida sem mudança de estado/histórico;
- bloqueio de alteração direta;
- cancelamento exigindo motivo;
- cancelamento como estado final;
- ator de outro tenant;
- timestamps operacionais;
- histórico com from_status, to_status, changed_by e ordenação.

## Validações

- PASS — ./vendor/bin/pint --dirty --format agent;
- PASS — ./vendor/bin/pint --dirty --test --format agent;
- PASS — php -l nos arquivos PHP alterados;
- PASS — git diff --check;
- MANUAL VALIDATION REQUIRED — php artisan migrate:fresh --force: MySQL 127.0.0.1:3306/vetoros2 indisponível;
- MANUAL VALIDATION REQUIRED — php artisan test --compact tests/Feature/Orders: MySQL vetoros2_test indisponível; 22 testes não iniciaram;
- PHPStan não foi repetido, pois o executor já havia demonstrado falha do servidor TCP interno.

Não foram declarados testes verdes sem execução real.

## VALIDAÇÃO MANUAL

Executar em ambiente com MySQL disponível:

    php artisan migrate:fresh --force
    php artisan test --compact tests/Feature/CRM
    php artisan test --compact tests/Feature/Orders
    php artisan test --compact tests/Feature/Orders/OrderLifecycleTest.php
    vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

---

# ORD-07 — Numeração Transacional e Fechamento da Fundação

## Auditoria

O mecanismo anterior de order_number existia apenas na OrderFactory e usava fake()->unique()->numberBetween(1, 999999). Não havia contador oficial no domínio, nem MAX(order_number) + 1 ou incremento sem lock no código operacional. A constraint UNIQUE(tenant_id, order_number) foi preservada.

## Implementação

- Criada migration database/migrations/2026_09_15_165355_create_tenant_sequences_table.php.
- Criado TenantSequence com BelongsToTenant.
- Criado TenantSequenceService::next(string $key), exigindo tenant atual, criando a sequência quando necessário, usando transaction e lockForUpdate e incrementando atomicamente.
- Criada UNIQUE(tenant_id, key), permitindo a mesma key em tenants diferentes e keys independentes no mesmo tenant.
- Integrado OrderCreationService: quando order_number não é informado, obtém o próximo valor da key orders na mesma transaction da abertura.
- Em caso de rollback da abertura, o incremento também é revertido pelo banco; o número pode ser reutilizado. Essa decisão privilegia consistência transacional e não promete sequência sem lacunas.
- A arquitetura vigente, Company/Branch, snapshots, recepção, status e atribuição não foi alterada.

## Auditoria do fluxo

O único Order::create encontrado fora de factories/testes está dentro do OrderCreationService. Não foram encontrados Order::forceCreate ou new Order em código operacional concorrente.

## Testes criados

- primeiro número igual a 1;
- números subsequentes por key;
- keys diferentes no mesmo tenant;
- mesma key isolada entre tenants;
- ausência de tenant ou key inválida;
- integração do OrderCreationService com numeração transacional.

## Validações

- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — ./vendor/bin/pint --dirty --test --format agent.
- PASS — php -l nos arquivos PHP alterados.
- PASS — git diff --check.
- MANUAL VALIDATION REQUIRED — migrations e testes de banco não foram executados novamente; o MySQL continua indisponível no executor, conforme tentativa anterior.

Comandos para validação manual:

    php artisan migrate:fresh --force
    php artisan test --compact tests/Feature/CRM
    php artisan test --compact tests/Feature/Orders
    vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

A fundação da OS está pronta para iniciar ORC-01 — Orçamento da Ordem de Serviço, condicionada à validação manual das migrations e testes no MySQL.

---

# ORD-06 — Aplicação Operacional da Ordem de Serviço

## Diferenças encontradas

O executar.md mudou do ajuste isolado de FKs para a consolidação do fluxo operacional. Os requisitos novos determinam que a criação de snapshots seja explícita, removem o efeito colateral de Order::created e pedem uma abertura transacional com recepção opcional.

## Ajustes arquiteturais

- Removida a FK simples order_snapshots.order_id; permanece exclusivamente a FK composta (tenant_id, order_id) para orders(tenant_id, id), com UNIQUE(tenant_id, order_id).
- Removida a criação automática de snapshot pelo evento Order::created.
- Criado OrderSnapshotService como ponto explícito para montagem e persistência do snapshot.
- Consolidado OrderCreationService para receber entrada estruturada e executar em transaction.
- O fluxo oficial valida o tenant atual, cria a Order, cria o snapshot, registra acessórios e condições opcionais, materializa checklist opcional e aplica técnico inicial compatível.
- A atribuição inicial reutiliza OrderAssignmentService e não altera a máquina de estados.
- Order agora exige que CustomerEquipment, quando informado, pertença ao Customer e seja compatível com o EquipmentType da OS.
- Company, Branch, clientes masters, recepção, status e atribuição mantêm a arquitetura dos marcos anteriores.

## Arquivos alterados ou criados

- database/migrations/2026_09_15_145854_create_order_snapshots_table.php
- app/Models/Order.php
- app/Models/OrderSnapshot.php
- app/Services/OrderCreationService.php
- app/Services/OrderSnapshotService.php
- tests/Feature/Orders/OrderSnapshotTest.php

## Testes ajustados

OrderSnapshotTest passou a criar snapshots pelo OrderSnapshotService, sem depender de evento Eloquent, e cobre:

- abertura com e sem equipamento físico;
- preservação após alterações nos masters;
- rastreabilidade dos IDs;
- snapshot cross-tenant;
- Company/Branch;
- duplicidade;
- update e delete proibidos;
- rollback do caminho oficial quando a validação da OS falha.

## Validações

- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — ./vendor/bin/pint --dirty --test --format agent.
- PASS — php -l nos arquivos PHP alterados.
- PASS — git diff --check.
- MANUAL VALIDATION REQUIRED — testes e migrations dependentes de MySQL não foram repetidos; a tentativa anterior confirmou indisponibilidade de 127.0.0.1:3306.

Comandos para validação manual:

    php artisan migrate:fresh --force
    php artisan test --compact tests/Feature/CRM
    php artisan test --compact tests/Feature/Orders
    php artisan test --compact tests/Feature/Orders/OrderSnapshotTest.php
    vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

Não foram declaradas migrations ou testes como verdes sem execução real no MySQL.

---

# Correção de FKs compostas multitenant

## Causa raiz

O executar.md foi alterado para registrar uma falha real de migration. A causa foi o uso de ON DELETE SET NULL em FKs compostas que incluíam tenant_id NOT NULL. No MySQL, ao excluir o registro referenciado, SET NULL tenta anular todas as colunas da FK; como tenant_id é obrigatório, a constraint fica incorretamente formada.

## Ocorrências auditadas e decisões

Todas as migrations dos marcos CRM e ORD-01 até ORD-04 foram auditadas:

- checklist_templates(tenant_id, equipment_type_id) → equipment_types: alterado para RESTRICT. O EquipmentType não pode ser removido enquanto houver template dependente.
- order_checklists(tenant_id, checklist_template_id) → checklist_templates: alterado para RESTRICT, preservando o histórico de recepção.
- orders(tenant_id, created_by) e orders(tenant_id, assigned_to) → users: alterados para RESTRICT. Usuários referenciados por OS não podem ser apagados silenciosamente.
- order_status_history(tenant_id, changed_by) → users: alterado para RESTRICT, pois o histórico não deve perder o autor.
- order_reception e order_media, nos vínculos compostos com usuários/templates: alterados para RESTRICT pelo mesmo motivo histórico.
- order_assignment_history(tenant_id, from_user_id/to_user_id/changed_by) → users: alterados para RESTRICT, preservando a auditoria de atribuição.

As colunas de IDs que já eram nullable continuam nullable para representar ausência de vínculo na criação. A exclusão do registro pai, porém, é bloqueada quando houver dependência. FKs compostas continuam sendo usadas; nenhuma proteção cross-tenant foi enfraquecida. Relações Company/Branch e os cadastros mestres permanecem inalterados.

Não foram encontrados outros nullOnDelete, set null ou SET NULL nas migrations auditadas após a correção.

## Arquivos alterados

- database/migrations/2026_09_15_114606_create_crm_base_tables.php
- database/migrations/2026_09_15_122033_create_orders_and_order_status_history_tables.php
- database/migrations/2026_09_15_125736_create_order_reception_tables.php
- database/migrations/2026_09_15_141549_add_branches_and_order_assignments.php
- resumo.md

## Validações

- PASS — auditoria textual das migrations com rg; nenhuma ocorrência SET NULL permaneceu.
- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — ./vendor/bin/pint --dirty --test --format agent.
- PASS — php -l nas cinco migrations alteradas.
- PASS — git diff --check.
- MANUAL VALIDATION REQUIRED — migrations e testes ainda precisam ser executados em MySQL real. A tentativa anterior de php artisan migrate:fresh --force falhou porque 127.0.0.1:3306 estava indisponível.

Comandos de validação manual:

    php artisan migrate:fresh --force
    php artisan test --compact tests/Feature/CRM
    php artisan test --compact tests/Feature/Orders
    vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check

## Riscos e pendências para ORD-04

- Validar no MySQL as FKs compostas e as transições completas.
- Definir política de visibilidade entre matriz e filiais usando Company.
- Criar operação auditada de atribuição de técnico, se necessária.
- Definir máquina de estados completa quando orçamento, estoque e financeiro forem implementados.
- Avaliar snapshots operacionais e eventos específicos sem criar tabela genérica prematuramente.

---

# ORD-04 — Unidade Operacional, Visibilidade e Atribuição de Técnico

## Resultado

O executar.md estava diferente do estágio anterior: passou a exigir a separação explícita entre Company (matriz) e Branch (unidade operacional), além de visibilidade por unidade e atribuição auditada de técnico. A implementação foi executada e este relatório foi atualizado.

## Implementado

- Criada a tabela branches, vinculada ao tenant e à Company matriz por chave composta.
- Criada a tabela branch_user para acesso de usuários às unidades.
- Adicionado orders.branch_id, com validação de tenant e de pertencimento à mesma matriz.
- Adicionada migração de compatibilidade: Companies legadas com type=branch são convertidas em Branches e seus acessos company_user são reconstruídos em branch_user.
- Criadas as relações Company::operationalBranches, Tenant::branches, User::branches e Order::branch.
- Criada order_assignment_history com from_user_id, to_user_id, changed_by, changed_at e note.
- O histórico de atribuição é imutável contra update e delete.
- Criado OrderVisibilityService:
  - usuário com acesso à matriz vê as OS da própria matriz e de suas unidades;
  - usuário vinculado à unidade vê somente as OS daquela unidade;
  - root admin mantém a visibilidade integral dentro do tenant atual;
  - usuário sem vínculo não recebe OS.
- Criado OrderAssignmentService com transaction, lockForUpdate, validação de tenant/matriz/unidade e compatibilidade de acesso do técnico.
- Implementados os fluxos null → técnico A → técnico B → null sem alterar status da OS.
- A máquina de estados ORD-03 permanece isolada; atribuição não altera status.
- Clientes e equipamentos continuam masters do tenant, sem branch_id.
- As tabelas filhas de recepção continuam derivando a unidade através da OS, sem branch_id próprio.

## Testes criados

- OrderOperationalVisibilityTest: visibilidade matriz/unidade e rejeição de cruzamento de tenant/unidade.
- OrderAssignmentTest: ciclo completo de atribuição, rejeição de técnico incompatível e histórico append-only.
- Testes anteriores de fundação, lifecycle e recepção foram atualizados para criar uma Branch válida.

## Validações executadas

- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — php -l nos arquivos PHP alterados.
- PASS — git diff --check.
- MANUAL VALIDATION REQUIRED — php artisan migrate:fresh --force && php artisan test --compact tests/Feature/Orders: falhou antes da migração por indisponibilidade do MySQL em 127.0.0.1:3306, banco vetoros2.

A validação de integração permanece pendente em ambiente com MySQL disponível. Não foram declarados testes verdes sem execução real.

---

# ORD-05 — Snapshots Operacionais e Identidade Histórica

## Resultado

O executar.md mudou novamente e passou a exigir a preservação histórica do contexto da OS. O novo marco foi executado e este relatório foi atualizado.

## Estrutura criada

- Migration 2026_09_15_145854_create_order_snapshots_table.php.
- Model OrderSnapshot com BelongsToTenant, TenantScope, casts array para JSON e relação OrderSnapshot::order().
- Relação singular Order::snapshot().
- OrderCreationService para criação transacional de OS.
- Order::created gera automaticamente o snapshot inicial, evitando que os fluxos atuais com Order::create() fiquem sem contexto histórico.
- Factory OrderSnapshotFactory para apoio aos testes.
- OrderSnapshotTest cobrindo criação, alterações cadastrais, ausência de equipamento físico, multitenancy, duplicidade e imutabilidade.

## Decisões arquiteturais

Foi escolhida uma tabela própria em vez de adicionar várias colunas JSON em orders. Isso mantém o domínio explícito, evita poluir a OS e não cria uma abstração genérica de snapshots.

O snapshot complementa as FKs atuais; não substitui customer_id, customer_equipment_id, equipment_type_id, company_id ou branch_id. Ele não é cache: as relações normais continuam exibindo cadastros atuais, enquanto o snapshot serve para histórico e documentos.

Customer, CustomerEquipment e EquipmentType continuam masters do Tenant, sem company_id ou branch_id. Branch também não é duplicada nas tabelas de recepção.

## Campos preservados

- customer_data: número, tipo, nomes, documentos, contatos e endereço existentes em customers.
- equipment_data: número, marca, modelo, serial, IMEI, asset tag, cor, descrição e equipamento_type com número e nome. Quando não há equipamento físico, preserva apenas o tipo.
- company_data: identificação, documentos, contatos e endereço existentes em companies.
- branch_data: name, company_id e active, que são os campos existentes em branches.

Datas de cadastro, relações, permissões, tokens, senhas, credenciais e dados de autenticação não entram no snapshot. O payload é montado explicitamente; Models inteiros não são serializados.

## Momento e imutabilidade

O evento created da Order cria exatamente um snapshot inicial. O OrderCreationService envolve a operação em transaction. O snapshot rejeita update e delete por LogicException, e a combinação unique tenant_id/order_id impede mais de um snapshot inicial por OS.

## Integridade multitenant

order_snapshots possui tenant_id e order_id, FK composta para orders(tenant_id, id), FK simples para remoção em cascata e índices/uniques compostos. O model valida que a OS vinculada pertence ao mesmo tenant do snapshot. O TenantScope continua isolando consultas.

## Compatibilidade VetorOS 1

Não foi criado importador. Para futura reconstrução histórica, permanecem úteis os campos legados de cliente, equipamento, empresa/filial, número da OS, datas operacionais e identificadores usados nas relações. O campo legado orders.password não será usado, conforme a regra de segurança do marco.

## Testes criados

- criação de OS gera um snapshot;
- snapshot pertence ao tenant e à OS corretos;
- dados originais de Customer, CustomerEquipment, Company e Branch permanecem após alterações nos masters;
- OS sem equipamento físico preserva o EquipmentType;
- snapshot cross-tenant é rejeitado;
- duplicidade, update e delete são rejeitados;
- regressões anteriores permanecem cobertas pelos testes existentes.

## Validações

- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — ./vendor/bin/pint --dirty --test --format agent.
- PASS — php -l nos arquivos PHP alterados.
- PASS — git diff --check.
- MANUAL VALIDATION REQUIRED — os testes de banco não foram repetidos, pois a tentativa anterior de php artisan migrate:fresh --force já confirmou MySQL indisponível em 127.0.0.1:3306.

Os testes não foram declarados como PASS sem execução real.

## Próximo marco

O próximo marco lógico é expor os dados históricos em uma camada de consulta/documento, sem implementar ainda PDF, impressão ou módulos financeiros.

---

# Revisão do ORD-05 — requisitos adicionais do executar.md

O executar.md foi alterado novamente, embora continue no ORD-05. A revisão tornou explícitos os testes de rastreabilidade, remoção, rollback e validação Company/Branch. A implementação foi conferida e ajustada sem reabrir os marcos anteriores.

## Ajustes realizados

- Os payloads agora preservam também customer_id, customer_equipment_id, company_id e branch_id originais para rastreabilidade.
- Mantido o equipamento físico opcional; sem CustomerEquipment, equipment_data registra o EquipmentType.
- Acrescentado teste de que delete do snapshot é rejeitado.
- Duplicidade de snapshot é rejeitada pelo Model antes da violação de unique.
- Acrescentado teste do caminho oficial OrderCreationService, confirmando rollback quando a validação da OS falha.
- Mantida a validação de que Branch pertence à mesma Company e ao mesmo Tenant através das regras já existentes de Order.
- Nenhum campo foi adicionado indevidamente a customers, customer_equipments, equipment_types ou tabelas filhas de recepção.

## Validação

- PASS — ./vendor/bin/pint --dirty --format agent.
- PASS — ./vendor/bin/pint --dirty --test --format agent.
- PASS — php -l nos arquivos PHP alterados.
- PASS — git diff --check.
- MANUAL VALIDATION REQUIRED — testes dependentes de banco não foram repetidos; a tentativa anterior confirmou MySQL indisponível em 127.0.0.1:3306.

Comandos para validação manual:

    php artisan migrate:fresh --force
    php artisan test --compact tests/Feature/CRM
    php artisan test --compact tests/Feature/Orders
    php artisan test --compact tests/Feature/Orders/OrderSnapshotTest.php
    vendor/bin/phpstan analyse
    ./vendor/bin/pint --dirty --test --format agent
    git diff --check
