# Documentação Técnica — SenaiStock

**Projeto:** Controle de Estoque de Livros Didáticos  
**Instituição:** SENAI Limeira/SP  
**Equipe:** Diogo Scherrer, Gabriel Furtunato, Gabriel Xavier  
**Ano:** 2026  
**Stack:** Laravel 13 + Livewire 4 + Tailwind CSS 4 + Alpine.js + MySQL + PHP 8.3

---

## Índice

1. [Visão Geral do Sistema](#1-visão-geral-do-sistema)
2. [Stack Tecnológica](#2-stack-tecnológica)
3. [Estrutura do Projeto](#3-estrutura-do-projeto)
4. [Banco de Dados](#4-banco-de-dados)
5. [Autenticação e Perfis de Acesso](#5-autenticação-e-perfis-de-acesso)
6. [Módulos do Sistema](#6-módulos-do-sistema)
7. [Regras de Negócio](#7-regras-de-negócio)
8. [API RESTful](#8-api-restful)
9. [Serviços (Services)](#9-serviços-services)
10. [Notificações](#10-notificações)
11. [Credenciais de Demonstração](#11-credenciais-de-demonstração)
12. [Como Executar](#12-como-executar)
13. [Log de Desenvolvimento](#13-log-de-desenvolvimento)

---

## 1. Visão Geral do Sistema

O SenaiStock resolve um problema real do SENAI: o almoxarifado recebia grandes remessas de livros didáticos, mas não tinha controle das saídas. Isso causava rupturas — o estoque zerava sem aviso e as turmas ficavam sem material.

**O sistema oferece:**
- Cadastro de livros com ISBN único por título
- Registro de entradas (abastecimento) e saídas (retiradas para turmas)
- Saldo atualizado em tempo real após cada movimentação
- Alerta de livros com estoque abaixo do mínimo configurado
- Histórico completo de todas as movimentações com filtros
- Sistema de reservas: professor solicita livros, almoxarife dá baixa
- Notificação automática ao professor quando chegam livros da sua matéria
- Controle de acesso por perfil (Almoxarife / Professor)
- Gerenciamento completo de usuários pelo almoxarife

**Acesso:**
- Interface web: `http://senaistock.test` (Laragon) ou `http://127.0.0.1:8000` (artisan serve)
- API RESTful: `/api/...`

---

## 2. Stack Tecnológica

| Tecnologia | Versão | Função |
|---|---|---|
| PHP | 8.3 | Linguagem principal |
| Laravel | 13.x | Framework back-end |
| Livewire | 4.3 | Componentes reativos server-side |
| Tailwind CSS | 4.0 | Estilização utilitária |
| Alpine.js | 3.x | Interatividade client-side leve (bundled com Livewire) |
| MySQL | 8.4 | Banco de dados relacional |
| Laravel Sanctum | 4.3 | Autenticação via tokens (API) |
| Vite | 8.x | Bundler de assets (CSS/JS) |

### Por que Livewire em vez de API + SPA?

O projeto usa **Livewire** para a interface web, que mantém o código no servidor (PHP) e sincroniza o estado com o browser via WebSockets/AJAX de forma transparente. As vantagens para este projeto:

- **Sem JavaScript customizado** para a maioria das interações — os componentes são escritos em PHP puro
- **Reatividade nativa** — filtros, buscas e formulários reagem em tempo real sem recarregar a página
- **`wire:navigate`** — navegação entre páginas funciona como SPA (sem recarregamento completo)
- **Fácil manutenção** — toda a lógica fica em PHP, sem precisar sincronizar estado entre front e back

---

## 3. Estrutura do Projeto

```
SenaiStock/
├── app/
│   ├── Enums/                    ← Enumerações PHP (PerfilUsuario, TipoMovimentacao, StatusReserva)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/             ← LoginController (web) e AuthController (API)
│   │   │   ├── LivroController.php       ← API
│   │   │   ├── EstoqueController.php     ← API
│   │   │   └── MovimentacaoController.php ← API
│   │   ├── Middleware/
│   │   │   └── EnsureRole.php    ← Middleware role:almoxarife / role:coordenador
│   │   └── Requests/             ← Form Requests de validação da API
│   ├── Livewire/                 ← Componentes Livewire (interface web)
│   │   ├── Dashboard.php
│   │   ├── Livros/               ← Index, Criar, Editar
│   │   ├── Movimentacoes/        ← Index, Registrar
│   │   ├── Reservas/             ← Index, Nova
│   │   ├── Notificacoes/         ← Index
│   │   └── Usuarios/             ← Index, Criar, Editar
│   ├── Models/                   ← User, Livro, Movimentacao, Reserva
│   ├── Notifications/
│   │   └── NovaEntradaLivro.php  ← Notificação de entrada de estoque
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       └── EstoqueService.php    ← Lógica de negócio do estoque
├── database/
│   ├── migrations/               ← Estrutura do banco
│   └── seeders/                  ← LivroSeeder, MovimentacaoSeeder, DatabaseSeeder
├── resources/
│   ├── css/app.css               ← Tailwind 4 (@import 'tailwindcss')
│   ├── js/app.js
│   └── views/
│       ├── auth/login.blade.php  ← Tela de login
│       ├── layouts/app.blade.php ← Layout principal com sidebar
│       └── livewire/             ← Views dos componentes Livewire
│           ├── dashboard.blade.php
│           ├── livros/
│           ├── movimentacoes/
│           ├── reservas/
│           ├── notificacoes/
│           └── usuarios/
├── routes/
│   ├── api.php                   ← Rotas da API REST
│   └── web.php                   ← Rotas da interface web
└── DOCUMENTACAO.md               ← Este arquivo
```

---

## 4. Banco de Dados

### 4.1 Diagrama de Relacionamentos

```
users
  │
  ├─── 1:N ──► movimentacoes ◄─── N:1 ───┐
  │                                       │
  └─── 1:N ──► reservas ◄──── N:1 ────► livros
                                          │
                                          └─── 1:N ──► movimentacoes
                                          └─── 1:N ──► reservas
```

### 4.2 Tabela `users`

| Campo | Tipo | Descrição |
|---|---|---|
| id | BIGINT PK | Identificador único |
| name | VARCHAR(255) | Nome completo |
| email | VARCHAR(255) UNIQUE | E-mail de acesso |
| password | VARCHAR(255) | Senha com hash bcrypt |
| perfil | ENUM(`almoxarife`, `coordenador`) | Nível de acesso |
| materia | VARCHAR(180) NULLABLE | Matéria que o professor leciona (só coordenadores) |
| remember_token | VARCHAR(100) | Token "lembrar-me" |
| created_at / updated_at | TIMESTAMP | Automático |

> **Campo `materia`:** Determina quais livros o professor vê (filtragem automática) e quais notificações recebe quando chega estoque novo.

### 4.3 Tabela `livros`

| Campo | Tipo | Descrição |
|---|---|---|
| id | BIGINT PK | Identificador único |
| titulo | VARCHAR(200) | Título do livro |
| isbn | VARCHAR(20) UNIQUE | Código ISBN — único (RN3) |
| materia | VARCHAR(188) | Disciplina/matéria |
| saldo_atual | INT DEFAULT 0 | Quantidade em estoque (atualizado por movimentações) |
| estoque_minimo | INT DEFAULT 10 | Limite para alerta de baixo estoque (RN6) |
| created_at / updated_at | TIMESTAMP | Automático |

### 4.4 Tabela `movimentacoes`

| Campo | Tipo | Descrição |
|---|---|---|
| id | BIGINT PK | Identificador único |
| livro_id | FK → livros.id | Livro movimentado |
| user_id | FK → users.id | Quem registrou (RN4) |
| tipo | ENUM(`entrada`, `saida`) | Tipo da operação |
| quantidade | INT UNSIGNED | Quantidade (sempre > 0, RN2) |
| observacao | TEXT NULLABLE | Justificativa (obrigatório em saídas) |
| data_hora | TIMESTAMP | Data e hora exata (RN4) |
| created_at / updated_at | TIMESTAMP | Automático |

> **Imutável:** movimentações nunca são editadas ou excluídas — formam o histórico de auditoria.

### 4.5 Tabela `reservas`

| Campo | Tipo | Descrição |
|---|---|---|
| id | BIGINT PK | Identificador único |
| livro_id | FK → livros.id | Livro reservado |
| user_id | FK → users.id | Professor que fez a reserva |
| quantidade | INT UNSIGNED | Quantidade solicitada |
| status | ENUM(`pendente`, `retirada`, `cancelada`) | Estado da reserva |
| observacao | TEXT NULLABLE | Turma / motivo |
| data_reserva | TIMESTAMP | Quando foi criada |
| data_retirada | TIMESTAMP NULLABLE | Quando o almoxarife deu baixa |
| created_at / updated_at | TIMESTAMP | Automático |

### 4.6 Tabela `notifications` (Laravel padrão)

| Campo | Tipo | Descrição |
|---|---|---|
| id | UUID PK | Identificador único |
| type | VARCHAR | Classe da notificação |
| notifiable_type / notifiable_id | MORPHS | A quem pertence (User) |
| data | TEXT (JSON) | Payload da notificação |
| read_at | TIMESTAMP NULLABLE | Quando foi lida |
| created_at / updated_at | TIMESTAMP | Automático |

---

## 5. Autenticação e Perfis de Acesso

### 5.1 Autenticação Web (sessão)

```
Usuário acessa /login
       ↓
Preenche e-mail + senha → POST /login
       ↓
LoginController::login() → Auth::attempt()
       ↓
✅ Almoxarife  → redireciona para /dashboard
✅ Professor   → redireciona para /livros (sem dashboard)
❌ Credenciais erradas → volta ao login com mensagem de erro
```

### 5.2 Perfis e Permissões

| Recurso | Almoxarife | Professor |
|---|---|---|
| Dashboard com estatísticas | ✅ | ❌ (cai direto em Livros) |
| Livros — visualizar | ✅ todos | ✅ só da sua matéria |
| Livros — criar / editar / excluir | ✅ | ❌ |
| Movimentações — registrar entrada/saída | ✅ | ❌ |
| Movimentações — ver histórico | ✅ | ❌ |
| Reservas — criar nova | ❌ | ✅ |
| Reservas — ver e dar baixa | ✅ (todas) | ✅ (só as próprias) |
| Notificações de chegada de livros | ❌ | ✅ |
| Gerenciar usuários | ✅ | ❌ |

### 5.3 Middleware de Roles

O arquivo `app/Http/Middleware/EnsureRole.php` implementa o middleware `role`:

```php
// Uso nas rotas:
Route::get('/dashboard', ...)->middleware('role:almoxarife');
Route::get('/reservas/nova', ...)->middleware('role:coordenador');
```

### 5.4 Autenticação API (Sanctum)

Para acesso à API RESTful, o fluxo usa tokens Bearer:
1. `POST /api/auth/login` → retorna token
2. Todas as rotas `/api/*` protegidas exigem `Authorization: Bearer {token}`
3. `POST /api/auth/logout` → invalida o token

---

## 6. Módulos do Sistema

### 6.1 Dashboard (`/dashboard`) — Almoxarife

Exibe em tempo real (atualiza a cada 60 s via `wire:poll`):
- **4 cards de estatísticas:** total de títulos, livros em baixo estoque, entradas hoje, saídas hoje
- **Tabela de baixo estoque:** livros com `saldo_atual ≤ estoque_minimo`
- **Movimentações recentes:** últimas 6 movimentações
- **Reservas pendentes:** reservas aguardando baixa

---

### 6.2 Livros (`/livros`)

**Almoxarife:**
- Listagem paginada com busca por título/ISBN/matéria e filtro por matéria
- Criar, editar e excluir livros
- Badge de saldo colorido: 🟢 normal | 🟡 abaixo do mínimo | 🔴 zerado
- Modal de confirmação antes de excluir (bloqueia exclusão de livros com movimentações)

**Professor:**
- Mesma listagem, porém **filtrada automaticamente pela sua matéria**
- Somente leitura (sem botões de criar/editar/excluir)
- Campo de busca por título/ISBN (sem filtro de matéria, pois já está fixo)

---

### 6.3 Movimentações (`/movimentacoes`) — Almoxarife

**Histórico (`/movimentacoes`):**
- Tabela paginada (20 por página) com todas as entradas e saídas
- Filtros: tipo (entrada/saída), livro, período (data início / data fim)
- Botão "Limpar filtros" reseta tudo de uma vez
- Colunas: data/hora + tempo relativo, badge de tipo, livro, quantidade, observação, quem registrou

**Registrar (`/movimentacoes/registrar`):**
- **Campo de livro:** combobox que combina seleção com pesquisa por digitação
  - Clicando: exibe todos os livros agrupados por matéria
  - Digitando: filtra instantaneamente no browser (Alpine.js) por título ou matéria
  - Cada opção mostra o saldo atual com badge colorido
- **Tipo:** cards visuais para Entrada (verde) ou Saída (vermelho)
- **Quantidade:** campo numérico (mínimo 1)
- **Observação:** texto livre — obrigatório para saídas, opcional para entradas
- Após salvar: atualiza o saldo do livro + notifica professores da matéria (se entrada)

---

### 6.4 Reservas (`/reservas`)

**Fluxo completo:**
```
Professor cria reserva (múltiplos livros em um único envio)
       ↓
Reserva fica com status "Pendente"
       ↓
Badge âmbar aparece no menu "Reservas" do almoxarife
       ↓
Almoxarife clica em "Dar Baixa" → modal de confirmação
       ↓
Sistema valida saldo → registra saída automática → status vira "Retirada"
```

**Professor — Nova Reserva (`/reservas/nova`):**
- Lista todos os livros da sua matéria em formato de tabela
- Checkbox por linha + campo de quantidade (habilitado só quando selecionado)
- Badge de saldo em cada linha (verde/amarelo/vermelho)
- Resumo dinâmico dos livros selecionados
- Um único envio cria todas as reservas em transação SQL
- Botão de envio desabilitado até selecionar ao menos um livro

**Almoxarife — Gestão de reservas:**
- Filtro por status: Todas / Pendentes / Retiradas / Canceladas
- Para reservas pendentes: botões "Dar Baixa" e "Cancelar" (com modal de confirmação)
- Badge de quantidade fica vermelho quando o saldo é insuficiente para atender
- Ao dar baixa: cria automaticamente uma saída no estoque

---

### 6.5 Notificações (`/notificacoes`) — Professor

- Lista de notificações de chegada de livros da matéria do professor
- Marcadas automaticamente como lidas ao abrir a página
- Badge vermelho no menu lateral com a contagem de não lidas
- Botão para remover notificações individuais
- Payload de cada notificação: título do livro, matéria, quantidade que chegou, data

**Quando é disparada:**
- Almoxarife registra uma **entrada** no estoque
- Sistema busca todos os professores com `materia = livro->materia`
- Envia `NovaEntradaLivro` para cada um via canal `database`
- Toast no almoxarife informa quantos professores foram notificados

---

### 6.6 Usuários (`/usuarios`) — Almoxarife

**Listagem:**
- Tabela com avatar (inicial colorida), nome, e-mail, badge de perfil, matéria
- Busca por nome ou e-mail
- Filtro por perfil
- O próprio usuário logado aparece marcado "(você)" sem botão Excluir
- Proteção: não permite excluir o único almoxarife do sistema

**Criar usuário (`/usuarios/criar`):**
- Nome, e-mail, senha (mínimo 6 caracteres) + confirmação
- Seleção de perfil com cards visuais (Almoxarife / Professor)
- Campo **Matéria** aparece condicionalmente quando "Professor" é escolhido
- Campo de matéria tem autocomplete com as matérias já cadastradas nos livros
- Senha salva com `Hash::make()` (bcrypt)

**Editar usuário (`/usuarios/{id}/editar`):**
- Mesmos campos da criação
- Seção de redefinição de senha (opcional — deixar em branco mantém a atual)
- Almoxarife não pode alterar o próprio perfil (prevenção de bloqueio acidental)

---

## 7. Regras de Negócio

| Regra | Descrição | Onde é aplicada |
|---|---|---|
| RN1 | Estoque não pode ficar negativo | `EstoqueService::registrarSaida()` — lança `DomainException` |
| RN2 | Quantidade deve ser maior que zero | `EstoqueService` + validação de formulário (`min:1`) |
| RN3 | ISBN deve ser único por livro | Migration (UNIQUE) + validação de criação/edição |
| RN4 | Toda movimentação registra usuário e timestamp | `EstoqueService` — `user_id` + `data_hora = now()` |
| RN5 | Operações de entrada/saída são atômicas | `DB::transaction()` em `registrarEntrada()` e `registrarSaida()` |
| RN6 | Alerta quando saldo ≤ estoque_mínimo | Dashboard, `BaixoEstoque`, badge no header do livro |
| RN7 | Reserva só pode ser criada por professor | Rota `/reservas/nova` com `middleware('role:coordenador')` |
| RN8 | Professor vê apenas livros da sua matéria | `Livros/Index.php` — filtro SQL automático por `user->materia` |
| RN9 | Entrada notifica professor da matéria | `Movimentacoes/Registrar.php` → `NovaEntradaLivro` notification |
| RN10 | Dar baixa valida saldo antes de registrar saída | `Reservas/Index::darBaixa()` — verifica `temSaldoSuficiente()` |

---

## 8. API RESTful

> Base URL: `/api`  
> Autenticação: `Authorization: Bearer {token}` (Sanctum)  
> Formato: JSON

| Método | Rota | Auth | Perfil | Descrição |
|---|---|---|---|---|
| POST | `/api/auth/login` | ❌ | — | Login, retorna token Sanctum |
| POST | `/api/auth/logout` | ✅ | Ambos | Invalida o token |
| GET | `/api/livros` | ✅ | Ambos | Lista com busca, filtros e paginação |
| POST | `/api/livros` | ✅ | Almoxarife | Cadastra novo livro |
| GET | `/api/livros/{id}` | ✅ | Ambos | Dados e saldo de um livro |
| GET | `/api/livros/{id}/saldo` | ✅ | Ambos | Apenas o saldo atual |
| POST | `/api/stock/entries` | ✅ | Almoxarife | Registra entrada de estoque |
| POST | `/api/stock/exits` | ✅ | Ambos | Registra saída de estoque |
| GET | `/api/stock/low` | ✅ | Ambos | Livros abaixo do mínimo |
| GET | `/api/movimentacoes` | ✅ | Almoxarife | Histórico paginado com filtros |

### Padrão de resposta

**Sucesso (entrada):**
```json
{
    "mensagem": "Entrada registrada com sucesso.",
    "saldo_atual": 45,
    "livro": { "id": 1, "titulo": "Algoritmos", "saldo_atual": 45 },
    "movimentacao": { "id": 12, "tipo": "entrada", "quantidade": 10, ... }
}
```

**Erro de negócio (estoque insuficiente):**
```json
{
    "mensagem": "Estoque insuficiente. Saldo atual: 3 | Solicitado: 10."
}
```

### Códigos HTTP utilizados
| Código | Quando |
|---|---|
| 200 | GET / operação bem-sucedida |
| 201 | Recurso criado com sucesso |
| 401 | Token ausente ou inválido |
| 403 | Perfil sem permissão para a ação |
| 404 | Recurso não encontrado |
| 422 | Erro de validação ou regra de negócio |

---

## 9. Serviços (Services)

### `EstoqueService` — `app/Services/EstoqueService.php`

Centraliza toda a lógica de estoque, isolando-a dos controllers e componentes Livewire.

| Método | Parâmetros | O que faz |
|---|---|---|
| `registrarEntrada()` | `Livro, int $quantidade, User, string $observacao` | Soma ao saldo + grava movimentação em `DB::transaction()` |
| `registrarSaida()` | `Livro, int $quantidade, User, string $observacao` | Valida saldo (RN1) + subtrai + grava em `DB::transaction()` |
| `listarBaixoEstoque()` | `?int $minimo = null` | Retorna livros com `saldo_atual ≤ estoque_minimo` (ou ≤ $minimo) |

**Fluxo interno de uma saída:**
```
registrarSaida() chamado
       ↓
if ($quantidade <= 0) → throw InvalidArgumentException (RN2)
       ↓
if (!$livro->temSaldoSuficiente()) → throw DomainException (RN1)
       ↓
DB::transaction() {
    $livro->decrement('saldo_atual', $quantidade)
    Movimentacao::create([user_id, tipo=saida, ...])
}
```

---

## 10. Notificações

O sistema usa o **canal de banco de dados** do Laravel (`notifications` table).

### Classe `NovaEntradaLivro`

> `app/Notifications/NovaEntradaLivro.php`

```
EstoqueService::registrarEntrada() concluído
       ↓
Movimentacoes/Registrar.php busca:
  User::where('perfil', Coordenador)->where('materia', $livro->materia)->get()
       ↓
Para cada professor encontrado:
  $professor->notify(new NovaEntradaLivro($livro, $quantidade))
       ↓
Registro na tabela notifications com payload:
  { livro_id, livro_titulo, materia, quantidade, mensagem }
```

### Interface para o professor

- Badge vermelho no item "Notificações" da sidebar mostra a contagem de não lidas
- Abrindo `/notificacoes`, todas as não lidas são marcadas como lidas automaticamente
- O professor pode remover notificações individualmente

---

## 11. Credenciais de Demonstração

| Perfil | E-mail | Senha | Matéria |
|---|---|---|---|
| Almoxarife | `almoxarife@senai.br` | `senha123` | — |
| Professor | `coordenador@senai.br` | `senha123` | Programação |
| Professor | `prof.redes@senai.br` | `senha123` | Redes |
| Professor | `prof.eletro@senai.br` | `senha123` | Eletrotécnica |

---

## 12. Como Executar

### Pré-requisitos
- PHP 8.3+
- Composer
- MySQL 8.x
- Node.js 18+

### Passo a passo

```bash
# 1. Instalar dependências PHP
composer install

# 2. Instalar dependências Node e compilar CSS/JS
npm install && npm run build

# 3. Configurar o banco no .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=senaistock
DB_USERNAME=root
DB_PASSWORD=sua_senha

# 4. Criar tabelas e popular com dados de demonstração
php artisan migrate:fresh --seed

# 5. Iniciar o servidor
php artisan serve
```

Acesse: `http://127.0.0.1:8000`

---

## 13. Log de Desenvolvimento

### Fase 1 — Base (Laravel + MySQL + Login)
**Stack inicial:** Laravel 13 + Filament 5.6 + MySQL

- Projeto iniciado com Laravel 13 e PHP 8.3
- Banco configurado no MySQL 8.4 (Laragon)
- Tela de login personalizada com CSS próprio
- `LoginController` com autenticação via sessão
- Enum `PerfilUsuario` (Almoxarife / Coordenador)
- Migrations: `users` (+ campo `perfil`), `livros`, `movimentacoes`
- Models: `User`, `Livro`, `Movimentacao`
- `EstoqueService` com `registrarEntrada()`, `registrarSaida()`, `listarBaixoEstoque()`
- `EstoqueController` e `LivroController` para a API REST
- Rotas API completas em `routes/api.php`
- Seeders com 13 livros didáticos e histórico de movimentações de 30 dias

### Fase 2 — Migração para Livewire + Tailwind (abandono do Filament)

**Motivo:** maior controle sobre a interface, melhor desempenho, sem dependência de pacote pesado.

- **Removido:** Filament 5.6 e todos os seus pacotes (`filament/filament` e dependências)
- **Adicionado:** Livewire 4.3 como dependência standalone
- Tailwind CSS 4 já estava configurado via Vite — mantido
- Alpine.js disponível via bundle do Livewire — sem instalação extra
- Novo layout com sidebar fixa (`resources/views/layouts/app.blade.php`)
  - Navegação dinâmica por perfil
  - Toast notifications via Alpine.js
  - `wire:navigate` para SPA-mode
- Componentes Livewire criados:
  - `Dashboard` — cards de estatísticas + tabelas de alerta
  - `Livros/Index`, `Criar`, `Editar`
  - `Movimentacoes/Index`, `Registrar`
- Middleware `EnsureRole` criado para proteção de rotas
- Tela de login reescrita com Tailwind CSS
- Rotas web completas em `routes/web.php`

### Fase 3 — Sistema de Reservas

- Migration `create_reservas_table` com ciclo de vida: `pendente → retirada / cancelada`
- Enum `StatusReserva` (Pendente, Retirada, Cancelada)
- Model `Reserva` com relacionamentos e helpers (`isPendente()`, `temEstoqueSuficiente()`)
- `Reservas/Index` — almoxarife vê todas, professor vê só as próprias
- `Reservas/Nova` — **formulário em lista tipo carrinho**:
  - Exibe todos os livros da matéria do professor
  - Checkbox + campo de quantidade por linha
  - Resumo dos selecionados antes de enviar
  - Um único clique cria múltiplas reservas em `DB::transaction()`
- Dar baixa com modal de confirmação → cria saída automática no `EstoqueService`
- **Badge âmbar** no menu "Reservas" do almoxarife com contagem de pendentes

### Fase 4 — Notificações + Matéria dos Professores

- Campo `materia` adicionado à tabela `users` (professores)
- Migration `add_materia_to_users_table`
- `Livros/Index` filtra automaticamente por `user->materia` para professores
- Filtro de matéria visível apenas para o almoxarife
- `Notification\NovaEntradaLivro` — notificação database quando chega estoque
- Migration `create_notifications_table`
- `Notificacoes/Index` com marcação automática de lidas e exclusão individual
- Badge vermelho no menu lateral do professor com contagem de não lidas
- Seeder atualizado com 3 professores de diferentes matérias

### Fase 5 — Gerenciamento de Usuários

- `Usuarios/Index` — listagem com busca e filtro por perfil
- `Usuarios/Criar` — formulário com perfil + matéria condicional + validação de senha
- `Usuarios/Editar` — edição com redefinição opcional de senha
- Proteções: não excluir a si mesmo, não excluir o único almoxarife
- Novo item "Usuários" na sidebar do almoxarife (seção Administração)

### Fase 6 — Ajustes de UX

- Campo de livro em Movimentações alterado para **combobox**:
  - Clicando: exibe todos os livros agrupados por matéria
  - Digitando: filtro instantâneo via Alpine.js (sem roundtrip ao servidor)
  - Implementado com `@script` para evitar conflitos de aspas em atributos HTML
- Professor não acessa mais o Dashboard — após login cai direto em `/livros`
- Rota `/dashboard` protegida com `role:almoxarife`
- Link Dashboard removido da sidebar do professor
- Filtro de matéria removido da tela de livros do professor (desnecessário com filtro automático)

---

> **Legenda:** ✅ Implementado | ⚠ Restrição conhecida | 🔄 Futuro
