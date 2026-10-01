import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/app_colors.dart';
import '../../models/notificacao.dart';
import '../../repositories/notificacao_repository.dart';
import '../../state/session.dart';
import '../../widgets/confirm_dialog.dart';
import '../../widgets/empty_state.dart';

/// Equivalente a App\Livewire\Notificacoes\Index.php — exclusivo do professor.
/// Marca automaticamente todas como lidas ao abrir (RN9).
class NotificacoesScreen extends StatefulWidget {
  const NotificacoesScreen({super.key});

  @override
  State<NotificacoesScreen> createState() => _NotificacoesScreenState();
}

class _NotificacoesScreenState extends State<NotificacoesScreen> {
  final _repository = NotificacaoRepository();
  List<NotificacaoLivro> _notificacoes = [];
  bool _carregando = true;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    setState(() => _carregando = true);
    final usuario = context.read<Session>().usuario!;
    final notificacoes = await _repository.listarEMarcarLidas(usuario.id);

    if (!mounted) return;
    setState(() {
      _notificacoes = notificacoes;
      _carregando = false;
    });
  }

  Future<void> _excluir(NotificacaoLivro notificacao) async {
    setState(() => _notificacoes.removeWhere((n) => n.id == notificacao.id));
    await _repository.excluir(notificacao.id);
    if (mounted) showAppSnackBar(context, 'Notificação removida.');
  }

  @override
  Widget build(BuildContext context) {
    final formatoData = DateFormat('dd/MM/yyyy HH:mm');

    return RefreshIndicator(
      onRefresh: _carregar,
      child: _carregando
          ? const Center(child: CircularProgressIndicator())
          : _notificacoes.isEmpty
              ? ListView(
                  children: const [
                    EmptyState(
                      icon: Icons.notifications_none,
                      message: 'Nenhuma notificação por aqui.\nVocê será avisado quando chegarem livros da sua matéria.',
                    ),
                  ],
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(12),
                  itemCount: _notificacoes.length,
                  itemBuilder: (context, index) {
                    final notificacao = _notificacoes[index];
                    return Dismissible(
                      key: ValueKey(notificacao.id),
                      direction: DismissDirection.endToStart,
                      onDismissed: (_) => _excluir(notificacao),
                      background: Container(
                        alignment: Alignment.centerRight,
                        padding: const EdgeInsets.only(right: 20),
                        margin: const EdgeInsets.only(bottom: 10),
                        decoration: BoxDecoration(color: AppColors.perigo, borderRadius: BorderRadius.circular(14)),
                        child: const Icon(Icons.delete_outline, color: Colors.white),
                      ),
                      child: Card(
                        margin: const EdgeInsets.only(bottom: 10),
                        child: ListTile(
                          leading: const CircleAvatar(
                            backgroundColor: AppColors.sucessoFundo,
                            child: Icon(Icons.inventory_2_outlined, color: AppColors.sucesso),
                          ),
                          title: Text(notificacao.mensagem),
                          subtitle: Text(formatoData.format(notificacao.createdAt)),
                          trailing: IconButton(
                            tooltip: 'Remover',
                            icon: const Icon(Icons.close),
                            onPressed: () => _excluir(notificacao),
                          ),
                        ),
                      ),
                    );
                  },
                ),
    );
  }
}
