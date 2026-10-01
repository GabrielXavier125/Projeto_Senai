import 'tipo_movimentacao.dart';

/// Registro imutável de uma entrada ou saída de estoque (RN4: sempre grava
/// autor + timestamp; nunca é editado ou excluído após criado).
class Movimentacao {
  final int id;
  final int livroId;
  final int userId;
  final TipoMovimentacao tipo;
  final int quantidade;
  final String? observacao;
  final DateTime dataHora;

  // Campos desnormalizados só para exibição (equivalentes ao ->load('livro','user') do Laravel).
  final String? livroTitulo;
  final String? userNome;

  const Movimentacao({
    required this.id,
    required this.livroId,
    required this.userId,
    required this.tipo,
    required this.quantidade,
    required this.dataHora,
    this.observacao,
    this.livroTitulo,
    this.userNome,
  });

  factory Movimentacao.fromMap(Map<String, Object?> map) => Movimentacao(
        id: map['id'] as int,
        livroId: map['livro_id'] as int,
        userId: map['user_id'] as int,
        tipo: TipoMovimentacao.fromValor(map['tipo'] as String),
        quantidade: map['quantidade'] as int,
        observacao: map['observacao'] as String?,
        dataHora: DateTime.parse(map['data_hora'] as String),
        livroTitulo: map['livro_titulo'] as String?,
        userNome: map['user_nome'] as String?,
      );
}
