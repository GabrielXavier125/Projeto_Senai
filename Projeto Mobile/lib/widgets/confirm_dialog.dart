import 'package:flutter/material.dart';

import '../core/app_colors.dart';

Future<bool> confirmDialog(
  BuildContext context, {
  required String titulo,
  required String mensagem,
  String textoConfirmar = 'Confirmar',
  bool destrutivo = false,
}) async {
  final resultado = await showDialog<bool>(
    context: context,
    builder: (context) => AlertDialog(
      title: Text(titulo),
      content: Text(mensagem),
      actions: [
        TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Voltar')),
        FilledButton(
          style: FilledButton.styleFrom(backgroundColor: destrutivo ? AppColors.perigo : AppColors.primary),
          onPressed: () => Navigator.pop(context, true),
          child: Text(textoConfirmar),
        ),
      ],
    ),
  );

  return resultado ?? false;
}

void showAppSnackBar(BuildContext context, String mensagem, {bool erro = false}) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(
      content: Text(mensagem),
      backgroundColor: erro ? AppColors.perigo : AppColors.sucesso,
      behavior: SnackBarBehavior.floating,
    ));
}
