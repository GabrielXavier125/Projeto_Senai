<x-filament-widgets::widget>
    <x-filament::section>

        <x-slot name="heading">
            <div style="display:flex; align-items:center; gap:8px;">
                <x-filament::icon icon="heroicon-o-bell-alert" style="width:20px; height:20px; color:#f59e0b;" />
                <span>Avisos do Almoxarifado</span>
                @php $totalAvisos = $livrosBaixoEstoque->count() + $reservasInsuficientes->count(); @endphp
                @if ($totalAvisos > 0)
                    <x-filament::badge color="danger">{{ $totalAvisos }}</x-filament::badge>
                @endif
            </div>
        </x-slot>

        @if ($totalAvisos === 0)
            <div style="display:flex; align-items:center; gap:12px; padding:16px; border-radius:12px; border:1px solid #bbf7d0; background:#f0fdf4;">
                <x-filament::icon icon="heroicon-o-check-circle" style="width:24px; height:24px; color:#22c55e; flex-shrink:0;" />
                <div>
                    <p style="font-size:14px; font-weight:600; color:#15803d; margin:0;">Tudo em ordem!</p>
                    <p style="font-size:12px; color:#16a34a; margin:0;">Nenhum aviso no momento. Estoque e reservas estão normais.</p>
                </div>
            </div>
        @else
            <div style="display:flex; flex-direction:column; gap:24px;">

                {{-- ── Livros com Baixo Estoque ──────────────────────── --}}
                @if ($livrosBaixoEstoque->count() > 0)
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                            <x-filament::icon icon="heroicon-o-archive-box-x-mark" style="width:16px; height:16px; color:#f59e0b;" />
                            <h4 style="font-size:13px; font-weight:600; margin:0; color:#6b7280;">
                                Livros com Baixo Estoque
                                <span style="font-weight:400; color:#9ca3af;">({{ $livrosBaixoEstoque->count() }})</span>
                            </h4>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:12px;">
                            @foreach ($livrosBaixoEstoque as $livro)
                                @php
                                    $zerado     = $livro->saldo_atual === 0;
                                    $corBorda   = $zerado ? '#fca5a5' : '#fcd34d';
                                    $corFundo   = $zerado ? '#fff1f2' : '#fffbeb';
                                    $corSaldo   = $zerado ? '#dc2626' : '#d97706';
                                    $badge      = $zerado ? ['bg'=>'#fee2e2','txt'=>'#b91c1c','label'=>'⛔ Sem estoque'] : ['bg'=>'#fef3c7','txt'=>'#92400e','label'=>'⚠️ Abaixo do mínimo'];
                                @endphp

                                <div style="border:1px solid {{ $corBorda }}; background:{{ $corFundo }}; border-radius:12px; padding:16px; display:flex; flex-direction:column; gap:10px;">

                                    {{-- Título + ícone --}}
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                                        <p style="font-size:13px; font-weight:600; line-height:1.4; color:#1f2937; margin:0;">{{ $livro->titulo }}</p>
                                        <x-filament::icon
                                            icon="{{ $zerado ? 'heroicon-o-x-circle' : 'heroicon-o-exclamation-triangle' }}"
                                            style="width:18px; height:18px; flex-shrink:0; color:{{ $corSaldo }};" />
                                    </div>

                                    {{-- Matéria --}}
                                    <p style="font-size:11px; color:#6b7280; margin:0;">📚 {{ $livro->materia }}</p>

                                    {{-- Contadores --}}
                                    <div style="display:flex; justify-content:space-between; background:rgba(255,255,255,0.7); border-radius:8px; padding:10px 12px;">
                                        <div style="text-align:center;">
                                            <p style="font-size:10px; color:#9ca3af; margin:0 0 2px;">Saldo Atual</p>
                                            <p style="font-size:22px; font-weight:700; color:{{ $corSaldo }}; margin:0;">{{ $livro->saldo_atual }}</p>
                                        </div>
                                        <div style="width:1px; background:#e5e7eb;"></div>
                                        <div style="text-align:center;">
                                            <p style="font-size:10px; color:#9ca3af; margin:0 0 2px;">Mínimo</p>
                                            <p style="font-size:22px; font-weight:700; color:#4b5563; margin:0;">{{ $livro->estoque_minimo }}</p>
                                        </div>
                                        <div style="width:1px; background:#e5e7eb;"></div>
                                        <div style="text-align:center;">
                                            <p style="font-size:10px; color:#9ca3af; margin:0 0 2px;">Faltam</p>
                                            <p style="font-size:22px; font-weight:700; color:{{ $corSaldo }}; margin:0;">{{ $livro->estoque_minimo - $livro->saldo_atual }}</p>
                                        </div>
                                    </div>

                                    {{-- Badge --}}
                                    <span style="display:inline-block; background:{{ $badge['bg'] }}; color:{{ $badge['txt'] }}; font-size:11px; font-weight:500; padding:3px 10px; border-radius:999px;">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ── Reservas com Estoque Insuficiente ─────────────── --}}
                @if ($reservasInsuficientes->count() > 0)
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                            <x-filament::icon icon="heroicon-o-bookmark-slash" style="width:16px; height:16px; color:#ef4444;" />
                            <h4 style="font-size:13px; font-weight:600; margin:0; color:#6b7280;">
                                Reservas com Estoque Insuficiente
                                <span style="font-weight:400; color:#9ca3af;">({{ $reservasInsuficientes->count() }})</span>
                            </h4>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(240px, 1fr)); gap:12px;">
                            @foreach ($reservasInsuficientes as $reserva)
                                <div style="border:1px solid #fca5a5; background:#fff1f2; border-radius:12px; padding:16px; display:flex; flex-direction:column; gap:10px;">

                                    {{-- Título --}}
                                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                                        <p style="font-size:13px; font-weight:600; line-height:1.4; color:#1f2937; margin:0;">{{ $reserva->livro->titulo }}</p>
                                        <x-filament::icon icon="heroicon-o-bookmark" style="width:18px; height:18px; flex-shrink:0; color:#ef4444;" />
                                    </div>

                                    {{-- Quem reservou --}}
                                    <p style="font-size:11px; color:#6b7280; margin:0;">👤 {{ $reserva->user->name }}</p>
                                    @if ($reserva->observacao)
                                        <p style="font-size:11px; color:#6b7280; margin:0;">🏫 {{ $reserva->observacao }}</p>
                                    @endif

                                    {{-- Contadores --}}
                                    <div style="display:flex; justify-content:space-between; background:rgba(255,255,255,0.7); border-radius:8px; padding:10px 12px;">
                                        <div style="text-align:center;">
                                            <p style="font-size:10px; color:#9ca3af; margin:0 0 2px;">Solicitado</p>
                                            <p style="font-size:22px; font-weight:700; color:#dc2626; margin:0;">{{ $reserva->quantidade }}</p>
                                        </div>
                                        <div style="width:1px; background:#e5e7eb;"></div>
                                        <div style="text-align:center;">
                                            <p style="font-size:10px; color:#9ca3af; margin:0 0 2px;">Disponível</p>
                                            <p style="font-size:22px; font-weight:700; color:#4b5563; margin:0;">{{ $reserva->livro->saldo_atual }}</p>
                                        </div>
                                        <div style="width:1px; background:#e5e7eb;"></div>
                                        <div style="text-align:center;">
                                            <p style="font-size:10px; color:#9ca3af; margin:0 0 2px;">Faltam</p>
                                            <p style="font-size:22px; font-weight:700; color:#dc2626; margin:0;">{{ $reserva->quantidade - $reserva->livro->saldo_atual }}</p>
                                        </div>
                                    </div>

                                    {{-- Data --}}
                                    <p style="font-size:11px; color:#9ca3af; margin:0;">🕐 Reservado {{ $reserva->data_reserva->diffForHumans() }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        @endif

    </x-filament::section>
</x-filament-widgets::widget>
