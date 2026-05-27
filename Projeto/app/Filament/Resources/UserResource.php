<?php

namespace App\Filament\Resources;

use App\Enums\PerfilUsuario;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * Resource do Filament para gerenciamento de usuários do sistema.
 *
 * Permite ao coordenador cadastrar novos almoxarifes e coordenadores
 * diretamente pelo painel, sem precisar de acesso ao banco de dados.
 *
 * Permissões:
 *   - Coordenador: acesso total (criar, editar, excluir, visualizar)
 *   - Almoxarife: somente visualização (não pode gerenciar usuários)
 *
 * Segurança:
 *   - A senha nunca é exibida — sempre armazenada com hash (bcrypt)
 *   - No formulário de edição, a senha só é atualizada se preenchida
 */
class UserResource extends Resource
{
    /** Model Eloquent gerenciado por este resource */
    protected static ?string $model = User::class;

    /** Ordem no menu lateral (último item) */
    protected static ?int $navigationSort = 3;

    /** Ícone do menu lateral */
    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-users';
    }

    /** Rótulo no menu lateral */
    public static function getNavigationLabel(): string
    {
        return 'Usuários';
    }

    /** Nome no singular */
    public static function getModelLabel(): string
    {
        return 'Usuário';
    }

    /** Nome no plural */
    public static function getPluralModelLabel(): string
    {
        return 'Usuários';
    }

    /** Grupo no menu — separado do catálogo de livros */
    public static function getNavigationGroup(): ?string
    {
        return 'Administração';
    }

    // =========================================================================
    // Formulário de cadastro/edição de usuário
    // =========================================================================

    /**
     * Formulário para criar ou editar um usuário.
     *
     * No modo de edição, a senha é opcional — se deixada em branco,
     * a senha atual do usuário é mantida sem alteração.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Dados do Usuário')
                ->description('Preencha os dados de acesso do funcionário.')
                ->schema([

                    // Nome completo do funcionário
                    TextInput::make('name')
                        ->label('Nome Completo')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(2),

                    // E-mail único — usado para login no sistema
                    TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->required()
                        ->unique(
                            table: 'users',
                            column: 'email',
                            ignorable: fn ($record) => $record, // ignora o próprio registro na edição
                        )
                        ->columnSpan(2),

                    // Perfil: almoxarife ou coordenador
                    Select::make('perfil')
                        ->label('Perfil de Acesso')
                        ->options([
                            PerfilUsuario::Almoxarife->value  => PerfilUsuario::Almoxarife->label(),
                            PerfilUsuario::Coordenador->value => PerfilUsuario::Coordenador->label(),
                        ])
                        ->required()
                        ->columnSpan(2),

                ])->columns(2),

            Section::make('Senha')
                ->description('Deixe em branco para manter a senha atual (somente na edição).')
                ->schema([

                    // Senha — obrigatória na criação, opcional na edição
                    TextInput::make('password')
                        ->label('Senha')
                        ->password()
                        ->revealable() // botão para mostrar/ocultar a senha
                        ->minLength(6)
                        ->required(fn (string $operation) => $operation === 'create')
                        ->dehydrated(fn ($state) => filled($state)) // só envia se preenchido
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state)) // salva com hash
                        ->helperText('Mínimo de 6 caracteres.')
                        ->columnSpan(2),

                ])->columns(2),
        ]);
    }

    // =========================================================================
    // Tabela de listagem de usuários
    // =========================================================================

    /**
     * Tabela com todos os usuários cadastrados no sistema.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Nome do usuário
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                // E-mail de login
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->sortable(),

                // Perfil exibido como badge colorido
                TextColumn::make('perfil')
                    ->label('Perfil')
                    ->badge()
                    ->color(fn (PerfilUsuario $state) => $state->cor())
                    ->formatStateUsing(fn (PerfilUsuario $state) => $state->label()),

                // Data de cadastro do usuário
                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(),

            ])

            ->defaultSort('name')

            ->actions([
                \Filament\Actions\EditAction::make()
                    ->visible(fn () => auth()->user()?->isAlmoxarife()),
                \Filament\Actions\DeleteAction::make()
                    ->visible(fn (Model $record) =>
                        auth()->user()?->isAlmoxarife() && auth()->id() !== $record->id
                    ),
            ])

            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ])->visible(fn () => auth()->user()?->isAlmoxarife()),
            ]);
    }

    // =========================================================================
    // Controle de acesso (RF2)
    // =========================================================================

    /**
     * Somente o almoxarife pode criar usuários.
     */
    public static function canCreate(): bool
    {
        return auth()->user()?->isAlmoxarife() ?? false;
    }

    /**
     * Somente o almoxarife pode editar usuários.
     */
    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isAlmoxarife() ?? false;
    }

    /**
     * Somente o almoxarife pode excluir usuários.
     * Proteção extra: não permite excluir o próprio usuário logado.
     */
    public static function canDelete(Model $record): bool
    {
        $usuario = auth()->user();

        // Almoxarife pode excluir, mas nunca a si mesmo
        return $usuario?->isAlmoxarife() && $usuario->id !== $record->id;
    }

    /**
     * Somente almoxarife acessa o gerenciamento de usuários.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isAlmoxarife() ?? false;
    }

    // =========================================================================
    // Páginas do Resource
    // =========================================================================

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
