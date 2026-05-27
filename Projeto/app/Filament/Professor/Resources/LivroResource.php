<?php

namespace App\Filament\Professor\Resources;

use App\Filament\Professor\Resources\LivroResource\Pages;
use App\Models\Livro;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Catálogo de livros — visão somente leitura para o Coordenador.
 *
 * O coordenador pode consultar os livros disponíveis e seus saldos,
 * mas não pode criar, editar ou excluir registros.
 * Para solicitar livros, use o menu Reservas.
 */
class LivroResource extends Resource
{
    protected static ?string $model = Livro::class;

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-book-open';
    }

    public static function getNavigationLabel(): string
    {
        return 'Livros Disponíveis';
    }

    public static function getModelLabel(): string
    {
        return 'Livro';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Livros';
    }

    /** Formulário vazio — coordenador não cria nem edita livros */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('ISBN copiado!')
                    ->fontFamily('mono'),

                TextColumn::make('materia')
                    ->label('Matéria')
                    ->searchable()
                    ->sortable(),

                // Saldo com badge colorido — ajuda o coordenador a saber disponibilidade
                TextColumn::make('saldo_atual')
                    ->label('Saldo Disponível')
                    ->badge()
                    ->color(function (Livro $record): string {
                        if ($record->saldo_atual <= 0) {
                            return 'danger';
                        }
                        if ($record->estaBaixoEstoque()) {
                            return 'warning';
                        }
                        return 'success';
                    })
                    ->sortable(),

            ])

            ->filters([
                Filter::make('com_estoque')
                    ->label('Com estoque disponível')
                    ->query(fn ($query) => $query->where('saldo_atual', '>', 0)),
            ])

            // Sem ações — coordenador só visualiza
            ->actions([])
            ->bulkActions([])

            ->defaultSort('titulo', 'asc');
    }

    /** Coordenador não cria livros */
    public static function canCreate(): bool { return false; }

    /** Coordenador não edita livros */
    public static function canEdit(Model $record): bool { return false; }

    /** Coordenador não exclui livros */
    public static function canDelete(Model $record): bool { return false; }

    public static function canViewAny(): bool { return true; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLivros::route('/'),
        ];
    }
}
