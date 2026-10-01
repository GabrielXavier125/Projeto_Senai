import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../../core/app_colors.dart';
import '../../core/app_exceptions.dart';
import '../../models/reserva.dart';
import '../../repositories/reserva_repository.dart';
import '../../state/session.dart';
import '../../widgets/badges.dart';
import '../../widgets/confirm_dialog.dart';
import '../../widgets/empty_state.dart';
import 'nova_reserva_screen.dart';

/// Equivalente a App\Livewire\Reservas\Index.php.
/// Almoxarife vê todas e pode dar baixa/cancelar; professor vê e cancela só as próprias (RN10).
class ReservasScreen extends StatefulWidget {
  const ReservasScreen({super.key});

  @override
  State<ReservasScreen> createState() => _ReservasScreenState();
}

class _ReservasScreenState extends State<ReservasScreen> {
  final _repository = ReservaRepository();
  List<Reserva> _reservas = [];
  String? _statusFiltro;
  bool _carregando = true;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    setState(() => _carregando = true);
    final usuario = context.read<Session>().usuario!;
    final reservas = await _repository.listar(usuarioLogado: usuario, status: _statusFiltro);

    if (!mounted) return;
    setState(() {
      _reservas = reservas;
      _carregando = false;
    });
  }

  Future<void> _darBaixa(Reserva reserva) async {
    final confirmar = await confirmDialog(
      context,
      titulo: 'Dar baixa na reserva',
      mensagem: 'Confirma a retirada de ${reserva.quantidade}x "${reserva.livroTitulo}" por ${reserva.userNome}?'
          '\n\nIsso registrará uma saída automática no estoque.',
      textoConfirmar: 'Dar baixa',
    );
    if (!confirmar || !mounted) return;

    final usuario = context.read<Session>().usuario!;
    try {
      await _repository.darBaixa(reserva.id, usuario);
      if (!mounted) return;
      showAppSnackBar(context, 'Baixa realizada! Saída registrada no estoque.');
      _carregar();
    } on EstoqueInsuficienteException catch (e) {
      if (mounted) showAppSnackBar(context, e.message, erro: true);
    } on RegraNegocioException catch (e) {
      if (mounted) showAppSnackBar(context, e.message, erro: true);
    }
  }

  Future<void> _cancelar(Reserva reserva) async {
    final confirmar = await confirmDialog(
      context,
      titulo: 'Cancelar reserva',
      mensagem: 'Tem certeza que deseja cancelar esta reserva?',
      textoConfirmar: 'Cancelar reserva',
      destrutivo: true,
    );
    if (!confirmar || !mounted) return;

    final usuario = context.read<Session>().usuario!;
    try {
      await _repository.cancelar(reserva.id, usuario);
      if (!mounted) return;
      showAppSnackBar(context, 'Reserva cancelada.');
      _carregar();
    } on RegraNegocioException catch (e) {
      if (mounted) showAppSnackBar(context, e.message, erro: true);
    }
  }

  Future<void> _abrirNovaReserva() async {
    final criada = await Navigator.of(context).push<bool>(
      MaterialPageRoute(builder: (_) => const NovaReservaScreen()),
    );
    if (criada == true) _carregar();
  }

  @override
  Widget build(BuildContext context) {
    final usuario = context.watch<Session>().usuario!;
    final formatoData = DateFormat('dd/MM/yyyy HH:mm');

    const filtros = <(String, String?)>[
      ('Todas', null),
      ('Pendentes', 'pendente'),
      ('Retiradas', 'retirada'),
      ('Canceladas', 'cancelada'),
    ];

    return Scaffold(
      floatingActionButton: usuario.isCoordenador
          ? FloatingActionButton.extended(
              heroTag: 'fab_reservas',
              onPressed: _abrirNovaReserva,
              icon: const Icon(Icons.add),
              label: const Text('Nova reserva'),
            )
          : null,
      body: RefreshIndicator(
        onRefresh: _carregar,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(12),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    for (final filtro in filtros) ...[
                      ChoiceChip(
                        label: Text(filtro.$1),
                        selected: _statusFiltro == filtro.$2,
                        onSelected: (_) {
                          setState(() => _statusFiltro = filtro.$2);
                          _carregar();
                        },
                      ),
                      const SizedBox(width: 8),
                    ],
                  ],
                ),
              ),
            ),
            Expanded(
              child: _carregando
                  ? const Center(child: CircularProgressIndicator())
                  : _reservas.isEmpty
                      ? ListView(children: const [
                          EmptyState(icon: Icons.event_note_outlined, message: 'Nenhuma reserva encontrada.'),
                        ])
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(12, 0, 12, 88),
                          itemCount: _reservas.length,
                          itemBuilder: (context, index) => _cartaoReserva(_reservas[index], usuario.id,
                              usuario.isAlmoxarife, formatoData),
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _cartaoReserva(Reserva reserva, int usuarioId, bool isAlmoxarife, DateFormat formatoData) {
    final suficiente = reserva.temEstoqueSuficiente;

    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Expanded(
                  child: Text(reserva.livroTitulo ?? '—', style: const TextStyle(fontWeight: FontWeight.w600)),
                ),
                StatusReservaBadge(status: reserva.status),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              '${isAlmoxarife ? '${reserva.userNome} · ' : ''}${formatoData.format(reserva.dataReserva)}',
              style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
            ),
            if (reserva.observacao != null) ...[
              const SizedBox(height: 4),
              Text(reserva.observacao!, style: const TextStyle(fontSize: 13)),
            ],
            const SizedBox(height: 8),
            Row(
              children: [
                // Quantidade fica vermelha quando o saldo atual não cobre a reserva.
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: suficiente ? AppColors.neutroFundo : AppColors.perigoFundo,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    '${reserva.quantidade}x',
                    style: TextStyle(
                      fontWeight: FontWeight.bold,
                      color: suficiente ? AppColors.neutro : AppColors.perigo,
                    ),
                  ),
                ),
                if (reserva.isPendente && !suficiente) ...[
                  const SizedBox(width: 6),
                  Text('Saldo: ${reserva.livroSaldoAtual}',
                      style: const TextStyle(color: AppColors.perigo, fontSize: 12)),
                ],
                const Spacer(),
                if (reserva.isPendente && isAlmoxarife) ...[
                  TextButton(onPressed: () => _cancelar(reserva), child: const Text('Cancelar')),
                  const SizedBox(width: 4),
                  FilledButton(onPressed: () => _darBaixa(reserva), child: const Text('Dar baixa')),
                ] else if (reserva.isPendente && reserva.userId == usuarioId)
                  TextButton(onPressed: () => _cancelar(reserva), child: const Text('Cancelar')),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
