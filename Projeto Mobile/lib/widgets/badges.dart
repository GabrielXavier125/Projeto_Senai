import 'package:flutter/material.dart';

import '../core/app_colors.dart';
import '../models/livro.dart';
import '../models/status_reserva.dart';
import '../models/tipo_movimentacao.dart';

class _Pill extends StatelessWidget {
  final String label;
  final Color foreground;
  final Color background;
  final IconData? icon;

  const _Pill({required this.label, required this.foreground, required this.background, this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: background, borderRadius: BorderRadius.circular(999)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 12, color: foreground),
            const SizedBox(width: 4),
          ],
          Text(
            label,
            style: TextStyle(color: foreground, fontSize: 12, fontWeight: FontWeight.w600),
          ),
        ],
      ),
    );
  }
}

/// Badge de saldo: verde (normal) / amarelo (abaixo do mínimo) / vermelho (zerado).
class SaldoBadge extends StatelessWidget {
  final Livro livro;
  const SaldoBadge({super.key, required this.livro});

  @override
  Widget build(BuildContext context) {
    if (livro.saldoAtual <= 0) {
      return _Pill(label: 'Zerado (${livro.saldoAtual})', foreground: AppColors.perigo, background: AppColors.perigoFundo);
    }
    if (livro.estaBaixoEstoque) {
      return _Pill(label: 'Baixo (${livro.saldoAtual})', foreground: AppColors.alerta, background: AppColors.alertaFundo);
    }
    return _Pill(label: 'Saldo: ${livro.saldoAtual}', foreground: AppColors.sucesso, background: AppColors.sucessoFundo);
  }
}

class TipoMovimentacaoBadge extends StatelessWidget {
  final TipoMovimentacao tipo;
  const TipoMovimentacaoBadge({super.key, required this.tipo});

  @override
  Widget build(BuildContext context) {
    final entrada = tipo == TipoMovimentacao.entrada;
    return _Pill(
      label: tipo.label,
      icon: entrada ? Icons.arrow_downward : Icons.arrow_upward,
      foreground: entrada ? AppColors.sucesso : AppColors.perigo,
      background: entrada ? AppColors.sucessoFundo : AppColors.perigoFundo,
    );
  }
}

class StatusReservaBadge extends StatelessWidget {
  final StatusReserva status;
  const StatusReservaBadge({super.key, required this.status});

  @override
  Widget build(BuildContext context) {
    final (fg, bg) = switch (status) {
      StatusReserva.pendente => (AppColors.alerta, AppColors.alertaFundo),
      StatusReserva.retirada => (AppColors.sucesso, AppColors.sucessoFundo),
      StatusReserva.cancelada => (AppColors.perigo, AppColors.perigoFundo),
    };
    return _Pill(label: status.label, foreground: fg, background: bg);
  }
}

class PerfilBadge extends StatelessWidget {
  final String label;
  final bool destaque;
  const PerfilBadge({super.key, required this.label, this.destaque = false});

  @override
  Widget build(BuildContext context) {
    return _Pill(
      label: label,
      foreground: destaque ? AppColors.alerta : AppColors.info,
      background: destaque ? AppColors.alertaFundo : AppColors.infoFundo,
    );
  }
}
