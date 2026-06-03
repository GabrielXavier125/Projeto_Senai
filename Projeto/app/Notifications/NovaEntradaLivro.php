<?php

namespace App\Notifications;

use App\Models\Livro;
use Illuminate\Notifications\Notification;

class NovaEntradaLivro extends Notification
{
    public function __construct(
        private readonly Livro $livro,
        private readonly int   $quantidade,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'livro_id'     => $this->livro->id,
            'livro_titulo' => $this->livro->titulo,
            'materia'      => $this->livro->materia,
            'quantidade'   => $this->quantidade,
            'mensagem'     => "Chegaram {$this->quantidade} exemplar(es) de \"{$this->livro->titulo}\" ({$this->livro->materia}).",
        ];
    }
}
