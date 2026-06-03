<?php

namespace App\Livewire\Usuarios;

use App\Enums\PerfilUsuario;
use App\Models\Livro;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Novo Usuário')]
class Criar extends Component
{
    public string  $name                  = '';
    public string  $email                 = '';
    public string  $password              = '';
    public string  $password_confirmation = '';
    public string  $perfil                = '';
    public ?string $materia               = null;

    protected function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:6', 'confirmed'],
            'perfil'                => ['required', 'in:almoxarife,coordenador'],
            'materia'               => ['nullable', 'required_if:perfil,coordenador', 'string', 'max:180'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'              => 'O nome é obrigatório.',
            'email.required'             => 'O e-mail é obrigatório.',
            'email.unique'               => 'Este e-mail já está cadastrado.',
            'password.required'          => 'A senha é obrigatória.',
            'password.min'               => 'A senha deve ter pelo menos 6 caracteres.',
            'password.confirmed'         => 'A confirmação de senha não confere.',
            'perfil.required'            => 'Selecione o perfil do usuário.',
            'materia.required_if'        => 'Informe a matéria do professor.',
        ];
    }

    public function salvar(): void
    {
        $this->validate();

        User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => Hash::make($this->password),
            'perfil'   => PerfilUsuario::from($this->perfil),
            'materia'  => $this->perfil === 'coordenador' ? $this->materia : null,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Usuário criado com sucesso!');
        $this->redirect(route('usuarios.index'), navigate: true);
    }

    public function render()
    {
        // Matérias existentes para autocomplete
        $materias = Livro::select('materia')->distinct()->orderBy('materia')->pluck('materia');

        return view('livewire.usuarios.criar', compact('materias'));
    }
}
