import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../models/usuario.dart';
import '../repositories/auth_repository.dart';

/// Sessão do usuário autenticado — equivalente à sessão web (Auth::user()),
/// mas persistida localmente via SharedPreferences (guarda só o id do usuário).
class Session extends ChangeNotifier {
  static const _chaveUsuarioId = 'usuario_id_logado';

  final AuthRepository _authRepository;
  Usuario? _usuario;
  bool _carregando = true;

  Session({AuthRepository? authRepository}) : _authRepository = authRepository ?? AuthRepository();

  Usuario? get usuario => _usuario;
  bool get autenticado => _usuario != null;
  bool get carregando => _carregando;

  Future<void> restaurar() async {
    final prefs = await SharedPreferences.getInstance();
    final id = prefs.getInt(_chaveUsuarioId);

    if (id != null) {
      _usuario = await _authRepository.buscarPorId(id);
    }

    _carregando = false;
    notifyListeners();
  }

  Future<String?> login(String email, String senha) async {
    final usuario = await _authRepository.login(email, senha);
    if (usuario == null) {
      return 'E-mail ou senha incorretos.';
    }

    _usuario = usuario;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_chaveUsuarioId, usuario.id);
    notifyListeners();
    return null;
  }

  Future<void> logout() async {
    _usuario = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_chaveUsuarioId);
    notifyListeners();
  }
}
