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
#[Title('Editar Usuário')]
class Editar extends Component
{
    public User    $usuario;
    public string  $name     = '';
    public string  $email    = '';
    public string  $perfil   = '';
    public ?string $materia  = null;
    public string  $password              = '';
    public string  $password_confirmation = '';

    public function mount(User $usuario): void
    {
        $this->usuario = $usuario;
        $this->name    = $usuario->name;
        $this->email   = $usuario->email;
        $this->perfil  = $usuario->perfil->value;
        $this->materia = $usuario->materia;
    }

    protected function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255', "unique:users,email,{$this->usuario->id}"],
            'perfil'  => ['required', 'in:almoxarife,coordenador'],
            'materia' => ['nullable', 'required_if:perfil,coordenador', 'string', 'max:180'],
            'password'=> ['nullable', 'string', 'min:6', 'confirmed'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'       => 'O nome é obrigatório.',
            'email.required'      => 'O e-mail é obrigatório.',
            'email.unique'        => 'Este e-mail já está em uso por outro usuário.',
            'perfil.required'     => 'Selecione o perfil do usuário.',
            'materia.required_if' => 'Informe a matéria do professor.',
            'password.min'        => 'A nova senha deve ter pelo menos 6 caracteres.',
            'password.confirmed'  => 'A confirmação de senha não confere.',
        ];
    }

    public function salvar(): void
    {
        $this->validate();

        $dados = [
            'name'    => $this->name,
            'email'   => $this->email,
            'perfil'  => PerfilUsuario::from($this->perfil),
            'materia' => $this->perfil === 'coordenador' ? $this->materia : null,
        ];

        if (!empty($this->password)) {
            $dados['password'] = Hash::make($this->password);
        }

        $this->usuario->update($dados);

        $this->dispatch('notify', type: 'success', message: 'Usuário atualizado com sucesso!');
        $this->redirect(route('usuarios.index'), navigate: true);
    }

    public function render()
    {
        $materias = Livro::select('materia')->distinct()->orderBy('materia')->pluck('materia');

        return view('livewire.usuarios.editar', compact('materias'));
    }
}
