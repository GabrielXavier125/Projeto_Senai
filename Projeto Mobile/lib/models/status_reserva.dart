/// Ciclo de vida de uma reserva: pendente -> retirada / cancelada.
enum StatusReserva {
  pendente('pendente'),
  retirada('retirada'),
  cancelada('cancelada');

  final String valor;
  const StatusReserva(this.valor);

  static StatusReserva fromValor(String valor) =>
      StatusReserva.values.firstWhere((e) => e.valor == valor);

  String get label => switch (this) {
        StatusReserva.pendente => 'Pendente',
        StatusReserva.retirada => 'Retirada',
        StatusReserva.cancelada => 'Cancelada',
      };
}
