import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/app_colors.dart';
import '../../core/app_exceptions.dart';
import '../../models/livro.dart';
import '../../repositories/livro_repository.dart';
import '../../repositories/reserva_repository.dart';
import '../../state/session.dart';
import '../../widgets/badges.dart';
import '../../widgets/confirm_dialog.dart';
import '../../widgets/empty_state.dart';

class _SelecaoItem {
  bool selecionado = false;
  int quantidade = 1;
}

/// Equivalente a App\Livewire\Reservas\Nova.php — carrinho de solicitação:
/// o professor marca livros da própria matéria + quantidade, tudo em um único envio (RN7).
class NovaReservaScreen extends StatefulWidget {
  const NovaReservaScreen({super.key});

  @override
  State<NovaReservaScreen> createState() => _NovaReservaScreenState();
}

class _NovaReservaScreenState extends State<NovaReservaScreen> {
  final _livroRepository = LivroRepository();
  final _reservaRepository = ReservaRepository();
  final _observacaoController = TextEditingController();

  List<Livro> _livros = [];
  final Map<int, _SelecaoItem> _selecao = {};
  bool _carregando = true;
  bool _enviando = false;
  String? _erro;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  @override
  void dispose() {
    _observacaoController.dispose();
    super.dispose();
  }

  Future<void> _carregar() async {
    final usuario = context.read<Session>().usuario!;
    final livros = await _livroRepository.listar(usuarioLogado: usuario);

    if (!mounted) return;
    setState(() {
      _livros = livros;
      for (final livro in livros) {
        _selecao[livro.id] = _SelecaoItem();
      }
      _carregando = false;
    });
  }

  List<Livro> get _selecionados => _livros.where((l) => _selecao[l.id]!.selecionado).toList();

  Future<void> _enviar() async {
    setState(() => _erro = null);

    if (_observacaoController.text.trim().isEmpty) {
      setState(() => _erro = 'Informe a turma ou motivo da solicitação.');
      return;
    }

    final itens = [
      for (final livro in _selecionados)
        ItemReserva(livroId: livro.id, quantidade: _selecao[livro.id]!.quantidade),
    ];

    if (itens.isEmpty) {
      setState(() => _erro = 'Selecione pelo menos um livro antes de enviar.');
      return;
    }

    setState(() => _enviando = true);
    final usuario = context.read<Session>().usuario!;

    try {
      final total = await _reservaRepository.criar(
        professor: usuario,
        itens: itens,
        observacao: _observacaoController.text.trim(),
      );

      if (!mounted) return;
      showAppSnackBar(context, 'Solicitação enviada! $total reserva(s) criada(s). O almoxarife será notificado.');
      Navigator.of(context).pop(true);
    } on RegraNegocioException catch (e) {
      setState(() => _erro = e.message);
    } finally {
      if (mounted) setState(() => _enviando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final selecionados = _selecionados;

    return Scaffold(
      appBar: AppBar(title: const Text('Nova Solicitação')),
      body: _carregando
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Expanded(
                  child: ListView(
                    padding: const EdgeInsets.all(12),
                    children: [
                      TextField(
                        controller: _observacaoController,
                        maxLines: 2,
                        decoration: const InputDecoration(
                          labelText: 'Turma / motivo da solicitação',
                          hintText: 'Ex.: Turma DS-01 — 2026',
                        ),
                      ),
                      const SizedBox(height: 16),
                      const Text('Livros da sua matéria', style: TextStyle(fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      if (_livros.isEmpty)
                        const EmptyState(
                          icon: Icons.menu_book_outlined,
                          message: 'Nenhum livro cadastrado para a sua matéria.',
                        ),
                      for (final livro in _livros) _linhaLivro(livro),
                    ],
                  ),
                ),
                SafeArea(
                  child: Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      border: Border(top: BorderSide(color: Colors.grey.shade200)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        if (_erro != null) ...[
                          Text(_erro!, style: const TextStyle(color: AppColors.perigo)),
                          const SizedBox(height: 8),
                        ],
                        // Resumo dinâmico dos livros selecionados.
                        Text(
                          selecionados.isEmpty
                              ? 'Nenhum livro selecionado'
                              : selecionados.map((l) => '${_selecao[l.id]!.quantidade}x ${l.titulo}').join('\n'),
                          style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                        ),
                        const SizedBox(height: 8),
                        ElevatedButton(
                          onPressed: (_enviando || selecionados.isEmpty) ? null : _enviar,
                          child: _enviando
                              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : Text('Enviar solicitação (${selecionados.length})'),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
    );
  }

  Widget _linhaLivro(Livro livro) {
    final item = _selecao[livro.id]!;

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        child: Row(
          children: [
            Checkbox(
              value: item.selecionado,
              onChanged: (v) => setState(() {
                item.selecionado = v ?? false;
                // Desmarcar redefine a quantidade para 1 (igual ao updatedSelecao do Livewire).
                if (!item.selecionado) item.quantidade = 1;
              }),
            ),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(livro.titulo, style: const TextStyle(fontWeight: FontWeight.w600)),
                  const SizedBox(height: 4),
                  SaldoBadge(livro: livro),
                ],
              ),
            ),
            // Quantidade só fica habilitada quando o livro está selecionado.
            IconButton(
              icon: const Icon(Icons.remove_circle_outline),
              onPressed: item.selecionado && item.quantidade > 1 ? () => setState(() => item.quantidade--) : null,
            ),
            Text(
              '${item.quantidade}',
              style: TextStyle(fontWeight: FontWeight.bold, color: item.selecionado ? null : Colors.grey),
            ),
            IconButton(
              icon: const Icon(Icons.add_circle_outline),
              onPressed: item.selecionado ? () => setState(() => item.quantidade++) : null,
            ),
          ],
        ),
      ),
    );
  }
}
