<?php

namespace App\Services\Brand;

use App\Models\AgencyBrandContext;
use App\Models\Company;
use RuntimeException;

class BrandContextService
{
    public function getActiveAgencyContext(): ?AgencyBrandContext
    {
        return AgencyBrandContext::where('active', true)
            ->whereNull('client_id')
            ->latest()
            ->first();
    }

    public function getCompactContext(): string
    {
        $ctx = $this->getActiveAgencyContext();
        if (!$ctx) return '';
        return $ctx->toCompactString();
    }

    public function validateReadyForGeneration(): void
    {
        $ctx = $this->getActiveAgencyContext();

        if (!$ctx) {
            throw new RuntimeException(
                'Nenhum contexto de marca ativo encontrado. Configure o BrandContext em /admin/agency/brand-context.'
            );
        }

        if (!$ctx->isComplete()) {
            throw new RuntimeException(
                'Contexto de marca incompleto. Preencha pelo menos: nome da marca, posicionamento, tom de voz e público-alvo.'
            );
        }
    }
}
