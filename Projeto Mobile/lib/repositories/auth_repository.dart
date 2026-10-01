import 'package:sqflite/sqflite.dart';

import '../core/password_hasher.dart';
import '../data/app_database.dart';
import '../models/usuario.dart';

/// Equivalente ao AuthService::login() do projeto original — aqui sem tokens,
/// já que a sessão é local (guardamos apenas o id do usuário logado).
class AuthRepository {
  Future<Database> get _db async => AppDatabase.instance.database;

  Future<Usuario?> login(String email, String senha) async {
    final db = await _db;
    final rows = await db.query('users', where: 'email = ?', whereArgs: [email]);

    if (rows.isEmpty) return null;

    final usuario = Usuario.fromMap(rows.first);
    if (!PasswordHasher.check(senha, usuario.senhaHash)) return null;

    return usuario;
  }

  Future<Usuario?> buscarPorId(int id) async {
    final db = await _db;
    final rows = await db.query('users', where: 'id = ?', whereArgs: [id]);
    if (rows.isEmpty) return null;
    return Usuario.fromMap(rows.first);
  }
}
