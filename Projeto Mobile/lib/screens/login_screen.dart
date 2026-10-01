import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/app_colors.dart';
import '../state/session.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController(text: 'almoxarife@senai.br');
  final _senhaController = TextEditingController(text: 'senha123');

  bool _carregando = false;
  String? _erro;

  @override
  void dispose() {
    _emailController.dispose();
    _senhaController.dispose();
    super.dispose();
  }

  Future<void> _entrar() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _carregando = true;
      _erro = null;
    });

    final erro = await context.read<Session>().login(
          _emailController.text.trim(),
          _senhaController.text,
        );

    if (!mounted) return;
    setState(() {
      _carregando = false;
      _erro = erro;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.primary,
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.symmetric(horizontal: 24),
            child: Card(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Form(
                  key: _formKey,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const Icon(Icons.inventory_2_rounded, size: 48, color: AppColors.primary),
                      const SizedBox(height: 12),
                      const Text(
                        'SenaiStock',
                        textAlign: TextAlign.center,
                        style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                      ),
                      const Text(
                        'Controle de Estoque de Livros Didáticos',
                        textAlign: TextAlign.center,
                        style: TextStyle(color: Colors.grey),
                      ),
                      const SizedBox(height: 24),
                      TextFormField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(labelText: 'E-mail', prefixIcon: Icon(Icons.email_outlined)),
                        validator: (v) => (v == null || v.isEmpty) ? 'O e-mail é obrigatório.' : null,
                      ),
                      const SizedBox(height: 12),
                      TextFormField(
                        controller: _senhaController,
                        obscureText: true,
                        decoration: const InputDecoration(labelText: 'Senha', prefixIcon: Icon(Icons.lock_outline)),
                        validator: (v) => (v == null || v.isEmpty) ? 'A senha é obrigatória.' : null,
                        onFieldSubmitted: (_) => _entrar(),
                      ),
                      if (_erro != null) ...[
                        const SizedBox(height: 12),
                        Text(_erro!, style: const TextStyle(color: AppColors.perigo)),
                      ],
                      const SizedBox(height: 20),
                      ElevatedButton(
                        onPressed: _carregando ? null : _entrar,
                        child: _carregando
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                              )
                            : const Text('Entrar'),
                      ),
                      const SizedBox(height: 20),
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Credenciais de demonstração', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                            SizedBox(height: 6),
                            Text('Almoxarife: almoxarife@senai.br', style: TextStyle(fontSize: 12)),
                            Text('Professor: coordenador@senai.br', style: TextStyle(fontSize: 12)),
                            Text('Senha (ambos): senha123', style: TextStyle(fontSize: 12)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
