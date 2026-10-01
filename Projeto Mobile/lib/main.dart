import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/app_theme.dart';
import 'screens/home_shell.dart';
import 'screens/login_screen.dart';
import 'state/session.dart';

void main() {
  runApp(const SenaiStockApp());
}

class SenaiStockApp extends StatelessWidget {
  const SenaiStockApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => Session()..restaurar(),
      child: MaterialApp(
        title: 'SenaiStock',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light(),
        home: const _RaizApp(),
      ),
    );
  }
}

/// Decide entre Login e o shell principal com base na sessão restaurada.
class _RaizApp extends StatelessWidget {
  const _RaizApp();

  @override
  Widget build(BuildContext context) {
    final session = context.watch<Session>();

    if (session.carregando) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return session.autenticado ? const HomeShell() : const LoginScreen();
  }
}
