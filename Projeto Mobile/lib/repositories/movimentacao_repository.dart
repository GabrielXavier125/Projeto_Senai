import 'package:sqflite/sqflite.dart';

import '../data/app_database.dart';
import '../models/movimentacao.dart';

/// Equivalente a MovimentacaoController::index + App\Livewire\Movimentacoes\Index.
class MovimentacaoRepository {
  Future<Database> get _db async => AppDatabase.instance.database;

  Future<List<Movimentacao>> listar({
    String? tipo,
    int? livroId,
    DateTime? dataInicio,
    DateTime? dataFim,
  }) async {
    final db = await _db;

    final where = <String>[];
    final args = <Object?>[];

    if (tipo != null && tipo.isNotEmpty) {
      where.add('m.tipo = ?');
      args.add(tipo);
    }
    if (livroId != null) {
      where.add('m.livro_id = ?');
      args.add(livroId);
    }
    if (dataInicio != null) {
      where.add('date(m.data_hora) >= date(?)');
      args.add(dataInicio.toIso8601String());
    }
    if (dataFim != null) {
      where.add('date(m.data_hora) <= date(?)');
      args.add(dataFim.toIso8601String());
    }

    final sql = '''
      SELECT m.*, l.titulo AS livro_titulo, u.nome AS user_nome
      FROM movimentacoes m
      JOIN livros l ON l.id = m.livro_id
      JOIN users u ON u.id = m.user_id
      ${where.isEmpty ? '' : 'WHERE ${where.join(' AND ')}'}
      ORDER BY m.data_hora DESC
    ''';

    final rows = await db.rawQuery(sql, args);
    return rows.map(Movimentacao.fromMap).toList();
  }
}
