import 'package:sqflite/sqflite.dart';

import '../core/app_exceptions.dart';
import '../data/app_database.dart';
import '../models/livro.dart';
import '../models/movimentacao.dart';
import '../models/tipo_movimentacao.dart';
import '../models/usuario.dart';

/// Equivalente direto a app/Services/EstoqueService.php — centraliza as
/// regras de negócio de estoque (RN1, RN2, RN4, RN5, RN6).
class EstoqueRepository {
  Future<Database> get _db async => AppDatabase.instance.database;

  Future<Movimentacao> registrarEntrada({
    required Livro livro,
    required int quantidade,
    required Usuario usuario,
    String observacao = '',
  }) async {
    if (quantidade <= 0) {
      throw const QuantidadeInvalidaException('A quantidade deve ser maior que zero.');
    }

    final db = await _db;
    late int movimentacaoId;

    // RN5: soma ao saldo + grava a movimentação atomicamente.
    await db.transaction((txn) async {
      await txn.rawUpdate(
        'UPDATE livros SET saldo_atual = saldo_atual + ? WHERE id = ?',
        [quantidade, livro.id],
      );

      movimentacaoId = await txn.insert('movimentacoes', {
        'livro_id': livro.id,
        'user_id': usuario.id,
        'tipo': 'entrada',
        'quantidade': quantidade,
        'observacao': observacao.isEmpty ? null : observacao,
        'data_hora': DateTime.now().toIso8601String(),
      });
    });

    return Movimentacao(
      id: movimentacaoId,
      livroId: livro.id,
      userId: usuario.id,
      tipo: TipoMovimentacao.entrada,
      quantidade: quantidade,
      observacao: observacao.isEmpty ? null : observacao,
      dataHora: DateTime.now(),
    );
  }

  Future<Movimentacao> registrarSaida({
    required Livro livro,
    required int quantidade,
    required Usuario usuario,
    String observacao = '',
  }) async {
    if (quantidade <= 0) {
      throw const QuantidadeInvalidaException('A quantidade deve ser maior que zero.');
    }

    // RN1: bloqueia saída se não há saldo suficiente.
    if (!livro.temSaldoSuficiente(quantidade)) {
      throw EstoqueInsuficienteException(
        'Estoque insuficiente. Saldo atual: ${livro.saldoAtual} | Solicitado: $quantidade.',
      );
    }

    final db = await _db;
    late int movimentacaoId;

    await db.transaction((txn) async {
      await txn.rawUpdate(
        'UPDATE livros SET saldo_atual = saldo_atual - ? WHERE id = ?',
        [quantidade, livro.id],
      );

      movimentacaoId = await txn.insert('movimentacoes', {
        'livro_id': livro.id,
        'user_id': usuario.id,
        'tipo': 'saida',
        'quantidade': quantidade,
        'observacao': observacao.isEmpty ? null : observacao,
        'data_hora': DateTime.now().toIso8601String(),
      });
    });

    return Movimentacao(
      id: movimentacaoId,
      livroId: livro.id,
      userId: usuario.id,
      tipo: TipoMovimentacao.saida,
      quantidade: quantidade,
      observacao: observacao.isEmpty ? null : observacao,
      dataHora: DateTime.now(),
    );
  }

  Future<List<Livro>> listarBaixoEstoque({int? minimo}) async {
    final db = await _db;
    final rows = minimo != null
        ? await db.query('livros', where: 'saldo_atual <= ?', whereArgs: [minimo], orderBy: 'saldo_atual')
        : await db.query('livros', where: 'saldo_atual <= estoque_minimo', orderBy: 'saldo_atual');

    return rows.map(Livro.fromMap).toList();
  }
}
