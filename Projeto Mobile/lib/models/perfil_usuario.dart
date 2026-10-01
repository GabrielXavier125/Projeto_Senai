/// Perfis de usuário do sistema.
///
/// Internamente o valor salvo é `almoxarife` / `coordenador` (mesmo nome do
/// projeto original), mas na interface o perfil `coordenador` é sempre
/// apresentado como "Professor".
enum PerfilUsuario {
  almoxarife('almoxarife'),
  coordenador('coordenador');

  final String valor;
  const PerfilUsuario(this.valor);

  static PerfilUsuario fromValor(String valor) =>
      PerfilUsuario.values.firstWhere((e) => e.valor == valor);

  String get label => switch (this) {
        PerfilUsuario.almoxarife => 'Almoxarife',
        PerfilUsuario.coordenador => 'Professor',
      };
}
