<x-layouts.app>
    <div style="padding:2rem;max-width:960px;">

        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
            <div>
                <a href="{{ route('admin.social.posts.index') }}" style="color:#64748b;text-decoration:none;font-size:.9rem;">← Posts</a>
                <h1 style="font-size:1.6rem;font-weight:700;color:#0f172a;margin-top:.5rem;">{{ $post->title }}</h1>
                <div style="display:flex;gap:.75rem;align-items:center;margin-top:.5rem;flex-wrap:wrap;">
                    <span style="font-size:.8rem;padding:.25rem .75rem;border-radius:9999px;background:{{ $post->status_badge_color }}20;color:{{ $post->status_badge_color }};font-weight:600;">{{ $post->status_label }}</span>
                    <span style="color:#64748b;font-size:.8rem;">{{ $post->objective_label }} · {{ $post->content_type_label }}</span>
                    @if($post->client)
                        <span style="color:#64748b;font-size:.8rem;">{{ $post->client->name }}</span>
                    @else
                        <span style="color:#6366f1;font-size:.8rem;font-weight:600;">Agência</span>
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                @if($post->canBeEdited())
                    <a href="{{ route('admin.social.posts.edit', $post) }}"
                       style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.5rem .9rem;border-radius:.375rem;text-decoration:none;font-size:.85rem;">Editar</a>
                @endif
                @if(in_array($post->status, ['draft','rejected','failed']))
                    <form method="POST" action="{{ route('admin.social.posts.send-approval', $post) }}" style="display:inline;">
                        @csrf
                        @php $canSubmit = !empty(trim($post->main_caption ?? '')) && $post->hasValidImage(); @endphp
                        <button
                            @if(!$canSubmit) disabled title="Precisa de legenda e imagem válida para enviar" @endif
                            style="background:{{ $canSubmit ? '#f59e0b' : '#e2e8f0' }};color:{{ $canSubmit ? '#fff' : '#94a3b8' }};padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:{{ $canSubmit ? 'pointer' : 'not-allowed' }};font-size:.85rem;">
                            Enviar Aprovação
                        </button>
                    </form>
                @endif
                @if($post->status === 'pending_approval')
                    @php $canApprove = $post->hasValidImage(); @endphp
                    <form method="POST" action="{{ route('admin.social.posts.approve', $post) }}" style="display:inline;">
                        @csrf
                        <button
                            @if(!$canApprove) disabled title="Imagem precisa estar válida para aprovar" @endif
                            style="background:{{ $canApprove ? '#10b981' : '#e2e8f0' }};color:{{ $canApprove ? '#fff' : '#94a3b8' }};padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:{{ $canApprove ? 'pointer' : 'not-allowed' }};font-size:.85rem;">
                            Aprovar
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.social.posts.reject', $post) }}" style="display:inline;">
                        @csrf
                        <button style="background:#ef4444;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">Rejeitar</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
        <div style="background:#f0fdf4;border:1px solid #86efac;color:#166534;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
        <div style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('warning') }}</div>
        @endif
        @if(session('warning_image'))
        <div style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('warning_image') }}</div>
        @endif
        @if(session('error'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ session('error') }}</div>
        @endif
        @if($errors->has('instagram'))
        <div style="background:#fef2f2;border:1px solid #fca5a5;color:#dc2626;padding:.75rem;border-radius:.5rem;margin-bottom:1rem;">{{ $errors->first('instagram') }}</div>
        @endif

        {{-- Published / Permalink banner --}}
        @if($post->isPublished() && $post->permalink)
        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:.75rem;padding:1rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
            <div>
                <span style="color:#166534;font-weight:700;font-size:.9rem;">✓ Publicado no Instagram</span>
                <span style="color:#166534;font-size:.85rem;margin-left:.75rem;">{{ $post->published_at?->format('d/m/Y H:i') }}</span>
            </div>
            <a href="{{ $post->permalink }}" target="_blank"
               style="background:#10b981;color:#fff;padding:.4rem .9rem;border-radius:.375rem;text-decoration:none;font-size:.85rem;white-space:nowrap;">
                Ver no Instagram →
            </a>
        </div>
        @endif

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">

            {{-- Left Column --}}
            <div>

                {{-- AI Actions --}}
                @if($post->canBeEdited())
                <div style="background:#faf5ff;border:1px solid #ddd6fe;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#5b21b6;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">✨ Ações com IA</h3>
                    <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                        <form method="POST" action="{{ route('admin.social.posts.generate-caption', $post) }}" style="display:inline;">
                            @csrf
                            <button style="background:#8b5cf6;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">
                                ✨ Gerar Legenda com Gemini
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.social.posts.generate-image', $post) }}" style="display:inline;"
                              onsubmit="return confirm('Gerar imagem com Gemini? Isso pode levar alguns segundos.')">
                            @csrf
                            <button style="background:#7c3aed;color:#fff;padding:.5rem .9rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.85rem;">
                                🖼 Gerar Imagem com Gemini
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                {{-- Caption --}}
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Legenda Principal</h3>
                    @if($post->main_caption)
                        <p style="color:#334155;font-size:.95rem;line-height:1.7;white-space:pre-wrap;">{{ $post->main_caption }}</p>
                    @else
                        <p style="color:#94a3b8;font-size:.9rem;">Sem legenda. Use "Gerar Legenda com Gemini" ou edite o post.</p>
                    @endif
                    @if($post->hashtags)
                        <p style="color:#3b82f6;font-size:.85rem;margin-top:1rem;">{{ $post->hashtags }}</p>
                    @endif
                    @if($post->cta)
                        <p style="color:#10b981;font-size:.85rem;margin-top:.5rem;font-weight:600;">CTA: {{ $post->cta }}</p>
                    @endif
                </div>

                {{-- Image Section --}}
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                        <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;">Imagem do Post</h3>
                        @php
                            $imgStatusColor = match($post->image_validation_status) {
                                'valid'   => '#166534',
                                'invalid' => '#dc2626',
                                default   => '#64748b',
                            };
                            $imgStatusLabel = match($post->image_status ?? '') {
                                'generating' => 'Gerando...',
                                'generated'  => 'Gerada',
                                'validating' => 'Validando...',
                                'valid'      => 'Válida ✓',
                                'invalid'    => 'Inválida ✗',
                                'failed'     => 'Falhou',
                                'replaced'   => 'Substituída',
                                'missing'    => 'Ausente',
                                default      => ($post->image_status ?? 'Sem imagem'),
                            };
                        @endphp
                        @if($post->image_status)
                            <span style="font-size:.8rem;padding:.25rem .6rem;border-radius:9999px;background:{{ $imgStatusColor }}20;color:{{ $imgStatusColor }};font-weight:600;">{{ $imgStatusLabel }}</span>
                        @endif
                    </div>

                    {{-- Image preview --}}
                    @if($post->public_image_url)
                        <div style="margin-bottom:1rem;">
                            <img src="{{ $post->public_image_url }}"
                                 alt="Preview do post"
                                 style="width:100%;max-width:400px;border-radius:.5rem;border:1px solid #e2e8f0;"
                                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
                            <div style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:.5rem;padding:1rem;color:#dc2626;font-size:.85rem;">
                                Imagem não carregou. URL: <a href="{{ $post->public_image_url }}" target="_blank" style="color:#dc2626;">{{ Str::limit($post->public_image_url, 60) }}</a>
                            </div>
                        </div>
                        <div style="background:#f8fafc;border-radius:.375rem;padding:.75rem;margin-bottom:1rem;font-size:.8rem;color:#475569;word-break:break-all;">
                            <strong>URL:</strong> <a href="{{ $post->public_image_url }}" target="_blank" style="color:#3b82f6;">{{ $post->public_image_url }}</a>
                        </div>
                        @if($post->image_validation_status === 'invalid' && $post->image_validation_error)
                            <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:.375rem;padding:.75rem;margin-bottom:1rem;font-size:.85rem;color:#dc2626;">
                                <strong>Erro de validação:</strong> {{ $post->image_validation_error }}
                            </div>
                        @endif
                    @else
                        <div style="background:#f8fafc;border:2px dashed #e2e8f0;border-radius:.5rem;padding:2rem;text-align:center;margin-bottom:1rem;">
                            <div style="color:#94a3b8;font-size:2rem;">🖼</div>
                            <p style="color:#94a3b8;font-size:.9rem;margin-top:.5rem;">Sem imagem. Gere com Gemini ou faça upload.</p>
                        </div>
                    @endif

                    {{-- Image actions --}}
                    @if($post->canBeEdited())
                    <div style="display:flex;flex-direction:column;gap:.75rem;">

                        {{-- Validate --}}
                        @if($post->public_image_url)
                        <form method="POST" action="{{ route('admin.social.posts.validate-image', $post) }}" style="display:flex;gap:.5rem;align-items:center;">
                            @csrf
                            <button style="background:#0ea5e9;color:#fff;padding:.4rem .8rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.8rem;">
                                Revalidar Imagem
                            </button>
                        </form>
                        @endif

                        {{-- Replace by URL --}}
                        <details style="border:1px solid #e2e8f0;border-radius:.5rem;overflow:hidden;">
                            <summary style="padding:.6rem .75rem;cursor:pointer;background:#f8fafc;font-size:.85rem;color:#475569;font-weight:600;">
                                Substituir por URL HTTPS
                            </summary>
                            <form method="POST" action="{{ route('admin.social.posts.replace-image-url', $post) }}"
                                  style="padding:.75rem;display:flex;gap:.5rem;">
                                @csrf
                                <input type="url" name="public_image_url" placeholder="https://..."
                                       style="flex:1;padding:.5rem;border:1px solid #e2e8f0;border-radius:.375rem;font-size:.85rem;">
                                <button style="background:#475569;color:#fff;padding:.5rem .8rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.8rem;white-space:nowrap;">
                                    Substituir
                                </button>
                            </form>
                        </details>

                        {{-- Replace by upload --}}
                        <details style="border:1px solid #e2e8f0;border-radius:.5rem;overflow:hidden;">
                            <summary style="padding:.6rem .75rem;cursor:pointer;background:#f8fafc;font-size:.85rem;color:#475569;font-weight:600;">
                                Substituir por Upload (JPEG/PNG, máx 8MB)
                            </summary>
                            <form method="POST" action="{{ route('admin.social.posts.replace-image-upload', $post) }}"
                                  enctype="multipart/form-data" style="padding:.75rem;display:flex;gap:.5rem;align-items:center;">
                                @csrf
                                <input type="file" name="image" accept="image/jpeg,image/png"
                                       style="flex:1;font-size:.85rem;">
                                <button style="background:#475569;color:#fff;padding:.5rem .8rem;border-radius:.375rem;border:none;cursor:pointer;font-size:.8rem;white-space:nowrap;">
                                    Enviar
                                </button>
                            </form>
                        </details>
                    </div>
                    @endif

                    {{-- Image metadata --}}
                    @if($post->image_metadata)
                    <details style="margin-top:1rem;">
                        <summary style="cursor:pointer;font-size:.8rem;color:#94a3b8;">Metadados da imagem</summary>
                        <pre style="font-size:.75rem;color:#64748b;margin-top:.5rem;white-space:pre-wrap;">{{ json_encode($post->image_metadata, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                    @endif
                </div>

                {{-- Variants --}}
                @if($post->variants->isNotEmpty())
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Variações por Plataforma</h3>
                    @foreach($post->variants as $variant)
                    <div style="border:1px solid #e2e8f0;border-radius:.5rem;padding:1rem;margin-bottom:1rem;">
                        <p style="color:#0f172a;font-size:.9rem;font-weight:600;margin-bottom:.5rem;">{{ strtoupper($variant->platform) }}</p>
                        <p style="color:#475569;font-size:.85rem;line-height:1.6;white-space:pre-wrap;">{{ $variant->caption }}</p>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Generate Variants --}}
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Gerar Variações</h3>
                    <a href="{{ route('admin.social.ai.variants', $post) }}"
                       style="background:#8b5cf6;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;text-decoration:none;font-size:.85rem;">✨ Gerar com IA</a>
                </div>
            </div>

            {{-- Right Column --}}
            <div>

                {{-- Schedule --}}
                @if(in_array($post->status, ['approved','draft']))
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Agendar</h3>
                    @if($post->status !== 'approved')
                        <p style="color:#f59e0b;font-size:.8rem;margin-bottom:.75rem;">Post precisa estar aprovado para agendar.</p>
                    @endif
                    <form method="POST" action="{{ route('admin.social.posts.schedule', $post) }}">
                        @csrf @method('PATCH')
                        <input type="datetime-local" name="scheduled_at"
                               value="{{ $post->scheduled_at?->format('Y-m-d\TH:i') }}"
                               {{ $post->status !== 'approved' ? 'disabled' : '' }}
                               required
                               style="width:100%;background:#ffffff;border:1px solid #e2e8f0;color:#334155;padding:.5rem;border-radius:.375rem;font-size:.85rem;margin-bottom:.75rem;box-sizing:border-box;">
                        <button type="submit"
                                {{ $post->status !== 'approved' ? 'disabled' : '' }}
                                style="background:{{ $post->status === 'approved' ? '#8b5cf6' : '#e2e8f0' }};color:{{ $post->status === 'approved' ? '#fff' : '#94a3b8' }};padding:.5rem 1rem;border-radius:.375rem;border:none;cursor:{{ $post->status === 'approved' ? 'pointer' : 'not-allowed' }};font-size:.85rem;width:100%;">
                            Agendar
                        </button>
                    </form>
                </div>
                @endif

                {{-- Instagram Publish Now --}}
                @php
                    $instagramChannel  = \App\Models\SocialChannel::where('platform','instagram')->whereNotNull('company_id')->whereNull('client_id')->first();
                    $publishingEnabled = config('meta.instagram_publishing_enabled', false);
                    $canPublishNow     = $post->canBePublished()
                        && $publishingEnabled
                        && $instagramChannel?->isConnected()
                        && $instagramChannel?->hasValidToken();
                @endphp
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Publicação Instagram</h3>
                    @if($canPublishNow)
                        <form method="POST" action="{{ route('admin.social.posts.publish-instagram-now', $post) }}"
                              onsubmit="return confirm('Publicar agora no Instagram @lymity.ia?')">
                            @csrf
                            <button style="background:#e1306c;color:#fff;padding:.6rem 1.2rem;border-radius:.5rem;border:none;cursor:pointer;font-size:.85rem;width:100%;font-weight:600;">
                                📸 Publicar agora
                            </button>
                        </form>
                    @else
                        <div style="font-size:.8rem;color:#94a3b8;line-height:1.8;">
                            @if(!in_array($post->status, ['approved','scheduled']))<div>• Status: {{ $post->status_label }} (precisa ser aprovado)</div>@endif
                            @if($post->requires_approval && !$post->isApprovedForPublishing())<div>• Aprovação pendente</div>@endif
                            @if(!$post->hasValidImage())<div>• Imagem inválida ou ausente</div>@endif
                            @if(empty(trim($post->main_caption ?? '')))<div>• Legenda vazia</div>@endif
                            @if(!$publishingEnabled)<div>• INSTAGRAM_PUBLISHING_ENABLED=false</div>@endif
                            @if(!$instagramChannel)<div>• Canal @lymity.ia não encontrado</div>
                            @elseif(!$instagramChannel->isConnected())<div>• Canal não conectado</div>@endif
                        </div>
                    @endif
                    @if($post->isPublished() && $post->permalink)
                        <div style="margin-top:.75rem;padding-top:.75rem;border-top:1px solid #e2e8f0;">
                            <a href="{{ $post->permalink }}" target="_blank"
                               style="color:#10b981;font-size:.85rem;font-weight:600;">Ver publicação →</a>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-bottom:1.5rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Informações</h3>
                    @php
                        $infos = [
                            ['Criado',       $post->created_at->format('d/m/Y H:i')],
                            ['Agendado',     $post->scheduled_at?->format('d/m/Y H:i') ?? '—'],
                            ['Publicado',    $post->published_at?->format('d/m/Y H:i') ?? '—'],
                            ['Aprovado por', $post->approver?->name ?? '—'],
                            ['Aprovado em',  $post->approved_at?->format('d/m/Y H:i') ?? '—'],
                            ['Post ID ext.', $post->external_post_id ?? '—'],
                            ['Container IG', $post->instagram_container_id ?? '—'],
                            ['Provedor img', $post->image_provider ?? '—'],
                            ['Aprovação ob.',  $post->requires_approval ? 'Sim' : 'Não'],
                            ['Agência',       $post->isAgencyPost() ? 'Sim' : 'Não'],
                        ];
                    @endphp
                    @foreach($infos as [$label, $value])
                    <div style="display:flex;justify-content:space-between;padding:.4rem 0;border-bottom:1px solid #f1f5f9;">
                        <span style="color:#64748b;font-size:.8rem;">{{ $label }}</span>
                        <span style="color:#334155;font-size:.8rem;text-align:right;max-width:60%;word-break:break-all;">{{ $value }}</span>
                    </div>
                    @endforeach
                    @if($post->publication_error)
                    <div style="margin-top:.75rem;background:#fef2f2;border-radius:.375rem;padding:.75rem;">
                        <span style="color:#dc2626;font-size:.8rem;font-weight:600;">Erro de publicação:</span>
                        <p style="color:#dc2626;font-size:.8rem;margin-top:.25rem;word-break:break-word;">{{ $post->publication_error }}</p>
                    </div>
                    @endif
                </div>

                {{-- Back to draft / Delete --}}
                <div style="display:flex;flex-direction:column;gap:.5rem;">
                    @if(in_array($post->status, ['pending_approval','approved','scheduled','failed']))
                    <form method="POST" action="{{ route('admin.social.posts.back-to-draft', $post) }}"
                          onsubmit="return confirm('Voltar para rascunho?')">
                        @csrf
                        <button style="background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:.5rem 1rem;border-radius:.375rem;cursor:pointer;font-size:.85rem;width:100%;">
                            ↩ Voltar para Rascunho
                        </button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('admin.social.posts.destroy', $post) }}"
                          onsubmit="return confirm('Excluir este post permanentemente?')">
                        @csrf @method('DELETE')
                        <button style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:.5rem 1rem;border-radius:.375rem;cursor:pointer;font-size:.85rem;width:100%;">
                            Excluir Post
                        </button>
                    </form>
                </div>

                {{-- Brief --}}
                @if($post->creative_brief)
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-top:1rem;">
                    <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:.75rem;">Brief Criativo</h3>
                    <p style="color:#475569;font-size:.85rem;line-height:1.6;">{{ $post->creative_brief }}</p>
                </div>
                @endif

            </div>
        </div>

        {{-- Approval requests --}}
        @if($post->approvalRequests->isNotEmpty())
        <div style="background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:1.5rem;margin-top:1.5rem;">
            <h3 style="color:#475569;font-size:.85rem;font-weight:600;text-transform:uppercase;margin-bottom:1rem;">Histórico de Aprovações</h3>
            @foreach($post->approvalRequests->sortByDesc('created_at') as $approval)
            <div style="border:1px solid #e2e8f0;border-radius:.5rem;padding:.75rem;margin-bottom:.75rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;">
                    <span style="font-size:.85rem;font-weight:600;color:#0f172a;">{{ $approval->title }}</span>
                    <span style="font-size:.75rem;color:#64748b;">{{ $approval->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <span style="font-size:.75rem;padding:.15rem .5rem;border-radius:9999px;background:#e2e8f0;color:#475569;">{{ $approval->status }}</span>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</x-layouts.app>
