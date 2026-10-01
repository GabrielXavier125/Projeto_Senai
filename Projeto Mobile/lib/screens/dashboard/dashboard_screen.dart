import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../core/app_colors.dart';
import '../../models/status_reserva.dart';
import '../../repositories/dashboard_repository.dart';
import '../../widgets/badges.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/stat_card.dart';

/// Equivalente a App\Livewire\Dashboard.php — exclusivo do almoxarife.
class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final _repository = DashboardRepository();
  late Future<DashboardStats> _future;

  @override
  void initState() {
    super.initState();
    _future = _repository.carregar();
  }

  Future<void> _recarregar() async {
    setState(() => _future = _repository.carregar());
    await _future;
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _recarregar,
      child: FutureBuilder<DashboardStats>(
        future: _future,
        builder: (context, snapshot) {
          if (!snapshot.hasData) {
            return const Center(child: CircularProgressIndicator());
          }

          final stats = snapshot.data!;
          final formatoData = DateFormat('dd/MM HH:mm');

          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 2.2,
                children: [
                  StatCard(titulo: 'Total de títulos', valor: '${stats.totalLivros}', icon: Icons.menu_book, cor: AppColors.primary),
                  StatCard(titulo: 'Baixo estoque', valor: '${stats.baixoEstoque}', icon: Icons.warning_amber_rounded, cor: AppColors.alerta),
                  StatCard(titulo: 'Entradas hoje', valor: '${stats.entradasHoje}', icon: Icons.arrow_downward, cor: AppColors.sucesso),
                  StatCard(titulo: 'Saídas hoje', valor: '${stats.saidasHoje}', icon: Icons.arrow_upward, cor: AppColors.perigo),
                ],
              ),
              const SizedBox(height: 24),
              const Text('Baixo estoque', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 8),
              if (stats.livrosBaixo.isEmpty)
                const EmptyState(icon: Icons.check_circle_outline, message: 'Nenhum livro abaixo do mínimo.')
              else
                Card(
                  child: Column(
                    children: [
                      for (final livro in stats.livrosBaixo)
                        ListTile(
                          title: Text(livro.titulo),
                          subtitle: Text(livro.materia),
                          trailing: SaldoBadge(livro: livro),
                        ),
                    ],
                  ),
                ),
              const SizedBox(height: 24),
              const Text('Movimentações recentes', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 8),
              if (stats.movimentacoesRecentes.isEmpty)
                const EmptyState(icon: Icons.inbox_outlined, message: 'Nenhuma movimentação registrada ainda.')
              else
                Card(
                  child: Column(
                    children: [
                      for (final mov in stats.movimentacoesRecentes)
                        ListTile(
                          title: Text(mov.livroTitulo ?? '—'),
                          subtitle: Text('${mov.userNome ?? '—'} · ${formatoData.format(mov.dataHora)}'),
                          trailing: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              TipoMovimentacaoBadge(tipo: mov.tipo),
                              const SizedBox(height: 2),
                              Text('${mov.quantidade}x', style: const TextStyle(fontSize: 11, color: Colors.grey)),
                            ],
                          ),
                        ),
                    ],
                  ),
                ),
              const SizedBox(height: 24),
              const Text('Reservas pendentes', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 8),
              if (stats.reservasPendentes.isEmpty)
                const EmptyState(icon: Icons.event_available_outlined, message: 'Nenhuma reserva pendente.')
              else
                Card(
                  child: Column(
                    children: [
                      for (final reserva in stats.reservasPendentes)
                        ListTile(
                          title: Text(reserva.livroTitulo ?? '—'),
                          subtitle: Text('${reserva.userNome ?? '—'} · ${reserva.quantidade}x'),
                          trailing: const StatusReservaBadge(status: StatusReserva.pendente),
                        ),
                    ],
                  ),
                ),
              const SizedBox(height: 16),
            ],
          );
        },
      ),
    );
  }
}
