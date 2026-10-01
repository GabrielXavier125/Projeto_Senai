/// Notificação de chegada de estoque enviada a professores da matéria (RN9).
class NotificacaoLivro {
  final String id;
  final int userId;
  final int livroId;
  final String livroTitulo;
  final String materia;
  final int quantidade;
  final String mensagem;
  final DateTime? readAt;
  final DateTime createdAt;

  const NotificacaoLivro({
    required this.id,
    required this.userId,
    required this.livroId,
    required this.livroTitulo,
    required this.materia,
    required this.quantidade,
    required this.mensagem,
    required this.createdAt,
    this.readAt,
  });

  bool get lida => readAt != null;

  factory NotificacaoLivro.fromMap(Map<String, Object?> map) => NotificacaoLivro(
        id: map['id'] as String,
        userId: map['user_id'] as int,
        livroId: map['livro_id'] as int,
        livroTitulo: map['livro_titulo'] as String,
        materia: map['materia'] as String,
        quantidade: map['quantidade'] as int,
        mensagem: map['mensagem'] as String,
        createdAt: DateTime.parse(map['created_at'] as String),
        readAt: map['read_at'] == null ? null : DateTime.parse(map['read_at'] as String),
      );
}
