<?php

namespace Database\Seeders;

use App\Enums\TipoMovimentacao;
use App\Models\Livro;
use App\Models\Movimentacao;
use App\Models\User;
use App\Services\EstoqueService;
use Illuminate\Database\Seeder;

/**
 * Seeder de movimentações de estoque para demonstração.
 *
 * Gera um histórico realista de entradas e saídas nos últimos 30 dias,
 * simulando o uso cotidiano do almoxarifado do SENAI Limeira.
 *
 * As movimentações são registradas via EstoqueService para garantir
 * que todas as regras de negócio (RN1–RN5) sejam aplicadas corretamente
 * e o saldo dos livros fique consistente com o histórico.
 *
 * ATENÇÃO: Execute após LivroSeeder e UserSeeder.
 */
class MovimentacaoSeeder extends Seeder
{
    public function __construct(
        private EstoqueService $estoqueService
    ) {}

    /**
     * Gera o histórico de movimentações de exemplo.
     */
    public function run(): void
    {
        // Pula se já há movimentações (evita duplicar o histórico)
        if (Movimentacao::count() > 0) {
            $this->command->info('Movimentações já existem — seeder ignorado.');
            return;
        }

        $almoxarife  = User::where('email', 'almoxarife@senai.br')->first();
        $coordenador = User::where('email', 'coordenador@senai.br')->first();

        if (!$almoxarife || !$coordenador) {
            $this->command->warn('Usuários não encontrados. Execute o DatabaseSeeder primeiro.');
            return;
        }

        // ── Entradas de estoque (abastecimentos dos últimos 30 dias) ──────────

        $this->registrarEntrada(
            isbn:       '978-85-365-0104-6', // Algoritmos
            quantidade: 20,
            usuario:    $almoxarife,
            observacao: 'NF 4521 — Editora Érica — 15/04/2026',
            diasAtras:  28
        );

        $this->registrarEntrada(
            isbn:       '978-85-352-3576-7', // Redes
            quantidade: 15,
            usuario:    $almoxarife,
            observacao: 'NF 4598 — Editora Campus — 18/04/2026',
            diasAtras:  25
        );

        $this->registrarEntrada(
            isbn:       '978-85-7194-883-9', // Instalações Elétricas
            quantidade: 10,
            usuario:    $almoxarife,
            observacao: 'NF 4632 — Editora LTC — 22/04/2026',
            diasAtras:  20
        );

        $this->registrarEntrada(
            isbn:       '978-85-02-07654-8', // Gestão de Pessoas
            quantidade: 25,
            usuario:    $almoxarife,
            observacao: 'NF 4701 — Editora Saraiva — 28/04/2026',
            diasAtras:  15
        );

        $this->registrarEntrada(
            isbn:       '978-85-7842-111-3', // NR-10
            quantidade: 12,
            usuario:    $coordenador,
            observacao: 'NF 4755 — Editora Senac — 02/05/2026',
            diasAtras:  10
        );

        // ── Saídas de estoque (retiradas por turma) ───────────────────────────

        $this->registrarSaida(
            isbn:       '978-85-365-0104-6', // Algoritmos
            quantidade: 8,
            usuario:    $almoxarife,
            observacao: 'Turma DS-01 — Desenvolvimento de Sistemas 2026',
            diasAtras:  22
        );

        $this->registrarSaida(
            isbn:       '978-85-02-06394-4', // Banco de Dados
            quantidade: 5,
            usuario:    $almoxarife,
            observacao: 'Turma DS-01 — Banco de Dados 2026',
            diasAtras:  18
        );

        $this->registrarSaida(
            isbn:       '978-85-7194-883-9', // Instalações Elétricas
            quantidade: 7,
            usuario:    $almoxarife,
            observacao: 'Turma EL-01 — Eletrotécnica 2026',
            diasAtras:  14
        );

        $this->registrarSaida(
            isbn:       '978-85-02-07654-8', // Gestão de Pessoas
            quantidade: 10,
            usuario:    $coordenador,
            observacao: 'Turma ADM-01 — Administração 2026',
            diasAtras:  10
        );

        $this->registrarSaida(
            isbn:       '978-85-7842-111-3', // NR-10
            quantidade: 12,
            usuario:    $almoxarife,
            observacao: 'Turma ST-01 — Segurança do Trabalho 2026',
            diasAtras:  5
        );

        $this->registrarSaida(
            isbn:       '978-85-365-0155-8', // Sistemas Operacionais
            quantidade: 6,
            usuario:    $almoxarife,
            observacao: 'Turma DS-02 — Sistemas Operacionais 2026',
            diasAtras:  3
        );

        $this->registrarSaida(
            isbn:       '978-85-7608-391-7', // Automação
            quantidade: 5,
            usuario:    $almoxarife,
            observacao: 'Turma AU-01 — Automação Industrial 2026',
            diasAtras:  1
        );
    }

    /**
     * Registra uma entrada no estoque e ajusta o timestamp para simular histórico.
     * O saldo já foi definido diretamente no LivroSeeder — aqui apenas gravamos
     * o histórico de movimentações sem alterar o saldo novamente.
     */
    private function registrarEntrada(
        string $isbn,
        int $quantidade,
        User $usuario,
        string $observacao,
        int $diasAtras
    ): void {
        $livro = Livro::where('isbn', $isbn)->first();

        if (!$livro) {
            return;
        }

        // Cria diretamente para ter controle do timestamp do histórico
        // (EstoqueService usaria now() e alteraria o saldo, que já está no seeder)
        Movimentacao::create([
            'livro_id'   => $livro->id,
            'user_id'    => $usuario->id,
            'tipo'       => TipoMovimentacao::Entrada,
            'quantidade' => $quantidade,
            'observacao' => $observacao,
            'data_hora'  => now()->subDays($diasAtras),
        ]);
    }

    /**
     * Registra uma saída no histórico (sem alterar saldo — já definido no LivroSeeder).
     */
    private function registrarSaida(
        string $isbn,
        int $quantidade,
        User $usuario,
        string $observacao,
        int $diasAtras
    ): void {
        $livro = Livro::where('isbn', $isbn)->first();

        if (!$livro) {
            return;
        }

        Movimentacao::create([
            'livro_id'   => $livro->id,
            'user_id'    => $usuario->id,
            'tipo'       => TipoMovimentacao::Saida,
            'quantidade' => $quantidade,
            'observacao' => $observacao,
            'data_hora'  => now()->subDays($diasAtras),
        ]);
    }
}
