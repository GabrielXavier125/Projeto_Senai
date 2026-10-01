import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/app_colors.dart';
import '../../core/app_exceptions.dart';
import '../../models/usuario.dart';
import '../../repositories/usuario_repository.dart';
import '../../state/session.dart';
import '../../widgets/badges.dart';
import '../../widgets/confirm_dialog.dart';
import '../../widgets/empty_state.dart';
import 'usuario_form_screen.dart';

/// Equivalente a App\Livewire\Usuarios\Index.php — exclusivo do almoxarife.
class UsuariosScreen extends StatefulWidget {
  const UsuariosScreen({super.key});

  @override
  State<UsuariosScreen> createState() => _UsuariosScreenState();
}

class _UsuariosScreenState extends State<UsuariosScreen> {
  final _repository = UsuarioRepository();
  final _buscaController = TextEditingController();

  List<Usuario> _usuarios = [];
  String? _perfilFiltro;
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
    final usuarios = await _repository.listar(busca: _buscaController.text.trim(), perfilFiltro: _perfilFiltro);

    if (!mounted) return;
    setState(() {
      _usuarios = usuarios;
      _carregando = false;
    });
  }

  Future<void> _excluir(Usuario usuario) async {
    final logado = context.read<Session>().usuario!;
    if (usuario.id == logado.id) {
      showAppSnackBar(context, 'Você não pode excluir o próprio usuário.', erro: true);
      return;
    }

    final confirmar = await confirmDialog(
      context,
      titulo: 'Excluir usuário',
      mensagem: 'Tem certeza que deseja excluir "${usuario.nome}"?',
      textoConfirmar: 'Excluir',
      destrutivo: true,
    );
    if (!confirmar) return;

    try {
      await _repository.excluir(usuario.id, usuarioLogadoId: logado.id);
      if (!mounted) return;
      showAppSnackBar(context, 'Usuário excluído com sucesso.');
      _carregar();
    } on RegraNegocioException catch (e) {
      if (mounted) showAppSnackBar(context, e.message, erro: true);
    }
  }

  Future<void> _abrirFormulario({Usuario? usuario}) async {
    final salvo = await Navigator.of(context).push<bool>(
      MaterialPageRoute(builder: (_) => UsuarioFormScreen(usuario: usuario)),
    );
    if (salvo == true) _carregar();
  }

  Widget _chipPerfil(String label, String? valor) => ChoiceChip(
        label: Text(label),
        selected: _perfilFiltro == valor,
        onSelected: (_) {
          setState(() => _perfilFiltro = valor);
          _carregar();
        },
      );

  @override
  Widget build(BuildContext context) {
    final logado = context.watch<Session>().usuario!;

    return Scaffold(
      floatingActionButton: FloatingActionButton.extended(
        heroTag: 'fab_usuarios',
        onPressed: () => _abrirFormulario(),
        icon: const Icon(Icons.add),
        label: const Text('Novo usuário'),
      ),
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
                    decoration: const InputDecoration(hintText: 'Buscar por nome ou e-mail...', prefixIcon: Icon(Icons.search)),
                    onChanged: (_) => _carregar(),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      _chipPerfil('Todos', null),
                      const SizedBox(width: 8),
                      _chipPerfil('Almoxarifes', 'almoxarife'),
                      const SizedBox(width: 8),
                      _chipPerfil('Professores', 'coordenador'),
                    ],
                  ),
                ],
              ),
            ),
            Expanded(
              child: _carregando
                  ? const Center(child: CircularProgressIndicator())
                  : _usuarios.isEmpty
                      ? ListView(children: const [
                          EmptyState(icon: Icons.people_outline, message: 'Nenhum usuário encontrado.'),
                        ])
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(12, 0, 12, 88),
                          itemCount: _usuarios.length,
                          itemBuilder: (context, index) {
                            final usuario = _usuarios[index];
                            final voceMesmo = usuario.id == logado.id;

                            return Card(
                              margin: const EdgeInsets.only(bottom: 10),
                              child: ListTile(
                                onTap: () => _abrirFormulario(usuario: usuario),
                                leading: CircleAvatar(
                                  backgroundColor: usuario.isAlmoxarife ? AppColors.alerta : AppColors.primary,
                                  child: Text(
                                    usuario.nome.isNotEmpty ? usuario.nome[0].toUpperCase() : '?',
                                    style: const TextStyle(color: Colors.white),
                                  ),
                                ),
                                title: Text('${usuario.nome}${voceMesmo ? ' (você)' : ''}'),
                                subtitle: Text(
                                  usuario.materia == null ? usuario.email : '${usuario.email}\n${usuario.materia}',
                                ),
                                isThreeLine: usuario.materia != null,
                                trailing: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    PerfilBadge(label: usuario.perfil.label, destaque: usuario.isAlmoxarife),
                                    if (!voceMesmo)
                                      IconButton(
                                        tooltip: 'Excluir',
                                        icon: const Icon(Icons.delete_outline, color: AppColors.perigo),
                                        onPressed: () => _excluir(usuario),
                                      ),
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
