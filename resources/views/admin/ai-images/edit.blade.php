<x-layouts.app>
    <div style="padding:2rem;">
        <div style="margin-bottom:1.5rem;">
            <a href="{{ route('admin.ai-images.show', $generation) }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Voltar</a>
            <h1 style="font-size:1.8rem;font-weight:700;color:#0f172a;margin:.5rem 0 .25rem;">Editar Geração #{{ $generation->id }}</h1>
            <p style="color:#64748b;margin:0;">{{ $generation->title ?? 'Sem título' }}</p>
        </div>

        @if($errors->any())
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#991b1b;padding:.75rem 1rem;border-radius:.5rem;margin-bottom:1rem;">
            <ul style="margin:0;padding-left:1.2rem;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('admin.ai-images.update', $generation) }}" method="POST">
            @csrf
            @method('PUT')
            @include('admin.ai-images._form')
        </form>
    </div>
</x-layouts.app>
