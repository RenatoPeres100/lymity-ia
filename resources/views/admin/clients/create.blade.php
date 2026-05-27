<x-layouts.app title="Novo Cliente">

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
    <a href="{{ route('admin.clients.index') }}" style="display:inline-flex;align-items:center;color:#64748b;text-decoration:none;font-size:.82rem;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="15 18 9 12 15 6"/></svg>
        Clientes
    </a>
    <span style="color:#cbd5e1;">/</span>
    <span style="font-size:.82rem;color:#0f172a;font-weight:600;">Novo cliente</span>
</div>

<form method="POST" action="{{ route('admin.clients.store') }}">
    @csrf
    @include('admin.clients._form')
</form>

</x-layouts.app>
