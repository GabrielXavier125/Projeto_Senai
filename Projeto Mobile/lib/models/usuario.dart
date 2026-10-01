import 'perfil_usuario.dart';

class Usuario {
  final int id;
  final String nome;
  final String email;
  final String senhaHash;
  final PerfilUsuario perfil;
  final String? materia;

  const Usuario({
    required this.id,
    required this.nome,
    required this.email,
    required this.senhaHash,
    required this.perfil,
    this.materia,
  });

  bool get isAlmoxarife => perfil == PerfilUsuario.almoxarife;
  bool get isCoordenador => perfil == PerfilUsuario.coordenador;

  factory Usuario.fromMap(Map<String, Object?> map) => Usuario(
        id: map['id'] as int,
        nome: map['nome'] as String,
        email: map['email'] as String,
        senhaHash: map['senha_hash'] as String,
        perfil: PerfilUsuario.fromValor(map['perfil'] as String),
        materia: map['materia'] as String?,
      );

  Map<String, Object?> toMap() => {
        'id': id,
        'nome': nome,
        'email': email,
        'senha_hash': senhaHash,
        'perfil': perfil.valor,
        'materia': materia,
      };
}
