import 'package:flutter_test/flutter_test.dart';
import 'package:senaistock_mobile/core/password_hasher.dart';
import 'package:senaistock_mobile/models/livro.dart';
import 'package:senaistock_mobile/models/perfil_usuario.dart';
import 'package:senaistock_mobile/models/reserva.dart';
import 'package:senaistock_mobile/models/status_reserva.dart';

Livro _livro({int saldo = 10, int minimo = 10}) => Livro(
      id: 1,
      titulo: 'Algoritmos',
      isbn: '978-0',
      materia: 'Programação',
      saldoAtual: saldo,
      estoqueMinimo: minimo,
    );

void main() {
  group('Livro', () {
    test('RN6: está em baixo estoque quando saldo <= mínimo', () {
      expect(_livro(saldo: 10, minimo: 10).estaBaixoEstoque, isTrue);
      expect(_livro(saldo: 9, minimo: 10).estaBaixoEstoque, isTrue);
      expect(_livro(saldo: 11, minimo: 10).estaBaixoEstoque, isFalse);
    });

    test('RN1: saldo suficiente só quando cobre a quantidade pedida', () {
      final livro = _livro(saldo: 5);
      expect(livro.temSaldoSuficiente(5), isTrue);
      expect(livro.temSaldoSuficiente(6), isFalse);
    });
  });

  group('Reserva', () {
    Reserva reserva({required int qtd, int? saldo, StatusReserva status = StatusReserva.pendente}) => Reserva(
          id: 1,
          livroId: 1,
          userId: 1,
          quantidade: qtd,
          status: status,
          dataReserva: DateTime(2026),
          livroSaldoAtual: saldo,
        );

    test('indica estoque insuficiente para dar baixa', () {
      expect(reserva(qtd: 3, saldo: 2).temEstoqueSuficiente, isFalse);
      expect(reserva(qtd: 2, saldo: 2).temEstoqueSuficiente, isTrue);
    });

    test('só reservas pendentes podem receber baixa', () {
      expect(reserva(qtd: 1).isPendente, isTrue);
      expect(reserva(qtd: 1, status: StatusReserva.retirada).isPendente, isFalse);
    });
  });

  test('perfil coordenador é exibido como Professor', () {
    expect(PerfilUsuario.fromValor('coordenador').label, 'Professor');
    expect(PerfilUsuario.fromValor('almoxarife').label, 'Almoxarife');
  });

  test('hash de senha confere apenas com a senha correta', () {
    final hash = PasswordHasher.hash('senha123');
    expect(PasswordHasher.check('senha123', hash), isTrue);
    expect(PasswordHasher.check('outra', hash), isFalse);
  });
}
