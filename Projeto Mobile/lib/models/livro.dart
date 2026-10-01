class Livro {
  final int id;
  final String titulo;
  final String isbn;
  final String materia;
  final int saldoAtual;
  final int estoqueMinimo;

  const Livro({
    required this.id,
    required this.titulo,
    required this.isbn,
    required this.materia,
    required this.saldoAtual,
    required this.estoqueMinimo,
  });

  /// RF8/RN6 — livro com saldo abaixo ou igual ao mínimo configurado.
  bool get estaBaixoEstoque => saldoAtual <= estoqueMinimo;

  /// RN1 — usado antes de qualquer saída para garantir que o estoque não fique negativo.
  bool temSaldoSuficiente(int quantidade) => saldoAtual >= quantidade;

  factory Livro.fromMap(Map<String, Object?> map) => Livro(
        id: map['id'] as int,
        titulo: map['titulo'] as String,
        isbn: map['isbn'] as String,
        materia: map['materia'] as String,
        saldoAtual: map['saldo_atual'] as int,
        estoqueMinimo: map['estoque_minimo'] as int,
      );

  Map<String, Object?> toMap() => {
        'id': id,
        'titulo': titulo,
        'isbn': isbn,
        'materia': materia,
        'saldo_atual': saldoAtual,
        'estoque_minimo': estoqueMinimo,
      };
}
