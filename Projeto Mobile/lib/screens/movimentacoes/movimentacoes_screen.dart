import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../../models/livro.dart';
import '../../models/movimentacao.dart';
import '../../repositories/livro_repository.dart';
import '../../repositories/movimentacao_repository.dart';
import '../../widgets/badges.dart';
import '../../widgets/empty_state.dart';
import 'registrar_movimentacao_screen.dart';

/// Equivalente a App\Livewire\Movimentacoes\Index.php — exclusivo do almoxarife.
class MovimentacoesScreen extends StatefulWidget {
  const MovimentacoesScreen({super.key});

  @override
  State<MovimentacoesScreen> createState() => _MovimentacoesScreenState();
}

class _MovimentacoesScreenState extends State<MovimentacoesScreen> {
  final _movimentacaoRepository = MovimentacaoRepository();
  final _livroRepository = LivroRepository();
  final _formatoFiltro = DateFormat('dd/MM/yy');

  List<Movimentacao> _movimentacoes = [];
  List<Livro> _livros = [];
  String? _tipoFiltro;
  int? _livroFiltro;
  DateTime? _dataInicio;
  DateTime? _dataFim;
  bool _carregando = true;

  bool get _filtrosAtivos =>
      _tipoFiltro != null || _livroFiltro != null || _dataInicio != null || _dataFim != null;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  Future<void> _carregar() async {
    setState(() => _carregando = true);
    final movimentacoes = await _movimentacaoRepository.listar(
      tipo: _tipoFiltro,
      livroId: _livroFiltro,
      dataInicio: _dataInicio,
      dataFim: _dataFim,
    );
    final livros = await _livroRepository.buscarTodosParaFiltro();

    if (!mounted) return;
    setState(() {
      _movimentacoes = movimentacoes;
      _livros = livros;
      _carregando = false;
    });
  }

  void _limparFiltros() {
    setState(() {
      _tipoFiltro = null;
      _livroFiltro = null;
      _dataInicio = null;
      _dataFim = null;
    });
    _carregar();
  }

  Future<void> _escolherPeriodo() async {
    final agora = DateTime.now();
    final periodo = await showDateRangePicker(
      context: context,
      firstDate: DateTime(agora.year - 5),
      lastDate: agora,
      initialDateRange: _dataInicio != null && _dataFim != null
          ? DateTimeRange(start: _dataInicio!, end: _dataFim!)
          : null,
    );
    if (periodo == null) return;

    setState(() {
      _dataInicio = periodo.start;
      _dataFim = periodo.end;
    });
    _carregar();
  }

  Future<void> _abrirRegistro() async {
    final registrado = await Navigator.of(context).push<bool>(
      MaterialPageRoute(builder: (_) => const RegistrarMovimentacaoScreen()),
    );
    if (registrado == true) _carregar();
  }

  Widget _chipTipo(String label, String? valor) => ChoiceChip(
        label: Text(label),
        selected: _tipoFiltro == valor,
        onSelected: (_) {
          setState(() => _tipoFiltro = valor);
          _carregar();
        },
      );

  @override
  Widget build(BuildContext context) {
    final formatoData = DateFormat('dd/MM/yyyy HH:mm');
    final periodoLabel = _dataInicio == null
        ? 'Período'
        : '${_formatoFiltro.format(_dataInicio!)} – ${_formatoFiltro.format(_dataFim!)}';

    return Scaffold(
      floatingActionButton: FloatingActionButton.extended(
        heroTag: 'fab_movimentacoes',
        onPressed: _abrirRegistro,
        icon: const Icon(Icons.add),
        label: const Text('Registrar'),
      ),
      body: RefreshIndicator(
        onRefresh: _carregar,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
              child: Row(
                children: [
                  _chipTipo('Todos', null),
                  const SizedBox(width: 8),
                  _chipTipo('Entrada', 'entrada'),
                  const SizedBox(width: 8),
                  _chipTipo('Saída', 'saida'),
                  const Spacer(),
                  if (_filtrosAtivos)
                    IconButton(
                      tooltip: 'Limpar filtros',
                      onPressed: _limparFiltros,
                      icon: const Icon(Icons.filter_alt_off_outlined),
                    ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 8, 12, 12),
              child: Row(
                children: [
                  Expanded(
                    child: DropdownButtonFormField<int?>(
                      // A key força recriar o campo quando os filtros são limpos.
                      key: ValueKey(_livroFiltro),
                      initialValue: _livroFiltro,
                      isExpanded: true,
                      decoration: const InputDecoration(labelText: 'Livro', isDense: true),
                      items: [
                        const DropdownMenuItem<int?>(value: null, child: Text('Todos os livros')),
                        for (final livro in _livros)
                          DropdownMenuItem<int?>(
                            value: livro.id,
                            child: Text(livro.titulo, overflow: TextOverflow.ellipsis),
                          ),
                      ],
                      onChanged: (v) {
                        setState(() => _livroFiltro = v);
                        _carregar();
                      },
                    ),
                  ),
                  const SizedBox(width: 8),
                  OutlinedButton.icon(
                    onPressed: _escolherPeriodo,
                    icon: const Icon(Icons.date_range, size: 18),
                    label: Text(periodoLabel),
                  ),
                ],
              ),
            ),
            Expanded(
              child: _carregando
                  ? const Center(child: CircularProgressIndicator())
                  : _movimentacoes.isEmpty
                      ? ListView(children: const [
                          EmptyState(icon: Icons.receipt_long_outlined, message: 'Nenhuma movimentação encontrada.'),
                        ])
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(12, 0, 12, 88),
                          itemCount: _movimentacoes.length,
                          itemBuilder: (context, index) {
                            final mov = _movimentacoes[index];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 10),
                              child: ListTile(
                                title: Text(mov.livroTitulo ?? '—', style: const TextStyle(fontWeight: FontWeight.w600)),
                                subtitle: Text(
                                  '${formatoData.format(mov.dataHora)} · ${mov.userNome ?? '—'}'
                                  '${mov.observacao != null ? '\n${mov.observacao}' : ''}',
                                ),
                                isThreeLine: mov.observacao != null,
                                trailing: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  crossAxisAlignment: CrossAxisAlignment.end,
                                  children: [
                                    TipoMovimentacaoBadge(tipo: mov.tipo),
                                    const SizedBox(height: 4),
                                    Text('${mov.quantidade}x'),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }
}
