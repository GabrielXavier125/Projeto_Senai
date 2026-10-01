import 'package:sqflite/sqflite.dart';

import '../core/app_exceptions.dart';
import '../data/app_database.dart';
import '../models/livro.dart';
import '../models/usuario.dart';

/// Equivalente ao LivroController + App\Livewire\Livros\* do projeto original.
class LivroRepository {
  Future<Database> get _db async => AppDatabase.instance.database;

  /// RN8: professor só vê os livros da própria matéria. Busca por título/ISBN/matéria
  /// e filtro manual de matéria (só disponível para o almoxarife) espelham Livros/Index.
  Future<List<Livro>> listar({
    required Usuario usuarioLogado,
    String busca = '',
    String? materiaFiltro,
  }) async {
    final db = await _db;
    final materiaFixa = (usuarioLogado.isCoordenador && usuarioLogado.materia != null)
        ? usuarioLogado.materia
        : null;

    final where = <String>[];
    final args = <Object?>[];

    if (materiaFixa != null) {
      where.add('materia = ?');
      args.add(materiaFixa);
    } else if (materiaFiltro != null && materiaFiltro.isNotEmpty) {
      where.add('materia = ?');
      args.add(materiaFiltro);
    }

    if (busca.isNotEmpty) {
      where.add('(titulo LIKE ? OR isbn LIKE ? OR materia LIKE ?)');
      args.addAll(['%$busca%', '%$busca%', '%$busca%']);
    }

    final rows = await db.query(
      'livros',
      where: where.isEmpty ? null : where.join(' AND '),
      whereArgs: args.isEmpty ? null : args,
      orderBy: 'titulo',
    );

    return rows.map(Livro.fromMap).toList();
  }

  /// Todos os livros, sem filtro por matéria — usado em seletores/filtros de outras telas
  /// (ex.: escolher o livro ao registrar uma movimentação, ou filtrar o histórico).
  Future<List<Livro>> buscarTodosParaFiltro() async {
    final db = await _db;
    final rows = await db.query('livros', orderBy: 'materia, titulo');
    return rows.map(Livro.fromMap).toList();
  }

  Future<List<String>> listarMaterias() async {
    final db = await _db;
    final rows = await db.rawQuery('SELECT DISTINCT materia FROM livros ORDER BY materia');
    return rows.map((r) => r['materia'] as String).toList();
  }

  Future<Livro?> buscarPorId(int id) async {
    final db = await _db;
    final rows = await db.query('livros', where: 'id = ?', whereArgs: [id]);
    if (rows.isEmpty) return null;
    return Livro.fromMap(rows.first);
  }

  /// RN3: ISBN deve ser único por livro.
  Future<void> criar({
    required String titulo,
    required String isbn,
    required String materia,
    required int estoqueMinimo,
  }) async {
    final db = await _db;
    final existente = await db.query('livros', where: 'isbn = ?', whereArgs: [isbn]);
    if (existente.isNotEmpty) {
      throw const RegraNegocioException('Já existe um livro com este ISBN.');
    }

    await db.insert('livros', {
      'titulo': titulo,
      'isbn': isbn,
      'materia': materia,
      'saldo_atual': 0,
      'estoque_minimo': estoqueMinimo,
    });
  }

  Future<void> atualizar({
    required int id,
    required String titulo,
    required String isbn,
    required String materia,
    required int estoqueMinimo,
  }) async {
    final db = await _db;
    final duplicado = await db.query(
      'livros',
      where: 'isbn = ? AND id != ?',
      whereArgs: [isbn, id],
    );
    if (duplicado.isNotEmpty) {
      throw const RegraNegocioException('Já existe outro livro com este ISBN.');
    }

    await db.update(
      'livros',
      {'titulo': titulo, 'isbn': isbn, 'materia': materia, 'estoque_minimo': estoqueMinimo},
      where: 'id = ?',
      whereArgs: [id],
    );
  }

  /// Bloqueia a exclusão quando existem movimentações — mesma proteção da UI web.
  Future<void> excluir(int id) async {
    final db = await _db;
    final movimentacoes = Sqflite.firstIntValue(await db.rawQuery(
      'SELECT COUNT(*) FROM movimentacoes WHERE livro_id = ?',
      [id],
    ));

    if ((movimentacoes ?? 0) > 0) {
      throw const RegraNegocioException(
        'Não é possível excluir este livro: há movimentações registradas.',
      );
    }

    try {
      await db.delete('livros', where: 'id = ?', whereArgs: [id]);
    } on DatabaseException {
      throw const RegraNegocioException(
        'Não é possível excluir este livro: há movimentações ou reservas registradas.',
      );
    }
  }
}
