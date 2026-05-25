@extends('layouts.mobile-app')
@section('title', 'Agenda — Lymity App')
@section('header-title', 'Agenda')

@section('content')

@if(count($items))
    @php $lastDate = null; @endphp
    @foreach($items as $item)
        @if($item['date'] !== $lastDate)
            @if($lastDate !== null)</div>@endif
            <div style="font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;margin:16px 0 8px;">
                {{ \Carbon\Carbon::parse($item['date'])->format('d \d\e M, Y') }}
            </div>
            <div>
            @php $lastDate = $item['date']; @endphp
        @endif

        <div class="app-card" style="margin-bottom:8px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;background:#1e293b;border:1px solid #334155;flex-shrink:0;">
                    {{ $item['type'] === 'social_post' ? '◈' : ($item['type'] === 'campaign' ? '▲' : '✓') }}
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:14px;font-weight:600;color:#f1f5f9;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item['title'] }}</div>
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ ucfirst(str_replace('_', ' ', $item['type'])) }}</div>
                </div>
                <span class="badge badge-{{ $item['status'] }}">{{ ucfirst($item['status']) }}</span>
            </div>
        </div>
    @endforeach
    </div>
@else
    <div class="empty-state">
        <div class="empty-icon">◈</div>
        <div class="empty-text">Nenhum item agendado no momento.</div>
    </div>
@endif

@endsection
