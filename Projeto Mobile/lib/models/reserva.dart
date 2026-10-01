import 'status_reserva.dart';

class Reserva {
  final int id;
  final int livroId;
  final int userId;
  final int quantidade;
  final StatusReserva status;
  final String? observacao;
  final DateTime dataReserva;
  final DateTime? dataRetirada;

  // Desnormalizados para exibição.
  final String? livroTitulo;
  final int? livroSaldoAtual;
  final String? userNome;

  const Reserva({
    required this.id,
    required this.livroId,
    required this.userId,
    required this.quantidade,
    required this.status,
    required this.dataReserva,
    this.observacao,
    this.dataRetirada,
    this.livroTitulo,
    this.livroSaldoAtual,
    this.userNome,
  });

  bool get isPendente => status == StatusReserva.pendente;

  /// Usado para destacar em vermelho quando o saldo não cobre a reserva.
  bool get temEstoqueSuficiente =>
      livroSaldoAtual == null ? true : livroSaldoAtual! >= quantidade;

  factory Reserva.fromMap(Map<String, Object?> map) => Reserva(
        id: map['id'] as int,
        livroId: map['livro_id'] as int,
        userId: map['user_id'] as int,
        quantidade: map['quantidade'] as int,
        status: StatusReserva.fromValor(map['status'] as String),
        observacao: map['observacao'] as String?,
        dataReserva: DateTime.parse(map['data_reserva'] as String),
        dataRetirada: map['data_retirada'] == null
            ? null
            : DateTime.parse(map['data_retirada'] as String),
        livroTitulo: map['livro_titulo'] as String?,
        livroSaldoAtual: map['livro_saldo_atual'] as int?,
        userNome: map['user_nome'] as String?,
      );
}
