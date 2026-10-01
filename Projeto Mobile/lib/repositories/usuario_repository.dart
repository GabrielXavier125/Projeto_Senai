import 'package:sqflite/sqflite.dart';

import '../core/app_exceptions.dart';
import '../core/password_hasher.dart';
import '../data/app_database.dart';
import '../models/perfil_usuario.dart';
import '../models/usuario.dart';

/// Equivalente a App\Livewire\Usuarios\* — gestão de usuários, exclusiva do almoxarife.
class UsuarioRepository {
  Future<Database> get _db async => AppDatabase.instance.database;

  Future<List<Usuario>> listar({String busca = '', String? perfilFiltro}) async {
    final db = await _db;

    final where = <String>[];
    final args = <Object?>[];

    if (busca.isNotEmpty) {
      where.add('(nome LIKE ? OR email LIKE ?)');
      args.addAll(['%$busca%', '%$busca%']);
    }
    if (perfilFiltro != null && perfilFiltro.isNotEmpty) {
      where.add('perfil = ?');
      args.add(perfilFiltro);
    }

    final rows = await db.query(
      'users',
      where: where.isEmpty ? null : where.join(' AND '),
      whereArgs: args.isEmpty ? null : args,
      orderBy: 'perfil, nome',
    );

    return rows.map(Usuario.fromMap).toList();
  }

  Future<void> criar({
    required String nome,
    required String email,
    required String senha,
    required PerfilUsuario perfil,
    String? materia,
  }) async {
    final db = await _db;
    final existente = await db.query('users', where: 'email = ?', whereArgs: [email]);
    if (existente.isNotEmpty) {
      throw const RegraNegocioException('Este e-mail já está cadastrado.');
    }

    await db.insert('users', {
      'nome': nome,
      'email': email,
      'senha_hash': PasswordHasher.hash(senha),
      'perfil': perfil.valor,
      'materia': perfil == PerfilUsuario.coordenador ? materia : null,
    });
  }

  Future<void> atualizar({
    required int id,
    required String nome,
    required String email,
    required PerfilUsuario perfil,
    String? materia,
    String? novaSenha,
  }) async {
    final db = await _db;
    final duplicado = await db.query('users', where: 'email = ? AND id != ?', whereArgs: [email, id]);
    if (duplicado.isNotEmpty) {
      throw const RegraNegocioException('Este e-mail já está em uso por outro usuário.');
    }

    final dados = <String, Object?>{
      'nome': nome,
      'email': email,
      'perfil': perfil.valor,
      'materia': perfil == PerfilUsuario.coordenador ? materia : null,
    };

    if (novaSenha != null && novaSenha.isNotEmpty) {
      dados['senha_hash'] = PasswordHasher.hash(novaSenha);
    }

    await db.update('users', dados, where: 'id = ?', whereArgs: [id]);
  }

  /// Protege contra autoexclusão e contra remover o único almoxarife do sistema.
  Future<void> excluir(int id, {required int usuarioLogadoId}) async {
    if (id == usuarioLogadoId) {
      throw const RegraNegocioException('Você não pode excluir o próprio usuário.');
    }

    final db = await _db;
    final rows = await db.query('users', where: 'id = ?', whereArgs: [id]);
    if (rows.isEmpty) {
      throw const RegraNegocioException('Usuário não encontrado.');
    }

    final usuario = Usuario.fromMap(rows.first);
    if (usuario.isAlmoxarife) {
      final totalAlmoxarifes = Sqflite.firstIntValue(await db.rawQuery(
        "SELECT COUNT(*) FROM users WHERE perfil = 'almoxarife'",
      ));
      if ((totalAlmoxarifes ?? 0) <= 1) {
        throw const RegraNegocioException('Não é possível excluir o único almoxarife do sistema.');
      }
    }

    try {
      await db.delete('users', where: 'id = ?', whereArgs: [id]);
    } on DatabaseException {
      throw const RegraNegocioException(
        'Não é possível excluir este usuário: há movimentações ou reservas registradas em seu nome.',
      );
    }
  }
}
