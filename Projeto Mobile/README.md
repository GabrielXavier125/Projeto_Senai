# SenaiStock Mobile

Versão **Flutter** do SenaiStock (controle de estoque de livros didáticos do SENAI Limeira), construída como aplicativo **independente**: não consome a API do projeto Laravel em `Projeto/`. Todo o domínio — usuários, livros, movimentações, reservas e notificações — e as mesmas regras de negócio (RN1–RN10) foram reimplementados em Dart, com banco local **SQLite** (`sqflite`).

📘 Documentação técnica completa (stack, banco de dados, módulos, regras de negócio, como executar e log de desenvolvimento): [`DOCUMENTACAO.pdf`](DOCUMENTACAO.pdf) — fonte em Markdown: [`DOCUMENTACAO.md`](DOCUMENTACAO.md).

> **Por que não usar a API Laravel?** A API do projeto original só cobre Livros, Estoque e Movimentações; Reservas, Notificações e Usuários existem apenas nas telas Livewire. Para o app ter as mesmas funcionalidades sem alterar o projeto original, ele tem sua própria camada de dados local.

## Funcionalidades

| Módulo | Almoxarife | Professor |
|---|---|---|
| Dashboard (estatísticas, baixo estoque, movimentações recentes, reservas pendentes) | ✅ | ❌ (abre direto em Livros) |
| Livros: listar, buscar, filtrar por matéria | ✅ todos | ✅ só da própria matéria (RN8) |
| Livros: criar, editar, excluir (bloqueado se houver movimentações) | ✅ | ❌ |
| Movimentações: histórico com filtros (tipo, livro, período) | ✅ | ❌ |
| Registrar entrada/saída (saldo nunca negativo — RN1; transação atômica — RN5) | ✅ | ❌ |
| Reservas: nova solicitação (carrinho com vários livros) | ❌ | ✅ (RN7) |
| Reservas: dar baixa (gera saída automática — RN10) / cancelar | ✅ todas | ✅ cancelar as próprias |
| Notificações de chegada de livros da matéria (RN9) | ❌ | ✅ |
| Usuários: CRUD, sem autoexclusão e sem excluir o último almoxarife | ✅ | ❌ |

## Credenciais de demonstração

O banco é populado automaticamente na primeira abertura (mesmos dados de exemplo do sistema web):

| Perfil | E-mail | Senha | Matéria |
|---|---|---|---|
| Almoxarife | `almoxarife@senai.br` | `senha123` | — |
| Professor | `coordenador@senai.br` | `senha123` | Programação |
| Professor | `prof.redes@senai.br` | `senha123` | Redes |
| Professor | `prof.eletro@senai.br` | `senha123` | Eletrotécnica |

## Como rodar

Pré-requisitos (versões usadas no desenvolvimento):

| Ferramenta | Versão | Onde foi instalado |
|---|---|---|
| Flutter SDK | 3.47.5 (stable) | `C:\src\flutter` |
| JDK | Temurin 21 | `C:\src\jdk21` |
| Android SDK | platform 36, build-tools 36.0.0, NDK 28.2.13676358 | `C:\Android\sdk` |
| Emulador | AVD `medium_phone` (Android 16 / API 36) | — |

Variáveis de ambiente do usuário: `JAVA_HOME=C:\src\jdk21`, `ANDROID_HOME=C:\Android\sdk` e o `PATH` incluindo `C:\src\flutter\bin` e `C:\Android\sdk\platform-tools`.

```powershell
cd "Projeto Mobile"
flutter pub get

# Liga o emulador (ou conecte um celular Android com depuração USB ativa)
flutter emulators --launch medium_phone

flutter run
```

Durante o `flutter run`, `r` faz hot reload e `R` hot restart.

```powershell
flutter analyze   # análise estática
flutter test      # testes das regras de negócio
```

### Problemas conhecidos nesta máquina

- **Primeiro build trava baixando o Gradle ou o NDK**: os downloaders internos do Gradle e da CLI `android` travam em arquivos grandes nesta rede, mas `curl` funciona normalmente. Solução usada: baixar com `curl` e colocar o arquivo no lugar esperado.
  - Gradle 9.3.1 → `%USERPROFILE%\.gradle\wrapper\dists\gradle-9.3.1-all\9ot9r568e8zfvvd4mn8rbu1j0\gradle-9.3.1-all.zip`
  - NDK r28c → extrair em `C:\Android\sdk\ndk\28.2.13676358` (use `tar -xf`, o `Expand-Archive` do PowerShell é lento demais).
- **"Acesso negado" logo após extrair arquivos**: o antivírus segura os arquivos recém-extraídos por alguns segundos. Basta tentar de novo.

## Arquitetura

```
lib/
├── core/          # tema, cores, exceções de domínio, hash de senha
├── models/        # enums e entidades (Usuario, Livro, Movimentacao, Reserva, NotificacaoLivro)
├── data/          # AppDatabase: schema SQLite + dados de demonstração
├── repositories/  # regras de negócio (equivalentes aos Services/componentes Livewire do Laravel)
├── state/         # Session (usuário autenticado) via Provider
├── widgets/       # badges, cards, diálogos reutilizáveis
└── screens/       # uma pasta por módulo, espelhando app/Livewire/* do projeto web
```

| Projeto Laravel | Projeto Mobile |
|---|---|
| `app/Services/EstoqueService.php` | `lib/repositories/estoque_repository.dart` |
| `app/Notifications/NovaEntradaLivro.php` | `lib/repositories/notificacao_repository.dart` |
| `app/Livewire/*` | `lib/screens/*` |
| `database/migrations` + `seeders` | `lib/data/app_database.dart` |
| Sessão web + middleware `role` | `lib/state/session.dart` + abas por perfil em `lib/screens/home_shell.dart` |
