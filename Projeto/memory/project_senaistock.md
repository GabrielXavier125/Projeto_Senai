---
name: project-senaistock
description: Contexto do projeto SenaiStock — controle de estoque de livros do SENAI em Laravel 13 + Filament 5.6
metadata:
  type: project
---

Projeto SenaiStock: sistema de controle de estoque de livros didáticos do SENAI Limeira, construído em Laravel 13 + Filament 5.6 + MySQL + Laravel Sanctum.

**Why:** O almoxarifado perdia controle de saídas de livros, causando rupturas de estoque e atrasos no aprendizado.

**How to apply:** Ao sugerir funcionalidades, seguir os requisitos do documento (RF1–RF10, RN1–RN6). O painel Filament é para gerenciamento interno; a API RESTful é para consumo externo.

**Estrutura planejada:**
- 3 tabelas: users (+ perfil ENUM), livros, movimentacoes
- 2 perfis: ALMOXARIFE, COORDENADOR
- 2 services: AuthService, EstoqueService
- 4 controllers API: Auth, Livro, Estoque, Movimentacao
- Filament Resources: LivroResource, MovimentacaoResource + BaixoEstoqueWidget

**Documentação:** `DEVELOPMENT.md` na raiz do projeto — contém checklist completo por Sprint.

**Diretório:** `c:\laragon\www\SenaiStock`
