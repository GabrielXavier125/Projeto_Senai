import 'dart:convert';
import 'package:crypto/crypto.dart';

/// Hash simples (sha256) só para o propósito deste app local de demonstração —
/// equivalente conceitual ao Hash::make() (bcrypt) do Laravel original.
class PasswordHasher {
  PasswordHasher._();

  static String hash(String plainText) =>
      sha256.convert(utf8.encode(plainText)).toString();

  static bool check(String plainText, String hash) => PasswordHasher.hash(plainText) == hash;
}
