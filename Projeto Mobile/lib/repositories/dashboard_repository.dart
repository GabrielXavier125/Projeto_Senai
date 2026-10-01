import 'package:sqflite/sqflite.dart';

import '../data/app_database.dart';
import '../models/livro.dart';
import '../models/movimentacao.dart';
import '../models/reserva.dart';

class DashboardStats {
  final int totalLivros;
  final int baixoEstoque;
  final int entradasHoje;
  final int saidasHoje;
  final List<Livro> livrosBaixo;
  final List<Movimentacao> movimentacoesRecentes;
  final List<Reserva> reservasPendentes;

  const DashboardStats({
    required this.totalLivros,
    required this.baixoEstoque,
    required this.entradasHoje,
    required this.saidasHoje,
    required this.livrosBaixo,
    required this.movimentacoesRecentes,
    required this.reservasPendentes,
  });
}

/// Equivalente a App\Livewire\Dashboard — exclusivo do almoxarife.
class DashboardRepository {
  Future<Database> get _db async => AppDatabase.instance.database;

  Future<int> _contar(Database db, String sql, [List<Object?>? args]) async =>
      Sqflite.firstIntValue(await db.rawQuery(sql, args)) ?? 0;

  Future<DashboardStats> carregar() async {
    final db = await _db;
    final hoje = DateTime.now().toIso8601String().substring(0, 10);

    final totalLivros = await _contar(db, 'SELECT COUNT(*) FROM livros');
    final baixoEstoque = await _contar(db, 'SELECT COUNT(*) FROM livros WHERE saldo_atual <= estoque_minimo');
    final entradasHoje = await _contar(
      db,
      "SELECT COUNT(*) FROM movimentacoes WHERE tipo = 'entrada' AND date(data_hora) = date(?)",
      [hoje],
    );
    final saidasHoje = await _contar(
      db,
      "SELECT COUNT(*) FROM movimentacoes WHERE tipo = 'saida' AND date(data_hora) = date(?)",
      [hoje],
    );

    final livrosBaixoRows = await db.query(
      'livros',
      where: 'saldo_atual <= estoque_minimo',
      orderBy: 'saldo_atual',
      limit: 10,
    );

    final movRecentesRows = await db.rawQuery('''
      SELECT m.*, l.titulo AS livro_titulo, u.nome AS user_nome
      FROM movimentacoes m
      JOIN livros l ON l.id = m.livro_id
      JOIN users u ON u.id = m.user_id
      ORDER BY m.data_hora DESC
      LIMIT 6
    ''');

    final reservasPendentesRows = await db.rawQuery('''
      SELECT r.*, l.titulo AS livro_titulo, l.saldo_atual AS livro_saldo_atual, u.nome AS user_nome
      FROM reservas r
      JOIN livros l ON l.id = r.livro_id
      JOIN users u ON u.id = r.user_id
      WHERE r.status = 'pendente'
      ORDER BY r.data_reserva DESC
      LIMIT 5
    ''');

    return DashboardStats(
      totalLivros: totalLivros,
      baixoEstoque: baixoEstoque,
      entradasHoje: entradasHoje,
      saidasHoje: saidasHoje,
      livrosBaixo: livrosBaixoRows.map(Livro.fromMap).toList(),
      movimentacoesRecentes: movRecentesRows.map(Movimentacao.fromMap).toList(),
      reservasPendentes: reservasPendentesRows.map(Reserva.fromMap).toList(),
    );
  }
}
