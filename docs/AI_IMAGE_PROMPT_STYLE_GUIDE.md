# AI Image Prompt Style Guide — Lymity IA

## Diretriz visual obrigatória

Todas as imagens geradas por IA para Lymity IA devem seguir este padrão:

### Adjetivos-chave
- Premium · Moderno · Limpo · Maduro · Editorial
- Estratégico · Minimalista · Profissional · B2B
- Composição com respiro · Iluminação sofisticada

### Paleta
- Azul marinho escuro (dark navy) como base
- Branco off-white / cinza claro como contraste
- Gradientes sutis de azul
- Sem neon excessivo, sem cores primárias estridentes

### O que NUNCA gerar
- Imagens infantis ou carregadas
- Mascotes ou personagens caricatos
- Ilustrações 3D exageradas
- Foguetes, setas gigantes
- Dashboards exagerados com muitos dados
- Textos grandes dentro da imagem
- Frases, bullets ou blocos de texto na arte
- Pessoas com aparência artificial/plastificada
- Excesso de elementos "tech" (chips, circuitos sobrepostos, etc.)
- Poluição visual / clutter
- Visual futurístico exagerado

### Para posts de Instagram (feed)
- **Sem texto** dentro da imagem por padrão
- O texto vai na legenda (caption), não na arte
- Foco em conceito visual forte e elegante
- Composição quadrada (1:1) ou retrato (4:5) premium

### Para carrossel
- Slides podem ter texto, mas layout editorial e limpo
- Nunca gerar arte poluída

## Prompt base (inglês)

```
Premium editorial B2B visual. Clean minimal composition, sophisticated lighting,
soft dark navy and white palette with subtle blue gradients.
Modern strategic business atmosphere. Elegant abstract forms or subtle data/automation references.
No text, no slogans, no bullet points, no cartoon, no childish illustration.
No rockets, no giant arrows, no neon overload, no mascots, no exaggerated dashboards.
No 3D caricatures. No clutter. Mature, premium, trustworthy, minimal.
```

## Como o prompt é construído

`StructuredPromptBuilderService::buildImagePrompt()` monta:

1. Sujeito do conteúdo (título ou prompt bruto do Gemini)
2. Diretriz de estilo profissional (fixo)
3. Visual identity do Brand Context (se existir)

`buildProfessionalSocialImagePrompt()` para posts sociais:
- Inclui tema da caption
- Enfatiza "Instagram image"
- Aplicar quando `SocialImageService` gera imagem manual

## Se as imagens continuarem ruins

O prompt já é restritivo. Problemas residuais são do modelo Gemini.
Não alterar o fluxo — apenas tornar o prompt mais negativo:

```
NEGATIVE: cartoon, childish, exaggerated, clutter, text overlay, generic tech icons,
blue circuit boards, neon, rockets, mascots, complex diagrams
```

Aumentar a especificidade do sujeito principal.
