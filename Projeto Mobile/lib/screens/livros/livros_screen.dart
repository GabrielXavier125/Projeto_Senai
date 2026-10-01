import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/app_exceptions.dart';
import '../../models/livro.dart';
import '../../repositories/livro_repository.dart';
import '../../state/session.dart';
import '../../widgets/badges.dart';
import '../../widgets/confirm_dialog.dart';
import '../../widgets/empty_state.dart';
import 'livro_form_screen.dart';

/// Equivalente a App\Livewire\Livros\Index.php.
/// Almoxarife: CRUD completo. Professor: somente leitura, filtrado pela própria matéria (RN8).
class LivrosScreen extends StatefulWidget {
  const LivrosScreen({super.key});

  @override
  State<LivrosScreen> createState() => _LivrosScreenState();
}

class _LivrosScreenState extends State<LivrosScreen> {
  final _repository = LivroRepository();
  final _buscaController = TextEditingController();

  List<Livro> _livros = [];
  List<String> _materias = [];
  String? _materiaFiltro;
  bool _carregando = true;

  @override
  void initState() {
    super.initState();
    _carregar();
  }

  @override
  void dispose() {
    _buscaController.dispose();
    super.dispose();
  }

  Future<void> _carregar() async {
    setState(() => _carregando = true);
    final usuario = context.read<Session>().usuario!;

    final livros = await _repository.listar(
      usuarioLogado: usuario,
      busca: _buscaController.text.trim(),
      materiaFiltro: _materiaFiltro,
    );
    final materias = usuario.isAlmoxarife ? await _repository.listarMaterias() : <String>[];

    if (!mounted) return;
    setState(() {
      _livros = livros;
      _materias = materias;
      _carregando = false;
    });
  }

  Future<void> _excluir(Livro livro) async {
    final confirmar = await confirmDialog(
      context,
      titulo: 'Excluir livro',
      mensagem: 'Tem certeza que deseja excluir "${livro.titulo}"?',
      textoConfirmar: 'Excluir',
      destrutivo: true,
    );
    if (!confirmar) return;

    try {
      await _repository.excluir(livro.id);
      if (!mounted) return;
      showAppSnackBar(context, 'Livro excluído com sucesso.');
      _carregar();
    } on RegraNegocioException catch (e) {
      if (mounted) showAppSnackBar(context, e.message, erro: true);
    }
  }

  Future<void> _abrirFormulario({Livro? livro}) async {
    final salvo = await Navigator.of(context).push<bool>(
      MaterialPageRoute(builder: (_) => LivroFormScreen(livro: livro)),
    );
    if (salvo == true) _carregar();
  }

  @override
  Widget build(BuildContext context) {
    final usuario = context.watch<Session>().usuario!;

    return Scaffold(
      floatingActionButton: usuario.isAlmoxarife
          ? FloatingActionButton.extended(
              heroTag: 'fab_livros',
              onPressed: () => _abrirFormulario(),
              icon: const Icon(Icons.add),
              label: const Text('Novo livro'),
            )
          : null,
      body: RefreshIndicator(
        onRefresh: _carregar,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                children: [
                  TextField(
                    controller: _buscaController,
                    decoration: InputDecoration(
                      hintText: usuario.isAlmoxarife
                          ? 'Buscar por título, ISBN ou matéria...'
                          : 'Buscar por título ou ISBN...',
                      prefixIcon: const Icon(Icons.search),
                    ),
                    onChanged: (_) => _carregar(),
                  ),
                  if (usuario.isCoordenador && usuario.materia != null) ...[
                    const SizedBox(height: 8),
                    Align(
                      alignment: Alignment.centerLeft,
                      child: Text(
                        'Mostrando livros de ${usuario.materia}',
                        style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                      ),
                    ),
                  ],
                  if (usuario.isAlmoxarife && _materias.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(
                        children: [
                          ChoiceChip(
                            label: const Text('Todas as matérias'),
                            selected: _materiaFiltro == null,
                            onSelected: (_) {
                              setState(() => _materiaFiltro = null);
                              _carregar();
                            },
                          ),
                          for (final materia in _materias) ...[
                            const SizedBox(width: 8),
                            ChoiceChip(
                              label: Text(materia),
                              selected: _materiaFiltro == materia,
                              onSelected: (_) {
                                setState(() => _materiaFiltro = materia);
                                _carregar();
                              },
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
            Expanded(
              child: _carregando
                  ? const Center(child: CircularProgressIndicator())
                  : _livros.isEmpty
                      ? ListView(children: const [
                          EmptyState(icon: Icons.menu_book_outlined, message: 'Nenhum livro encontrado.'),
                        ])
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(12, 0, 12, 88),
                          itemCount: _livros.length,
                          itemBuilder: (context, index) {
                            final livro = _livros[index];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 10),
                              child: ListTile(
                                onTap: usuario.isAlmoxarife ? () => _abrirFormulario(livro: livro) : null,
                                title: Text(livro.titulo, style: const TextStyle(fontWeight: FontWeight.w600)),
                                subtitle: Text('${livro.materia} · ISBN ${livro.isbn}'),
                                trailing: usuario.isAlmoxarife
                                    ? PopupMenuButton<String>(
                                        onSelected: (v) {
                                          if (v == 'editar') _abrirFormulario(livro: livro);
                                          if (v == 'excluir') _excluir(livro);
                                        },
                                        itemBuilder: (context) => const [
                                          PopupMenuItem(value: 'editar', child: Text('Editar')),
                                          PopupMenuItem(value: 'excluir', child: Text('Excluir')),
                                        ],
                                        child: SaldoBadge(livro: livro),
                                      )
                                    : SaldoBadge(livro: livro),
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
