import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/app_colors.dart';
import '../../core/app_exceptions.dart';
import '../../models/livro.dart';
import '../../repositories/estoque_repository.dart';
import '../../repositories/livro_repository.dart';
import '../../repositories/notificacao_repository.dart';
import '../../state/session.dart';
import '../../widgets/badges.dart';
import '../../widgets/confirm_dialog.dart';

/// Equivalente a App\Livewire\Movimentacoes\Registrar.php.
class RegistrarMovimentacaoScreen extends StatefulWidget {
  const RegistrarMovimentacaoScreen({super.key});

  @override
  State<RegistrarMovimentacaoScreen> createState() => _RegistrarMovimentacaoScreenState();
}

class _RegistrarMovimentacaoScreenState extends State<RegistrarMovimentacaoScreen> {
  final _formKey = GlobalKey<FormState>();
  final _livroRepository = LivroRepository();
  final _estoqueRepository = EstoqueRepository();
  final _notificacaoRepository = NotificacaoRepository();
  final _quantidadeController = TextEditingController(text: '1');
  final _observacaoController = TextEditingController();

  List<Livro> _livros = [];
  Livro? _livroSelecionado;
  String _tipo = 'entrada';
  bool _carregandoLivros = true;
  bool _salvando = false;
  String? _erroQuantidade;

  @override
  void initState() {
    super.initState();
    _carregarLivros();
  }

  @override
  void dispose() {
    _quantidadeController.dispose();
    _observacaoController.dispose();
    super.dispose();
  }

  Future<void> _carregarLivros() async {
    final livros = await _livroRepository.buscarTodosParaFiltro();
    if (!mounted) return;
    setState(() {
      _livros = livros;
      _carregandoLivros = false;
    });
  }

  Future<void> _escolherLivro() async {
    final escolhido = await showModalBottomSheet<Livro>(
      context: context,
      isScrollControlled: true,
      builder: (_) => _SeletorLivro(livros: _livros),
    );
    if (escolhido != null) setState(() => _livroSelecionado = escolhido);
  }

  Future<void> _registrar() async {
    setState(() => _erroQuantidade = null);

    if (_livroSelecionado == null) {
      showAppSnackBar(context, 'Selecione um livro.', erro: true);
      return;
    }
    if (!_formKey.currentState!.validate()) return;

    final quantidade = int.parse(_quantidadeController.text);
    final observacao = _observacaoController.text.trim();
    final livro = _livroSelecionado!;

    setState(() => _salvando = true);
    final usuario = context.read<Session>().usuario!;

    try {
      if (_tipo == 'entrada') {
        await _estoqueRepository.registrarEntrada(
          livro: livro,
          quantidade: quantidade,
          usuario: usuario,
          observacao: observacao,
        );

        // RN9: avisa os professores da matéria que chegou estoque.
        final notificados = await _notificacaoRepository.notificarProfessoresDaMateria(livro, quantidade);

        if (!mounted) return;
        final extra = notificados > 0 ? ' $notificados professor(es) de ${livro.materia} notificado(s).' : '';
        showAppSnackBar(context, 'Entrada registrada com sucesso!$extra');
      } else {
        await _estoqueRepository.registrarSaida(
          livro: livro,
          quantidade: quantidade,
          usuario: usuario,
          observacao: observacao,
        );

        if (!mounted) return;
        showAppSnackBar(context, 'Saída registrada com sucesso!');
      }

      Navigator.of(context).pop(true);
    } on EstoqueInsuficienteException catch (e) {
      setState(() => _erroQuantidade = e.message);
    } on QuantidadeInvalidaException catch (e) {
      setState(() => _erroQuantidade = e.message);
    } finally {
      if (mounted) setState(() => _salvando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Registrar Movimentação')),
      body: _carregandoLivros
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    InkWell(
                      onTap: _escolherLivro,
                      borderRadius: BorderRadius.circular(10),
                      child: InputDecorator(
                        decoration: const InputDecoration(
                          labelText: 'Livro',
                          suffixIcon: Icon(Icons.arrow_drop_down),
                        ),
                        child: Text(
                          _livroSelecionado?.titulo ?? 'Toque para escolher ou buscar...',
                          style: TextStyle(color: _livroSelecionado == null ? Colors.grey.shade600 : null),
                        ),
                      ),
                    ),
                    if (_livroSelecionado != null) ...[
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Text(_livroSelecionado!.materia, style: TextStyle(color: Colors.grey.shade600)),
                          const SizedBox(width: 8),
                          SaldoBadge(livro: _livroSelecionado!),
                        ],
                      ),
                    ],
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: _CartaoTipo(
                            label: 'Entrada',
                            icon: Icons.arrow_downward,
                            cor: AppColors.sucesso,
                            selecionado: _tipo == 'entrada',
                            onTap: () => setState(() => _tipo = 'entrada'),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: _CartaoTipo(
                            label: 'Saída',
                            icon: Icons.arrow_upward,
                            cor: AppColors.perigo,
                            selecionado: _tipo == 'saida',
                            onTap: () => setState(() => _tipo = 'saida'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _quantidadeController,
                      keyboardType: TextInputType.number,
                      decoration: InputDecoration(labelText: 'Quantidade', errorText: _erroQuantidade),
                      validator: (v) {
                        final n = int.tryParse(v ?? '');
                        if (n == null || n < 1) return 'A quantidade deve ser maior que zero.';
                        return null;
                      },
                    ),
                    const SizedBox(height: 12),
                    TextFormField(
                      controller: _observacaoController,
                      maxLines: 3,
                      decoration: InputDecoration(
                        labelText: _tipo == 'saida' ? 'Observação (obrigatória)' : 'Observação (opcional)',
                        hintText: _tipo == 'saida' ? 'Ex.: Turma DS-01 — 2026' : 'Ex.: NF 1234',
                      ),
                      validator: (v) => (_tipo == 'saida' && (v == null || v.trim().isEmpty))
                          ? 'A observação é obrigatória para saídas.'
                          : null,
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: _salvando ? null : _registrar,
                      child: _salvando
                          ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : const Text('Registrar'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}

/// Combobox do livro: lista todos agrupados por matéria e filtra ao digitar
/// (equivalente ao combobox Alpine.js da tela web de Registrar).
class _SeletorLivro extends StatefulWidget {
  final List<Livro> livros;
  const _SeletorLivro({required this.livros});

  @override
  State<_SeletorLivro> createState() => _SeletorLivroState();
}

class _SeletorLivroState extends State<_SeletorLivro> {
  String _busca = '';

  @override
  Widget build(BuildContext context) {
    final termo = _busca.toLowerCase();
    final filtrados = widget.livros
        .where((l) => l.titulo.toLowerCase().contains(termo) || l.materia.toLowerCase().contains(termo))
        .toList();

    final itens = <Widget>[];
    String? materiaAtual;
    for (final livro in filtrados) {
      if (livro.materia != materiaAtual) {
        materiaAtual = livro.materia;
        itens.add(Padding(
          padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
          child: Text(
            livro.materia.toUpperCase(),
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: AppColors.primary),
          ),
        ));
      }
      itens.add(ListTile(
        title: Text(livro.titulo),
        trailing: SaldoBadge(livro: livro),
        onTap: () => Navigator.of(context).pop(livro),
      ));
    }

    return Padding(
      padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SafeArea(
      child: SizedBox(
        height: MediaQuery.of(context).size.height * 0.8,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
              child: TextField(
                decoration: const InputDecoration(
                  hintText: 'Buscar por título ou matéria...',
                  prefixIcon: Icon(Icons.search),
                ),
                onChanged: (v) => setState(() => _busca = v),
              ),
            ),
            Expanded(
              child: itens.isEmpty
                  ? const Center(child: Text('Nenhum livro encontrado.'))
                  : ListView(children: itens),
            ),
          ],
        ),
      ),
      ),
    );
  }
}

class _CartaoTipo extends StatelessWidget {
  final String label;
  final IconData icon;
  final Color cor;
  final bool selecionado;
  final VoidCallback onTap;

  const _CartaoTipo({
    required this.label,
    required this.icon,
    required this.cor,
    required this.selecionado,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 18),
        decoration: BoxDecoration(
          color: selecionado ? cor.withValues(alpha: 0.12) : Colors.white,
          border: Border.all(color: selecionado ? cor : Colors.grey.shade300, width: selecionado ? 2 : 1),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Icon(icon, color: cor),
            const SizedBox(height: 6),
            Text(label, style: TextStyle(color: cor, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }
}
