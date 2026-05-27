<?php

namespace Database\Seeders;

use App\Models\Livro;
use Illuminate\Database\Seeder;

/**
 * Seeder de livros didáticos para demonstração.
 *
 * Popula a tabela de livros com títulos reais usados no SENAI Limeira/SP,
 * distribuídos pelas principais disciplinas dos cursos técnicos.
 *
 * Livros com saldo_atual <= estoque_minimo aparecem no widget de baixo estoque.
 * Usa updateOrCreate pelo ISBN (único) para não duplicar ao re-executar.
 */
class LivroSeeder extends Seeder
{
    /**
     * Insere ou atualiza os livros didáticos de exemplo.
     */
    public function run(): void
    {
        $livros = [

            // ── Informática / Desenvolvimento de Sistemas ─────────────────────

            [
                'titulo'          => 'Algoritmos e Lógica de Programação',
                'isbn'            => '978-85-365-0104-6',
                'materia'         => 'Programação',
                'saldo_atual'     => 32,
                'estoque_minimo'  => 10,
            ],
            [
                'titulo'          => 'PHP e MySQL — Desenvolvimento Web',
                'isbn'            => '978-85-7522-418-6',
                'materia'         => 'Programação',
                'saldo_atual'     => 8, // abaixo do mínimo — aparece no alerta
                'estoque_minimo'  => 10,
            ],
            [
                'titulo'          => 'Banco de Dados: Projeto e Implementação',
                'isbn'            => '978-85-02-06394-4',
                'materia'         => 'Banco de Dados',
                'saldo_atual'     => 15,
                'estoque_minimo'  => 10,
            ],
            [
                'titulo'          => 'Redes de Computadores',
                'isbn'            => '978-85-352-3576-7',
                'materia'         => 'Redes',
                'saldo_atual'     => 0, // sem estoque — aparece em destaque
                'estoque_minimo'  => 10,
            ],
            [
                'titulo'          => 'Sistemas Operacionais: Conceitos e Aplicações',
                'isbn'            => '978-85-365-0155-8',
                'materia'         => 'Sistemas Operacionais',
                'saldo_atual'     => 20,
                'estoque_minimo'  => 10,
            ],

            // ── Eletrotécnica / Automação ─────────────────────────────────────

            [
                'titulo'          => 'Instalações Elétricas Residenciais',
                'isbn'            => '978-85-7194-883-9',
                'materia'         => 'Eletrotécnica',
                'saldo_atual'     => 25,
                'estoque_minimo'  => 10,
            ],
            [
                'titulo'          => 'Automação Industrial: CLP e SCADA',
                'isbn'            => '978-85-7608-391-7',
                'materia'         => 'Automação',
                'saldo_atual'     => 5, // abaixo do mínimo
                'estoque_minimo'  => 10,
            ],
            [
                'titulo'          => 'Eletricidade Básica',
                'isbn'            => '978-85-216-1632-2',
                'materia'         => 'Eletrotécnica',
                'saldo_atual'     => 18,
                'estoque_minimo'  => 10,
            ],

            // ── Administração / Gestão ────────────────────────────────────────

            [
                'titulo'          => 'Gestão de Pessoas nas Organizações',
                'isbn'            => '978-85-02-07654-8',
                'materia'         => 'Administração',
                'saldo_atual'     => 40,
                'estoque_minimo'  => 15,
            ],
            [
                'titulo'          => 'Contabilidade Geral',
                'isbn'            => '978-85-224-5765-3',
                'materia'         => 'Contabilidade',
                'saldo_atual'     => 12,
                'estoque_minimo'  => 15,
            ],
            [
                'titulo'          => 'Marketing e Vendas',
                'isbn'            => '978-85-02-09156-5',
                'materia'         => 'Marketing',
                'saldo_atual'     => 9, // abaixo do mínimo
                'estoque_minimo'  => 10,
            ],

            // ── Segurança do Trabalho ─────────────────────────────────────────

            [
                'titulo'          => 'NR-10: Segurança em Instalações Elétricas',
                'isbn'            => '978-85-7842-111-3',
                'materia'         => 'Segurança do Trabalho',
                'saldo_atual'     => 30,
                'estoque_minimo'  => 10,
            ],
            [
                'titulo'          => 'CIPA: Prevenção de Acidentes no Trabalho',
                'isbn'            => '978-85-7842-222-6',
                'materia'         => 'Segurança do Trabalho',
                'saldo_atual'     => 22,
                'estoque_minimo'  => 10,
            ],
        ];

        foreach ($livros as $dados) {
            // Atualiza ou cria pelo ISBN — evita duplicatas ao re-executar
            Livro::updateOrCreate(
                ['isbn' => $dados['isbn']],
                $dados
            );
        }
    }
}
