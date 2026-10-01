/// Tipos de movimentação de estoque: entrada (abastecimento) e saída (retirada).
enum TipoMovimentacao {
  entrada('entrada'),
  saida('saida');

  final String valor;
  const TipoMovimentacao(this.valor);

  static TipoMovimentacao fromValor(String valor) =>
      TipoMovimentacao.values.firstWhere((e) => e.valor == valor);

  String get label => switch (this) {
        TipoMovimentacao.entrada => 'Entrada',
        TipoMovimentacao.saida => 'Saída',
      };
}
