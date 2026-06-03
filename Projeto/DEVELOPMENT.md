# SenaiStock — Documentação de Desenvolvimento

**Projeto:** Controle de Estoque de Livros Didáticos — SENAI Limeira  
**Stack:** Laravel 13 + Filament 5.6 + MySQL + Laravel Sanctum  
**Autores:** Diogo Scherrer, Gabriel Furtunato, Gabriel Xavier

---

## 1. Visão Geral

O SenaiStock é um sistema Back-End (API RESTful) para controle de estoque de livros didáticos do SENAI. Livros chegam ao almoxarifado em remessas e são retirados pelos instrutores para distribuição em sala de aula. O sistema garante rastreabilidade total das entradas e saídas, impedindo rupturas de estoque.

---

## 2. Tecnologias Utilizadas

| Tecnologia | Versão | Função |
|---|---|---|
| PHP | ^8.3 | Linguagem base |
| Laravel | ^13.0 | Framework Back-End |
| Filament | ^5.6 | Painel administrativo |
| Laravel Sanctum | ^4.x | Autenticação API via token |
| MySQL | — | Banco de dados relacional |

---

## 3. Atores e Perfis

| Perfil | Permissões |
|---|---|
| **ALMOXARIFE** | Cadastrar livros, registrar entradas e saídas de estoque |
| **COORDENADOR** | Consultar relatórios, monitorar baixo estoque, registrar movimentações |

---

## 4. Banco de Dados

### 4.1 Tabela `users` (modificada)

| Coluna | Tipo | Detalhe |
|---|---|---|
| id | INT PK | Auto-incremento |
| name | VARCHAR(100) | Nome do usuário |
| email | VARCHAR(150) | Único |
| senha_hash | VARCHAR(255) | Hash bcrypt |
| perfil | ENUM | `ALMOXARIFE` \| `COORDENADOR` |
| email_verified_at | TIMESTAMP | Nullable |
| created_at | TIMESTAMP | — |
| updated_at | TIMESTAMP | — |

### 4.2 Tabela `livros` (nova)

| Coluna | Tipo | Detalhe |
|---|---|---|
| id | INT PK | Auto-incremento |
| titulo | VARCHAR(200) | Obrigatório |
| isbn | VARCHAR(20) | Único (índice) |
| materia | VARCHAR(180) | Obrigatório |
| saldo_atual | INT | DEFAULT 0, CHECK > 0 |
| estoque_minimo | INT | DEFAULT 10 |
| created_at | TIMESTAMP | — |
| updated_at | TIMESTAMP | — |

### 4.3 Tabela `movimentacoes` (nova)

| Coluna | Tipo | Detalhe |
|---|---|---|
| id | INT PK | Auto-incremento |
| livro_id | INT FK | → livros.id |
| usuario_id | INT FK | → users.id |
| tipo | ENUM | `ENTRADA` \| `SAIDA` |
| quantidade | INT UNSIGNED | CHECK > 0 |
| observacao | TEXT | Nullable |
| data_hora | TIMESTAMP | Registrado automaticamente |
| created_at | TIMESTAMP | — |
| updated_at | TIMESTAMP | — |

**Índices:** `data_hora`, `tipo`, `livro_id`, `usuario_id`

---

## 5. Estrutura de Classes

```
app/
├── Enums/
│   ├── PerfilUsuario.php          # ALMOXARIFE, COORDENADOR
│   └── TipoMovimentacao.php       # ENTRADA, SAIDA
│
├── Models/
│   ├── User.php                   # +perfil, +movimentacoes()
│   ├── Livro.php                  # +movimentacoes(), consultarSaldo(), baixoEstoque()
│   └── Movimentacao.php           # +livro(), +usuario()
│
├── Services/
│   ├── AuthService.php            # login(), logout(), gerarToken(), validarToken()
│   └── EstoqueService.php         # registrarEntrada(), registrarSaida(),
│                                  # validarSaldo(), listarBaixoEstoque()
│
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php     # POST /login, POST /logout
│   │   ├── LivroController.php    # GET/POST /livros, GET /livros/{id}
│   │   ├── EstoqueController.php  # POST /stock/entries, POST /stock/exits,
│   │   │                          # GET /stock/low
│   │   └── MovimentacaoController.php  # GET /movimentacoes
│   │
│   └── Requests/
│       ├── LoginRequest.php
│       ├── StoreLivroRequest.php
│       ├── EntradaEstoqueRequest.php
│       └── SaidaEstoqueRequest.php
│
└── Filament/
    ├── Resources/
    │   ├── LivroResource.php
    │   └── MovimentacaoResource.php
    └── Widgets/
        └── BaixoEstoqueWidget.php
```

---

## 6. Endpoints da API

### Autenticação

| Método | Rota | Perfil | Descrição |
|---|---|---|---|
| POST | `/api/auth/login` | Público | Login com email/senha → retorna token |
| POST | `/api/auth/logout` | Autenticado | Revoga o token atual |

### Livros

| Método | Rota | Perfil | Descrição |
|---|---|---|---|
| GET | `/api/livros` | Autenticado | Lista livros com saldo (paginado, filtros: titulo/isbn/materia) |
| POST | `/api/livros` | Almoxarife / Coordenador | Cadastra novo livro |
| GET | `/api/livros/{id}` | Autenticado | Detalhes + saldo de um livro |
| GET | `/api/livros/{id}/saldo` | Autenticado | Consulta apenas o saldo atual |

### Estoque

| Método | Rota | Perfil | Descrição |
|---|---|---|---|
| POST | `/api/stock/entries` | Almoxarife / Coordenador | Registra entrada (abastecimento) |
| POST | `/api/stock/exits` | Almoxarife / Coordenador | Registra saída (baixa manual) |
| GET | `/api/stock/low` | Autenticado | Lista livros com estoque abaixo do mínimo |

### Movimentações

| Método | Rota | Perfil | Descrição |
|---|---|---|---|
| GET | `/api/movimentacoes` | Autenticado | Histórico paginado (filtros: livro, período, tipo, usuário) |

---

## 7. Regras de Negócio

| Código | Regra | Implementação |
|---|---|---|
| **RN1** | Estoque não pode ficar negativo | `EstoqueService::registrarSaida()` valida `quantidade <= saldo_atual`, retorna HTTP 422 |
| **RN2** | Quantidade deve ser positiva (> 0) | Form Request com `min:1` |
| **RN3** | ISBN deve ser único | Migration com `unique()` + Form Request com `unique:livros` |
| **RN4** | Movimentação deve registrar autor e data | `usuario_id = auth()->id()` e `data_hora = now()` em todo registro |
| **RN5** | Operações críticas devem ser atômicas | `DB::transaction()` envolvendo atualização de saldo + criação da movimentação |
| **RN6** | Nível mínimo de estoque | Parâmetro `?minimo=10` na consulta, padrão 10 se não informado |

---

## 8. Requisitos Funcionais — Checklist de Implementação

- [x] **RF1** — Autenticação (Login com email/senha, retorna token)
- [x] **RF2** — Controle de Perfil e Permissões (Almoxarife / Coordenador)
- [x] **RF3** — Cadastro de Livros (Título, ISBN único, Matéria)
- [x] **RF4** — Listagem de Livros (paginada, com filtros)
- [x] **RF5** — Entrada de Estoque (registra ENTRADA, soma ao saldo)
- [x] **RF6** — Saída de Estoque (registra SAIDA, valida saldo disponível)
- [x] **RF7** — Consulta de Saldo por Livro
- [x] **RF8** — Monitoramento de Baixo Estoque (parâmetro `minimo`, padrão 10)
- [x] **RF9** — Histórico de Movimentações (filtros + paginação)
- [x] **RF10** — Logout (revoga token)

> Os checkboxes acima serão marcados conforme cada RF for implementado e testado.

---

## 9. Requisitos Não Funcionais

| Código | Requisito | Como atendido |
|---|---|---|
| **RNF1** | API RESTful, respostas JSON, status HTTP corretos | Controllers retornam `response()->json()` com status semântico |
| **RNF2** | Segurança: autenticação, hash de senha, validação | Sanctum + bcrypt (padrão Laravel) + Form Requests |
| **RNF3** | Qualidade de código: PSR, Clean Code, separação de responsabilidades | Controllers finos, lógica em Services, validação em Requests |
| **RNF4** | Persistência e integridade: chaves, restrições, índices | Migrations com FK, UNIQUE, CHECK, INDEX |
| **RNF5** | Performance: paginação, consultas filtradas e indexadas | `->paginate()` + índices no DB + query scopes |
| **RNF6** | Versionamento: Git + commits descritivos | Git local |
| **RNF7** | Testabilidade: rotas via Insomnia/Postman | Endpoints documentados neste arquivo |

---

## 10. Status do Desenvolvimento (Sprints)

### Sprint 1 — Fundação
- [ ] Instalar Sanctum
- [ ] Migration: `add_perfil_to_users`
- [ ] Migration: `create_livros_table`
- [ ] Migration: `create_movimentacoes_table`
- [ ] Enums: `PerfilUsuario`, `TipoMovimentacao`
- [ ] Models: `User`, `Livro`, `Movimentacao`

### Sprint 2 — Autenticação (MUST HAVE)
- [ ] `AuthService` (login, logout, token)
- [ ] `AuthController` (POST /login, POST /logout)
- [ ] `LoginRequest`
- [ ] Rotas de autenticação

### Sprint 3 — Core de Estoque (MUST HAVE + SHOULD HAVE)
- [ ] `StoreLivroRequest`, `EntradaEstoqueRequest`, `SaidaEstoqueRequest`
- [ ] `EstoqueService` (RN1–RN6 implementadas)
- [ ] `LivroController`
- [ ] `EstoqueController`
- [ ] Rotas de livros e estoque

### Sprint 4 — Histórico e Consultas (COULD HAVE)
- [ ] `MovimentacaoController` (histórico com filtros)
- [ ] Endpoint baixo estoque (`/api/stock/low`)
- [ ] Consulta saldo por livro

### Sprint 5 — Painel Filament
- [ ] `LivroResource` (listar, criar, editar)
- [ ] `MovimentacaoResource` (histórico visual)
- [ ] `BaixoEstoqueWidget` no Dashboard

### Sprint 6 — Finalização
- [ ] `DatabaseSeeder` com usuários de teste (1 Almoxarife, 1 Coordenador)
- [ ] Ajustes e validações finais

---

## 11. Códigos de Resposta HTTP Utilizados

| Status | Situação |
|---|---|
| 200 | Sucesso em consultas |
| 201 | Recurso criado com sucesso |
| 400 | Requisição malformada |
| 401 | Não autenticado (sem token) |
| 403 | Sem permissão para a ação |
| 404 | Recurso não encontrado |
| 409 | Conflito (ex.: ISBN duplicado) |
| 422 | Erro de validação ou regra de negócio (ex.: estoque insuficiente) |

---

## 12. Exemplo de Fluxo Principal (Saída de Estoque)

```
1. Almoxarife → POST /api/stock/exits  { livro_id, quantidade, observacao, token }
2. StockController → valida token (Sanctum middleware)
3. StockController → delega para EstoqueService::registrarSaida()
4. EstoqueService → valida permissão do perfil
5. EstoqueService → valida quantidade > 0 (RN2)
6. EstoqueService → busca livro, verifica saldo disponível (RN1)
7.   [saldo suficiente] →
        DB::transaction:
          - Livro::saldo_atual -= quantidade
          - Movimentacao::create(tipo=SAIDA, quantidade, observacao, usuario_id, data_hora)
        Retorna 200 OK + saldo atualizado + movimentacao
8.   [saldo insuficiente] →
        Retorna 422 + "Estoque insuficiente"
```

---

*Documentação gerada em 13/05/2026 — atualizada conforme o desenvolvimento avança.*
