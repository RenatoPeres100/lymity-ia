<?php

namespace App\Traits;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

trait HasBulkDestroy
{
    /**
     * Bulk delete records.
     * Controller must define: $bulkDestroyModel (string FQCN) and optional $bulkDestroyPolicy (string).
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = array_filter(array_map('intval', $request->input('ids', [])));

        if (empty($ids)) {
            return back()->with('error', 'Nenhum item selecionado.');
        }

        $modelClass = $this->bulkDestroyModel ?? null;
        if (!$modelClass) {
            return back()->with('error', 'Operação não configurada.');
        }

        $query = $modelClass::whereIn('id', $ids);

        // Scope to user's company if not admin_geral
        $user = auth()->user();
        if ($user->role !== 'admin_geral' && isset($this->bulkDestroyScope)) {
            $field = $this->bulkDestroyScope;
            $value = $user->$field;
            if ($value) {
                $query->where($field, $value);
            }
        }

        $count = $query->count();
        $query->delete();

        return back()->with('success', "{$count} " . ($count === 1 ? 'item excluído.' : 'itens excluídos.'));
    }
}
