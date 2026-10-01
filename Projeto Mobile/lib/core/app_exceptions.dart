/// Equivalente ao DomainException lançado por EstoqueService::registrarSaida() (RN1).
class EstoqueInsuficienteException implements Exception {
  final String message;
  const EstoqueInsuficienteException(this.message);

  @override
  String toString() => message;
}

/// Equivalente ao InvalidArgumentException de quantidade <= 0 (RN2).
class QuantidadeInvalidaException implements Exception {
  final String message;
  const QuantidadeInvalidaException(this.message);

  @override
  String toString() => message;
}

/// Erro de regra de negócio genérico (ex.: ISBN duplicado, exclusão bloqueada).
class RegraNegocioException implements Exception {
  final String message;
  const RegraNegocioException(this.message);

  @override
  String toString() => message;
}
