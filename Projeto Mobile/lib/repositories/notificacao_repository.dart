import 'package:sqflite/sqflite.dart';
import 'package:uuid/uuid.dart';

import '../data/app_database.dart';
import '../models/livro.dart';
import '../models/notificacao.dart';
import '../models/perfil_usuario.dart';

/// Equivalente a App\Notifications\NovaEntradaLivro + Livewire\Notificacoes\Index.
class NotificacaoRepository {
  static const _uuid = Uuid();

  Future<Database> get _db async => AppDatabase.instance.database;

  /// RN9: dispara para todos os professores cadastrados na matéria do livro.
  Future<int> notificarProfessoresDaMateria(Livro livro, int quantidade) async {
    final db = await _db;
    final professores = await db.query(
      'users',
      where: 'perfil = ? AND materia = ?',
      whereArgs: [PerfilUsuario.coordenador.valor, livro.materia],
    );

    final mensagem =
        'Chegaram $quantidade exemplar(es) de "${livro.titulo}" (${livro.materia}).';

    for (final professor in professores) {
      await db.insert('notificacoes', {
        'id': _uuid.v4(),
        'user_id': professor['id'],
        'livro_id': livro.id,
        'livro_titulo': livro.titulo,
        'materia': livro.materia,
        'quantidade': quantidade,
        'mensagem': mensagem,
        'read_at': null,
        'created_at': DateTime.now().toIso8601String(),
      });
    }

    return professores.length;
  }

  /// Lista e marca como lidas automaticamente (mesmo comportamento do mount() do Livewire).
  Future<List<NotificacaoLivro>> listarEMarcarLidas(int userId) async {
    final db = await _db;
    final rows = await db.query(
      'notificacoes',
      where: 'user_id = ?',
      whereArgs: [userId],
      orderBy: 'created_at DESC',
    );

    await db.update(
      'notificacoes',
      {'read_at': DateTime.now().toIso8601String()},
      where: 'user_id = ? AND read_at IS NULL',
      whereArgs: [userId],
    );

    return rows.map(NotificacaoLivro.fromMap).toList();
  }

  Future<int> contarNaoLidas(int userId) async {
    final db = await _db;
    final result = Sqflite.firstIntValue(await db.rawQuery(
      'SELECT COUNT(*) FROM notificacoes WHERE user_id = ? AND read_at IS NULL',
      [userId],
    ));
    return result ?? 0;
  }

  Future<void> excluir(String id) async {
    final db = await _db;
    await db.delete('notificacoes', where: 'id = ?', whereArgs: [id]);
  }
}
