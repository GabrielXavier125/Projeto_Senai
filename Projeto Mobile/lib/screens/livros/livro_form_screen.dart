import 'package:flutter/material.dart';

import '../../core/app_exceptions.dart';
import '../../models/livro.dart';
import '../../repositories/livro_repository.dart';
import '../../widgets/confirm_dialog.dart';

/// Equivalente a App\Livewire\Livros\Criar.php e Editar.php.
class LivroFormScreen extends StatefulWidget {
  final Livro? livro;
  const LivroFormScreen({super.key, this.livro});

  bool get editando => livro != null;

  @override
  State<LivroFormScreen> createState() => _LivroFormScreenState();
}

class _LivroFormScreenState extends State<LivroFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _repository = LivroRepository();

  late final _tituloController = TextEditingController(text: widget.livro?.titulo ?? '');
  late final _isbnController = TextEditingController(text: widget.livro?.isbn ?? '');
  late final _materiaController = TextEditingController(text: widget.livro?.materia ?? '');
  late final _estoqueMinimoController =
      TextEditingController(text: '${widget.livro?.estoqueMinimo ?? 10}');

  bool _salvando = false;

  @override
  void dispose() {
    _tituloController.dispose();
    _isbnController.dispose();
    _materiaController.dispose();
    _estoqueMinimoController.dispose();
    super.dispose();
  }

  Future<void> _salvar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _salvando = true);

    try {
      if (widget.editando) {
        await _repository.atualizar(
          id: widget.livro!.id,
          titulo: _tituloController.text.trim(),
          isbn: _isbnController.text.trim(),
          materia: _materiaController.text.trim(),
          estoqueMinimo: int.parse(_estoqueMinimoController.text),
        );
      } else {
        await _repository.criar(
          titulo: _tituloController.text.trim(),
          isbn: _isbnController.text.trim(),
          materia: _materiaController.text.trim(),
          estoqueMinimo: int.parse(_estoqueMinimoController.text),
        );
      }

      if (!mounted) return;
      showAppSnackBar(context, widget.editando ? 'Livro atualizado com sucesso!' : 'Livro cadastrado com sucesso!');
      Navigator.of(context).pop(true);
    } on RegraNegocioException catch (e) {
      if (mounted) showAppSnackBar(context, e.message, erro: true);
    } finally {
      if (mounted) setState(() => _salvando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.editando ? 'Editar Livro' : 'Novo Livro')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextFormField(
                controller: _tituloController,
                decoration: const InputDecoration(labelText: 'Título'),
                validator: (v) => (v == null || v.trim().isEmpty) ? 'O título é obrigatório.' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _isbnController,
                decoration: const InputDecoration(labelText: 'ISBN'),
                validator: (v) => (v == null || v.trim().isEmpty) ? 'O ISBN é obrigatório.' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _materiaController,
                decoration: const InputDecoration(labelText: 'Matéria'),
                validator: (v) => (v == null || v.trim().isEmpty) ? 'A matéria é obrigatória.' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _estoqueMinimoController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'Estoque mínimo'),
                validator: (v) {
                  final n = int.tryParse(v ?? '');
                  if (n == null || n < 0) return 'Informe um número válido (>= 0).';
                  return null;
                },
              ),
              if (widget.editando) ...[
                const SizedBox(height: 12),
                Text(
                  'Saldo atual: ${widget.livro!.saldoAtual} — o saldo só muda por movimentações.',
                  style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                ),
              ],
              const SizedBox(height: 24),
              ElevatedButton(
                onPressed: _salvando ? null : _salvar,
                child: _salvando
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                    : const Text('Salvar'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
