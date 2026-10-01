import 'package:sqflite/sqflite.dart';

import '../core/app_exceptions.dart';
import '../data/app_database.dart';
import '../models/livro.dart';
import '../models/reserva.dart';
import '../models/usuario.dart';
import 'estoque_repository.dart';

/// Um item do "carrinho" de reserva: livro + quantidade solicitada.
class ItemReserva {
  final int livroId;
  final int quantidade;
  const ItemReserva({required this.livroId, required this.quantidade});
}

/// Equivalente a App\Livewire\Reservas\Index + Reservas\Nova.
/// Nota: dar baixa não dispara notificação — RN9 só se aplica a entradas de estoque.
class ReservaRepository {
  final EstoqueRepository _estoqueRepository;

  ReservaRepository({EstoqueRepository? estoqueRepository})
      : _estoqueRepository = estoqueRepository ?? EstoqueRepository();

  Future<Database> get _db async => AppDatabase.instance.database;

  static const _selectComJoins = '''
      SELECT r.*, l.titulo AS livro_titulo, l.saldo_atual AS livro_saldo_atual, u.nome AS user_nome
      FROM reservas r
      JOIN livros l ON l.id = r.livro_id
      JOIN users u ON u.id = r.user_id
  ''';

  Future<List<Reserva>> listar({
    required Usuario usuarioLogado,
    String? status,
  }) async {
    final db = await _db;

    final where = <String>[];
    final args = <Object?>[];

    if (status != null && status.isNotEmpty) {
      where.add('r.status = ?');
      args.add(status);
    }
    // Professor vê somente as próprias reservas; almoxarife vê todas.
    if (usuarioLogado.isCoordenador) {
      where.add('r.user_id = ?');
      args.add(usuarioLogado.id);
    }

    final sql = '''
      $_selectComJoins
      ${where.isEmpty ? '' : 'WHERE ${where.join(' AND ')}'}
      ORDER BY r.data_reserva DESC
    ''';

    final rows = await db.rawQuery(sql, args);
    return rows.map(Reserva.fromMap).toList();
  }

  /// RN7: só o professor cria reserva. Cria vários itens em uma única transação.
  Future<int> criar({
    required Usuario professor,
    required List<ItemReserva> itens,
    required String observacao,
  }) async {
    if (itens.isEmpty) {
      throw const RegraNegocioException('Selecione pelo menos um livro antes de enviar.');
    }

    final db = await _db;
    final agora = DateTime.now().toIso8601String();

    await db.transaction((txn) async {
      for (final item in itens) {
        await txn.insert('reservas', {
          'livro_id': item.livroId,
          'user_id': professor.id,
          'quantidade': item.quantidade,
          'status': 'pendente',
          'observacao': observacao,
          'data_reserva': agora,
        });
      }
    });

    return itens.length;
  }

  Future<Reserva> _buscar(int id) async {
    final db = await _db;
    final rows = await db.rawQuery('$_selectComJoins WHERE r.id = ?', [id]);

    if (rows.isEmpty) {
      throw const RegraNegocioException('Reserva não encontrada.');
    }
    return Reserva.fromMap(rows.first);
  }

  /// RN10: valida saldo antes de dar baixa; registra saída automática no estoque.
  Future<void> darBaixa(int reservaId, Usuario almoxarife) async {
    final reserva = await _buscar(reservaId);
    if (!reserva.isPendente) {
      throw const RegraNegocioException('Esta reserva já foi processada.');
    }

    final db = await _db;
    final livroRow = (await db.query('livros', where: 'id = ?', whereArgs: [reserva.livroId])).first;
    final livro = Livro.fromMap(livroRow);

    if (!livro.temSaldoSuficiente(reserva.quantidade)) {
      throw EstoqueInsuficienteException(
        'Estoque insuficiente. Saldo atual: ${livro.saldoAtual} | Solicitado: ${reserva.quantidade}.',
      );
    }

    var obs = 'Baixa de reserva #${reserva.id} — ${reserva.userNome}';
    if (reserva.observacao != null && reserva.observacao!.isNotEmpty) {
      obs += ' — ${reserva.observacao}';
    }

    await _estoqueRepository.registrarSaida(
      livro: livro,
      quantidade: reserva.quantidade,
      usuario: almoxarife,
      observacao: obs,
    );

    await db.update(
      'reservas',
      {'status': 'retirada', 'data_retirada': DateTime.now().toIso8601String()},
      where: 'id = ?',
      whereArgs: [reservaId],
    );
  }

  Future<void> cancelar(int reservaId, Usuario usuarioLogado) async {
    final reserva = await _buscar(reservaId);

    if (!usuarioLogado.isAlmoxarife && reserva.userId != usuarioLogado.id) {
      throw const RegraNegocioException('Você não tem permissão para cancelar esta reserva.');
    }
    if (!reserva.isPendente) {
      throw const RegraNegocioException('Esta reserva já foi processada.');
    }

    final db = await _db;
    await db.update('reservas', {'status': 'cancelada'}, where: 'id = ?', whereArgs: [reservaId]);
  }
}
