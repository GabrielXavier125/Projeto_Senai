import 'package:path/path.dart';
import 'package:sqflite/sqflite.dart';

import '../core/password_hasher.dart';

/// Banco local SQLite — schema espelha as migrations do projeto Laravel original
/// (users, livros, movimentacoes, reservas, notifications), populado com os
/// mesmos dados de demonstração no primeiro uso.
class AppDatabase {
  AppDatabase._();
  static final AppDatabase instance = AppDatabase._();

  Database? _db;

  Future<Database> get database async {
    _db ??= await _open();
    return _db!;
  }

  Future<Database> _open() async {
    final path = join(await getDatabasesPath(), 'senaistock_mobile.db');
    return openDatabase(
      path,
      version: 1,
      onConfigure: (db) => db.execute('PRAGMA foreign_keys = ON'),
      onCreate: _onCreate,
    );
  }

  Future<void> _onCreate(Database db, int version) async {
    await db.execute('''
      CREATE TABLE users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        senha_hash TEXT NOT NULL,
        perfil TEXT NOT NULL,
        materia TEXT
      )
    ''');

    await db.execute('''
      CREATE TABLE livros (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        isbn TEXT NOT NULL UNIQUE,
        materia TEXT NOT NULL,
        saldo_atual INTEGER NOT NULL DEFAULT 0,
        estoque_minimo INTEGER NOT NULL DEFAULT 10
      )
    ''');

    await db.execute('''
      CREATE TABLE movimentacoes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        livro_id INTEGER NOT NULL REFERENCES livros(id),
        user_id INTEGER NOT NULL REFERENCES users(id),
        tipo TEXT NOT NULL,
        quantidade INTEGER NOT NULL,
        observacao TEXT,
        data_hora TEXT NOT NULL
      )
    ''');

    await db.execute('''
      CREATE TABLE reservas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        livro_id INTEGER NOT NULL REFERENCES livros(id),
        user_id INTEGER NOT NULL REFERENCES users(id),
        quantidade INTEGER NOT NULL,
        status TEXT NOT NULL,
        observacao TEXT,
        data_reserva TEXT NOT NULL,
        data_retirada TEXT
      )
    ''');

    await db.execute('''
      CREATE TABLE notificacoes (
        id TEXT PRIMARY KEY,
        user_id INTEGER NOT NULL REFERENCES users(id),
        livro_id INTEGER NOT NULL,
        livro_titulo TEXT NOT NULL,
        materia TEXT NOT NULL,
        quantidade INTEGER NOT NULL,
        mensagem TEXT NOT NULL,
        read_at TEXT,
        created_at TEXT NOT NULL
      )
    ''');

    await _seed(db);
  }

  Future<void> _seed(Database db) async {
    final senha = PasswordHasher.hash('senha123');

    final almoxarifeId = await db.insert('users', {
      'nome': 'Carlos Almoxarife',
      'email': 'almoxarife@senai.br',
      'senha_hash': senha,
      'perfil': 'almoxarife',
      'materia': null,
    });

    final profProgramacaoId = await db.insert('users', {
      'nome': 'Prof. Ana Silva',
      'email': 'coordenador@senai.br',
      'senha_hash': senha,
      'perfil': 'coordenador',
      'materia': 'Programação',
    });

    await db.insert('users', {
      'nome': 'Prof. Carlos Redes',
      'email': 'prof.redes@senai.br',
      'senha_hash': senha,
      'perfil': 'coordenador',
      'materia': 'Redes',
    });

    await db.insert('users', {
      'nome': 'Prof. Marcos Eletro',
      'email': 'prof.eletro@senai.br',
      'senha_hash': senha,
      'perfil': 'coordenador',
      'materia': 'Eletrotécnica',
    });

    final livros = <Map<String, Object?>>[
      {'titulo': 'Algoritmos e Lógica de Programação', 'isbn': '978-85-365-0104-6', 'materia': 'Programação', 'saldo_atual': 32, 'estoque_minimo': 10},
      {'titulo': 'PHP e MySQL — Desenvolvimento Web', 'isbn': '978-85-7522-418-6', 'materia': 'Programação', 'saldo_atual': 8, 'estoque_minimo': 10},
      {'titulo': 'Banco de Dados: Projeto e Implementação', 'isbn': '978-85-02-06394-4', 'materia': 'Banco de Dados', 'saldo_atual': 15, 'estoque_minimo': 10},
      {'titulo': 'Redes de Computadores', 'isbn': '978-85-352-3576-7', 'materia': 'Redes', 'saldo_atual': 0, 'estoque_minimo': 10},
      {'titulo': 'Sistemas Operacionais: Conceitos e Aplicações', 'isbn': '978-85-365-0155-8', 'materia': 'Sistemas Operacionais', 'saldo_atual': 20, 'estoque_minimo': 10},
      {'titulo': 'Instalações Elétricas Residenciais', 'isbn': '978-85-7194-883-9', 'materia': 'Eletrotécnica', 'saldo_atual': 25, 'estoque_minimo': 10},
      {'titulo': 'Automação Industrial: CLP e SCADA', 'isbn': '978-85-7608-391-7', 'materia': 'Automação', 'saldo_atual': 5, 'estoque_minimo': 10},
      {'titulo': 'Eletricidade Básica', 'isbn': '978-85-216-1632-2', 'materia': 'Eletrotécnica', 'saldo_atual': 18, 'estoque_minimo': 10},
      {'titulo': 'Gestão de Pessoas nas Organizações', 'isbn': '978-85-02-07654-8', 'materia': 'Administração', 'saldo_atual': 40, 'estoque_minimo': 15},
      {'titulo': 'Contabilidade Geral', 'isbn': '978-85-224-5765-3', 'materia': 'Contabilidade', 'saldo_atual': 12, 'estoque_minimo': 15},
      {'titulo': 'Marketing e Vendas', 'isbn': '978-85-02-09156-5', 'materia': 'Marketing', 'saldo_atual': 9, 'estoque_minimo': 10},
      {'titulo': 'NR-10: Segurança em Instalações Elétricas', 'isbn': '978-85-7842-111-3', 'materia': 'Segurança do Trabalho', 'saldo_atual': 30, 'estoque_minimo': 10},
      {'titulo': 'CIPA: Prevenção de Acidentes no Trabalho', 'isbn': '978-85-7842-222-6', 'materia': 'Segurança do Trabalho', 'saldo_atual': 22, 'estoque_minimo': 10},
    ];

    final livroIds = <String, int>{};
    for (final livro in livros) {
      final id = await db.insert('livros', livro);
      livroIds[livro['isbn'] as String] = id;
    }

    // Histórico de movimentações dos últimos ~30 dias (mesmo cenário do MovimentacaoSeeder original).
    // Os saldos acima já refletem esse histórico, então aqui só gravamos o log.
    final agora = DateTime.now();
    Future<void> movimentar(String isbn, String tipo, int quantidade, int userId, String obs, int diasAtras) async {
      final livroId = livroIds[isbn];
      if (livroId == null) return;
      await db.insert('movimentacoes', {
        'livro_id': livroId,
        'user_id': userId,
        'tipo': tipo,
        'quantidade': quantidade,
        'observacao': obs,
        'data_hora': agora.subtract(Duration(days: diasAtras)).toIso8601String(),
      });
    }

    await movimentar('978-85-365-0104-6', 'entrada', 20, almoxarifeId, 'NF 4521 — Editora Érica', 28);
    await movimentar('978-85-352-3576-7', 'entrada', 15, almoxarifeId, 'NF 4598 — Editora Campus', 25);
    await movimentar('978-85-7194-883-9', 'entrada', 10, almoxarifeId, 'NF 4632 — Editora LTC', 20);
    await movimentar('978-85-02-07654-8', 'entrada', 25, almoxarifeId, 'NF 4701 — Editora Saraiva', 15);
    await movimentar('978-85-7842-111-3', 'entrada', 12, profProgramacaoId, 'NF 4755 — Editora Senac', 10);

    await movimentar('978-85-365-0104-6', 'saida', 8, almoxarifeId, 'Turma DS-01 — Desenvolvimento de Sistemas 2026', 22);
    await movimentar('978-85-02-06394-4', 'saida', 5, almoxarifeId, 'Turma DS-01 — Banco de Dados 2026', 18);
    await movimentar('978-85-7194-883-9', 'saida', 7, almoxarifeId, 'Turma EL-01 — Eletrotécnica 2026', 14);
    await movimentar('978-85-02-07654-8', 'saida', 10, profProgramacaoId, 'Turma ADM-01 — Administração 2026', 10);
    await movimentar('978-85-7842-111-3', 'saida', 12, almoxarifeId, 'Turma ST-01 — Segurança do Trabalho 2026', 5);
    await movimentar('978-85-365-0155-8', 'saida', 6, almoxarifeId, 'Turma DS-02 — Sistemas Operacionais 2026', 3);
    await movimentar('978-85-7608-391-7', 'saida', 5, almoxarifeId, 'Turma AU-01 — Automação Industrial 2026', 1);
  }
}
