import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/usuario.dart';
import '../repositories/notificacao_repository.dart';
import '../state/session.dart';
import 'dashboard/dashboard_screen.dart';
import 'livros/livros_screen.dart';
import 'movimentacoes/movimentacoes_screen.dart';
import 'notificacoes/notificacoes_screen.dart';
import 'reservas/reservas_screen.dart';
import 'usuarios/usuarios_screen.dart';

class _Aba {
  final String label;
  final String navLabel;
  final IconData icon;
  final Widget Function() builder;
  const _Aba({required this.label, String? navLabel, required this.icon, required this.builder})
      : navLabel = navLabel ?? label;
}

/// Navegação principal — os itens visíveis dependem do perfil logado,
/// espelhando a sidebar dinâmica de resources/views/layouts/app.blade.php.
class HomeShell extends StatefulWidget {
  const HomeShell({super.key});

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _abaAtual = 0;
  final _notificacaoRepository = NotificacaoRepository();
  int _naoLidas = 0;

  List<_Aba> _abasPara(Usuario usuario) {
    if (usuario.isAlmoxarife) {
      return [
        _Aba(label: 'Dashboard', icon: Icons.dashboard_outlined, builder: () => const DashboardScreen()),
        _Aba(label: 'Livros', icon: Icons.menu_book_outlined, builder: () => const LivrosScreen()),
        _Aba(label: 'Movimentações', navLabel: 'Estoque', icon: Icons.swap_vert, builder: () => const MovimentacoesScreen()),
        _Aba(label: 'Reservas', icon: Icons.event_note_outlined, builder: () => const ReservasScreen()),
        _Aba(label: 'Usuários', icon: Icons.people_outline, builder: () => const UsuariosScreen()),
      ];
    }

    // Professor não tem Dashboard nem Movimentações — cai direto em Livros.
    return [
      _Aba(label: 'Livros', icon: Icons.menu_book_outlined, builder: () => const LivrosScreen()),
      _Aba(label: 'Reservas', icon: Icons.event_note_outlined, builder: () => const ReservasScreen()),
      _Aba(label: 'Notificações', icon: Icons.notifications_outlined, builder: () => const NotificacoesScreen()),
    ];
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    _atualizarContadorNotificacoes();
  }

  Future<void> _atualizarContadorNotificacoes() async {
    final usuario = context.read<Session>().usuario;
    if (usuario == null || !usuario.isCoordenador) return;
    final total = await _notificacaoRepository.contarNaoLidas(usuario.id);
    if (mounted) setState(() => _naoLidas = total);
  }

  @override
  Widget build(BuildContext context) {
    final usuario = context.watch<Session>().usuario!;
    final abas = _abasPara(usuario);
    final abaAtual = _abaAtual < abas.length ? _abaAtual : 0;

    return Scaffold(
      appBar: AppBar(
        title: Text(abas[abaAtual].label),
        actions: [
          PopupMenuButton<String>(
            icon: const CircleAvatar(
              backgroundColor: Colors.white24,
              child: Icon(Icons.person, color: Colors.white),
            ),
            onSelected: (value) {
              if (value == 'sair') context.read<Session>().logout();
            },
            itemBuilder: (context) => [
              PopupMenuItem(
                enabled: false,
                child: Text('${usuario.nome}\n${usuario.perfil.label}',
                    style: const TextStyle(fontWeight: FontWeight.bold)),
              ),
              const PopupMenuDivider(),
              const PopupMenuItem(value: 'sair', child: Text('Sair')),
            ],
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: IndexedStack(
        index: abaAtual,
        children: abas.map((a) => a.builder()).toList(),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: abaAtual,
        onDestinationSelected: (i) {
          setState(() => _abaAtual = i);
          _atualizarContadorNotificacoes();
        },
        destinations: [
          for (final aba in abas)
            NavigationDestination(
              icon: aba.label == 'Notificações' && _naoLidas > 0
                  ? Badge(label: Text('$_naoLidas'), child: Icon(aba.icon))
                  : Icon(aba.icon),
              label: aba.navLabel,
            ),
        ],
      ),
    );
  }
}
