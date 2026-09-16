# VetorOS 2 — ORD-09 — Interface Operacional da Ordem de Serviço + CRM de Clientes

Além de toda a implementação de interface de Ordens de Serviço definida neste marco, incluir também a **primeira interface operacional completa de Clientes e Equipamentos**, pois estes módulos já possuem domínio/backend estrutural, mas ainda não possuem UI operacional.

A intenção é entregar um fluxo contínuo:

```text
Cliente
  ↓
Equipamentos
  ↓
Abrir Ordem de Serviço
  ↓
Atendimento
  ↓
Orçamento
```

Não transformar este marco em uma implementação ampla de CRM. O foco é o cadastro operacional utilizado pela assistência técnica.

---

# 27. Interface operacional de Clientes

Criar:

```text
resources/js/pages/customers/index.tsx
resources/js/pages/customers/create.tsx
resources/js/pages/customers/show.tsx
resources/js/pages/customers/edit.tsx
```

Ou estrutura equivalente coerente com o padrão atual do projeto.

Também criar controllers, requests e rotas necessárias.

Preferencialmente:

```text
routes/customers.php
```

incluído em `routes/web.php`.

Todas as rotas devem permanecer protegidas por:

- auth;
- verified;
- current.tenant;
- current.company;

Clientes continuam sendo **masters do Tenant**.

Não adicionar:

```text
company_id
branch_id
```

em Customer.

Matriz e filiais compartilham o cadastro do cliente.

---

# 28. Listagem de Clientes

Criar uma listagem operacional real.

Busca por:

- customer_number;
- nome;
- nome fantasia;
- CPF;
- CNPJ;
- telefone;
- celular;
- WhatsApp;
- e-mail.

Permitir filtros úteis como:

- PF/PJ;
- ativo/inativo, somente se já existir essa informação no domínio;
- clientes com equipamento cadastrado;
- eventualmente período de cadastro se houver campo apropriado.

Não criar campos novos apenas para suportar filtro.

A pesquisa deve acontecer no backend.

Não carregar todos os clientes para filtrar em React.

Usar paginação.

---

# 29. Cadastro de Cliente

A tela deve suportar PF e PJ respeitando o Model existente.

Organizar campos de forma lógica.

## Identificação

PF:

- nome;
- CPF;
- data de nascimento quando existente.

PJ:

- razão social/nome;
- nome fantasia quando existente;
- CNPJ;
- contato responsável.

## Contato

- telefone;
- celular;
- WhatsApp;
- e-mail.

## Endereço

- CEP;
- endereço;
- número como string;
- complemento;
- bairro;
- cidade;
- estado.

Usar os campos reais já existentes no Model/migration.

Não recriar nomes apenas para deixar a UI mais bonita.

Não alterar o domínio sem necessidade.

---

# 30. Normalização

CPF, CNPJ, telefone, WhatsApp e CEP continuam seguindo as regras já implementadas no domínio.

A UI pode aplicar máscara visual.

O backend continua sendo a autoridade final e deve persistir conforme as normalizações atuais.

Não duplicar regras de normalização de forma divergente no frontend.

---

# 31. Tela de detalhe do Cliente

A tela:

```text
/customers/{customer}
```

deve funcionar como ponto central do relacionamento operacional.

Organizar preferencialmente em Tabs:

```text
Geral
Equipamentos
Ordens de Serviço
```

Outras tabs somente quando houver domínio real.

Não adicionar Financeiro, Vendas, Histórico de contatos ou outros módulos ainda inexistentes.

---

# 32. Aba Geral do Cliente

Mostrar:

- número do cliente;
- tipo PF/PJ;
- nome;
- documentos;
- contatos;
- endereço;
- data de cadastro quando disponível.

Permitir ação:

```text
Editar cliente
```

Não disponibilizar exclusão automaticamente.

Antes de oferecer excluir, avaliar dependências.

Cliente com:

- equipamento;
- Ordem de Serviço;
- orçamento;
- qualquer histórico operacional

não deve simplesmente desaparecer.

Preferir política de inativação futuramente caso o domínio ainda não possua essa funcionalidade.

Documentar essa decisão.

---

# 33. Equipamentos do Cliente

Dentro da tela do cliente, a tab `Equipamentos` deve listar:

- equipment_number;
- tipo de equipamento;
- marca;
- modelo;
- serial;
- IMEI;
- asset tag;
- cor;
- demais campos reais disponíveis.

Permitir:

```text
+ Novo equipamento
```

Criar tela ou dialog conforme a complexidade real.

Não usar modal gigante para formulários extensos.

---

# 34. Cadastro de equipamento

O equipamento físico continua sendo:

```text
CustomerEquipment
```

e seu catálogo/tipo:

```text
EquipmentType
```

Não misturar novamente estes conceitos.

O usuário deve escolher:

```text
Tipo de equipamento
```

e preencher os dados da unidade física:

- marca;
- modelo;
- serial;
- IMEI;
- asset tag;
- cor;
- descrição;
- demais atributos existentes.

O `equipment_number` deve continuar seguindo o mecanismo atual do domínio.

Se ainda não existir numeração transacional apropriada para `CustomerEquipment`, auditar antes de inventar `MAX()+1`.

Caso seja necessária nova sequência, usar o padrão já adotado por:

```text
TenantSequenceService
```

Não criar contador concorrente.

---

# 35. Cliente → Abrir Ordem de Serviço

Essa integração é obrigatória.

Na tela do cliente deve existir ação:

```text
+ Nova Ordem de Serviço
```

Ao abrir a criação da OS a partir do cliente:

- `customer_id` deve vir pré-selecionado;
- listar somente equipamentos daquele cliente;
- ainda permitir abrir sem equipamento físico caso o domínio aceite;
- evitar exigir nova pesquisa pelo cliente.

Exemplo:

```text
/customers/{customer}/orders/create
```

ou:

```text
/orders/create?customer={id}
```

Escolha a abordagem mais consistente com Inertia e o projeto atual.

O backend nunca deve confiar apenas no parâmetro vindo do frontend.

---

# 36. Equipamento → Abrir Ordem de Serviço

Na listagem/detalhe do equipamento:

```text
Abrir OS
```

deve abrir o formulário de Ordem já com:

- Customer;
- CustomerEquipment;
- EquipmentType

preenchidos a partir das relações oficiais.

O usuário poderá completar:

- Branch;
- prioridade;
- defeito;
- acessórios;
- condições;
- checklist;
- técnico etc.

Isso reduz o fluxo operacional da recepção.

---

# 37. Histórico de Ordens do Cliente

Na tab:

```text
Ordens de Serviço
```

listar as OS daquele Customer respeitando `OrderVisibilityService`.

Mostrar:

- número;
- equipamento;
- status;
- Branch;
- técnico;
- data;
- orçamento/status quando útil.

Não mostrar para o usuário OS de Branch à qual ele não possui acesso.

O fato de Customer ser compartilhado no Tenant não significa que todas as suas OS devem ficar visíveis a qualquer usuário.

Aplicar a mesma política operacional já definida para Orders.

---

# 38. Histórico de Ordens do Equipamento

Quando fizer sentido na UI, ao abrir um equipamento mostrar também suas Ordens anteriores.

Isso é particularmente importante para assistência técnica.

Permite consultar:

- retornos;
- reparos anteriores;
- defeitos recorrentes;
- garantias futuras;
- histórico técnico.

Neste marco apenas listar e navegar para a OS.

Não criar ainda sistema novo de garantia ou análise de recorrência.

---

# 39. Equipment Types

Não é necessário transformar `EquipmentType` em um módulo grande neste marco.

Entretanto, deve existir forma operacional de selecionar os tipos já cadastrados.

Se o projeto ainda não possui UI mínima para administração de tipos, criar uma gestão simples:

```text
Configurações / Tipos de Equipamento
```

com:

- listar;
- criar;
- editar;
- ativar/desativar caso já exista essa propriedade.

Não misturar essa tela ao cadastro de CustomerEquipment.

---

# 40. Lookups reais

Criar endpoints tenant-safe de pesquisa para:

```text
customers
customer equipments
equipment types
technicians
orders
```

conforme necessário.

Usar:

- query;
- limite;
- paginação/limit;
- debounce no frontend.

Não depender de:

```text
Customer::limit(100)
```

ou listas estáticas semelhantes para operação real.

---

# 41. UX de Clientes

Seguir os mesmos padrões visuais do ORD-09 e ORC-04:

- shadcn/ui;
- tabelas full width;
- busca no cabeçalho;
- filtros compactos;
- ações contextuais;
- badges quando aplicáveis;
- ConfirmDialog;
- toast;
- formulários organizados em seções;
- comportamento responsivo.

Evitar formulário com dezenas de campos em uma única coluna vertical.

Usar seções como:

```text
Identificação
Contato
Endereço
```

---

# 42. Navegação principal

Adicionar Clientes ao menu principal de forma coerente.

Uma estrutura sugerida:

```text
Operacional
 ├── Ordens de Serviço
 ├── Clientes
 └── Orçamentos
```

ou conforme a organização atual do projeto.

Não criar item separado no menu principal para `CustomerEquipment` se o fluxo fizer mais sentido dentro de Cliente.

Equipamento físico é predominantemente contextual ao cliente.

---

# 43. Ações contextuais

Clientes:

```text
Novo cliente
Editar
Novo equipamento
Nova Ordem de Serviço
```

Equipamentos:

```text
Editar
Abrir Ordem de Serviço
Ver histórico de OS
```

Ordens:

```text
Abrir
Alterar status
Atribuir técnico
Novo orçamento
```

Orçamentos:

```text
Abrir OS
```

O objetivo é tornar o sistema navegável pela operação, e não apenas por CRUDs isolados.

---

# 44. Testes HTTP de Clientes

Criar:

```text
tests/Feature/CRM/CustomerHttpTest.php
```

ou estrutura equivalente.

Cobrir pelo menos:

1. listagem tenant-safe;
2. busca por nome;
3. busca por CPF/CNPJ;
4. criação PF;
5. criação PJ;
6. normalização de documentos;
7. edição;
8. cliente de outro Tenant retorna 404;
9. equipamentos pertencem ao cliente correto;
10. equipamento de outro Tenant não pode ser associado;
11. criação de equipamento;
12. tela do cliente lista seus equipamentos;
13. tela do cliente lista somente OS visíveis;
14. usuário de uma Branch não vê OS de outra Branch;
15. criação de OS a partir do cliente;
16. criação de OS a partir do equipamento;
17. customer/equipment pré-selecionados corretamente;
18. tentativa de trocar IDs manualmente no request é rejeitada.

Manter os testes CRM existentes.

---

# 45. Auditoria adicional

Ao final, além da auditoria de Orders/Budgets, procurar:

```text
Customer::create
Customer::forceCreate
CustomerEquipment::create
CustomerEquipment::forceCreate
EquipmentType::create
MAX(
max(
withoutGlobalScopes
withoutEvents
```

Analisar cada ocorrência.

Factories/testes não devem ser confundidos com fluxo operacional.

Não introduzir bypass das invariantes existentes.

---

# 46. Validação consolidada

Após implementar Clientes + Orders:

```bash
APP_ENV=testing php artisan migrate:fresh --force

APP_ENV=testing php artisan test --compact tests/Feature/CRM
APP_ENV=testing php artisan test --compact tests/Feature/Orders
APP_ENV=testing php artisan test --compact tests/Feature/Budgets

APP_ENV=testing php artisan test --compact

APP_ENV=testing vendor/bin/phpstan analyse

./vendor/bin/pint --dirty --test --format agent
git diff --check

npm run types:check
npx vp check --fix
npm run build
```

A implementação somente pode ser considerada pronta se Clientes, Orders e Budgets permanecerem integrados sem regressões.

---

# 47. Resultado esperado

Ao concluir o ORD-09, deve ser possível realizar o fluxo operacional:

```text
Cadastrar cliente
        ↓
Cadastrar equipamento
        ↓
Abrir OS pelo cliente/equipamento
        ↓
Registrar recepção
        ↓
Aplicar checklist
        ↓
Atribuir técnico
        ↓
Executar atendimento
        ↓
Alterar status
        ↓
Criar orçamento
        ↓
Abrir orçamento
        ↓
Voltar para a OS
        ↓
Consultar histórico pelo cliente/equipamento
```

Este é o fluxo que deve orientar a UI.

Não entregar CRUDs desconectados.

O objetivo do marco é transformar Customer + CustomerEquipment + Order + Budget em uma experiência operacional integrada de assistência técnica.
