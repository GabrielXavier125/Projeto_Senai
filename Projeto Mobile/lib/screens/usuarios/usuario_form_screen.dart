import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/app_colors.dart';
import '../../core/app_exceptions.dart';
import '../../models/perfil_usuario.dart';
import '../../models/usuario.dart';
import '../../repositories/livro_repository.dart';
import '../../repositories/usuario_repository.dart';
import '../../state/session.dart';
import '../../widgets/confirm_dialog.dart';

/// Equivalente a App\Livewire\Usuarios\Criar.php e Editar.php.
class UsuarioFormScreen extends StatefulWidget {
  final Usuario? usuario;
  const UsuarioFormScreen({super.key, this.usuario});

  bool get editando => usuario != null;

  @override
  State<UsuarioFormScreen> createState() => _UsuarioFormScreenState();
}

class _UsuarioFormScreenState extends State<UsuarioFormScreen> {
  final _formKey = GlobalKey<FormState>();
  final _usuarioRepository = UsuarioRepository();
  final _livroRepository = LivroRepository();

  late final _nomeController = TextEditingController(text: widget.usuario?.nome ?? '');
  late final _emailController = TextEditingController(text: widget.usuario?.email ?? '');
  final _senhaController = TextEditingController();
  final _confirmacaoController = TextEditingController();
  late final _materiaController = TextEditingController(text: widget.usuario?.materia ?? '');

  late PerfilUsuario _perfil = widget.usuario?.perfil ?? PerfilUsuario.almoxarife;
  List<String> _materiasSugeridas = [];
  bool _salvando = false;

  @override
  void initState() {
    super.initState();
    _livroRepository.listarMaterias().then((m) {
      if (mounted) setState(() => _materiasSugeridas = m);
    });
  }

  @override
  void dispose() {
    _nomeController.dispose();
    _emailController.dispose();
    _senhaController.dispose();
    _confirmacaoController.dispose();
    _materiaController.dispose();
    super.dispose();
  }

  Future<void> _salvar() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _salvando = true);

    final materia = _perfil == PerfilUsuario.coordenador ? _materiaController.text.trim() : null;

    try {
      if (widget.editando) {
        await _usuarioRepository.atualizar(
          id: widget.usuario!.id,
          nome: _nomeController.text.trim(),
          email: _emailController.text.trim(),
          perfil: _perfil,
          materia: materia,
          novaSenha: _senhaController.text.isEmpty ? null : _senhaController.text,
        );
      } else {
        await _usuarioRepository.criar(
          nome: _nomeController.text.trim(),
          email: _emailController.text.trim(),
          senha: _senhaController.text,
          perfil: _perfil,
          materia: materia,
        );
      }

      if (!mounted) return;
      showAppSnackBar(context, widget.editando ? 'Usuário atualizado com sucesso!' : 'Usuário criado com sucesso!');
      Navigator.of(context).pop(true);
    } on RegraNegocioException catch (e) {
      if (mounted) showAppSnackBar(context, e.message, erro: true);
    } finally {
      if (mounted) setState(() => _salvando = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    // Almoxarife não pode alterar o próprio perfil (evita se trancar fora do sistema).
    final editandoASiMesmo = widget.editando && widget.usuario!.id == context.read<Session>().usuario!.id;

    return Scaffold(
      appBar: AppBar(title: Text(widget.editando ? 'Editar Usuário' : 'Novo Usuário')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              TextFormField(
                controller: _nomeController,
                decoration: const InputDecoration(labelText: 'Nome'),
                validator: (v) => (v == null || v.trim().isEmpty) ? 'O nome é obrigatório.' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: const InputDecoration(labelText: 'E-mail'),
                validator: (v) {
                  if (v == null || v.trim().isEmpty) return 'O e-mail é obrigatório.';
                  if (!RegExp(r'^[^@\s]+@[^@\s]+\.[^@\s]+$').hasMatch(v.trim())) return 'Informe um e-mail válido.';
                  return null;
                },
              ),
              const SizedBox(height: 16),
              const Text('Perfil', style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: _CartaoPerfil(
                      label: 'Almoxarife',
                      icon: Icons.inventory_2_outlined,
                      selecionado: _perfil == PerfilUsuario.almoxarife,
                      onTap: editandoASiMesmo ? null : () => setState(() => _perfil = PerfilUsuario.almoxarife),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _CartaoPerfil(
                      label: 'Professor',
                      icon: Icons.school_outlined,
                      selecionado: _perfil == PerfilUsuario.coordenador,
                      onTap: editandoASiMesmo ? null : () => setState(() => _perfil = PerfilUsuario.coordenador),
                    ),
                  ),
                ],
              ),
              if (editandoASiMesmo) ...[
                const SizedBox(height: 6),
                Text('Você não pode alterar o próprio perfil.',
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 12)),
              ],
              if (_perfil == PerfilUsuario.coordenador) ...[
                const SizedBox(height: 16),
                Autocomplete<String>(
                  initialValue: TextEditingValue(text: _materiaController.text),
                  optionsBuilder: (value) => value.text.isEmpty
                      ? _materiasSugeridas
                      : _materiasSugeridas.where((m) => m.toLowerCase().contains(value.text.toLowerCase())),
                  onSelected: (v) => _materiaController.text = v,
                  fieldViewBuilder: (context, controller, focusNode, onSubmit) => TextFormField(
                    controller: controller,
                    focusNode: focusNode,
                    decoration: const InputDecoration(
                      labelText: 'Matéria',
                      helperText: 'Define quais livros o professor vê e de quais recebe notificações.',
                    ),
                    onChanged: (v) => _materiaController.text = v,
                    validator: (v) => (v == null || v.trim().isEmpty) ? 'Informe a matéria do professor.' : null,
                  ),
                ),
              ],
              const SizedBox(height: 16),
              Text(
                widget.editando ? 'Redefinir senha (deixe em branco para manter)' : 'Senha',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _senhaController,
                obscureText: true,
                decoration: InputDecoration(labelText: widget.editando ? 'Nova senha' : 'Senha'),
                validator: (v) {
                  if (!widget.editando && (v == null || v.isEmpty)) return 'A senha é obrigatória.';
                  if (v != null && v.isNotEmpty && v.length < 6) return 'A senha deve ter pelo menos 6 caracteres.';
                  return null;
                },
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _confirmacaoController,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'Confirmar senha'),
                validator: (v) => v != _senhaController.text ? 'A confirmação de senha não confere.' : null,
              ),
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

class _CartaoPerfil extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool selecionado;
  final VoidCallback? onTap;

  const _CartaoPerfil({required this.label, required this.icon, required this.selecionado, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final cor = selecionado ? AppColors.primary : Colors.grey.shade600;
    return Opacity(
      opacity: onTap == null && !selecionado ? 0.5 : 1,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 18),
          decoration: BoxDecoration(
            color: selecionado ? AppColors.primary.withValues(alpha: 0.1) : Colors.white,
            border: Border.all(color: selecionado ? AppColors.primary : Colors.grey.shade300, width: selecionado ? 2 : 1),
            borderRadius: BorderRadius.circular(12),
          ),
          child: Column(
            children: [
              Icon(icon, color: cor),
              const SizedBox(height: 6),
              Text(label, style: TextStyle(fontWeight: FontWeight.bold, color: cor)),
            ],
          ),
        ),
      ),
    );
  }
}
