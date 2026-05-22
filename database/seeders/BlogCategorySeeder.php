<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Inteligência Artificial', 'slug' => 'inteligencia-artificial', 'icon' => '🤖', 'description' => 'IA aplicada ao crescimento de negócios'],
            ['name' => 'Tráfego Pago',            'slug' => 'trafego-pago',            'icon' => '🎯', 'description' => 'Google Ads, Meta Ads e estratégias de mídia paga'],
            ['name' => 'Automação',               'slug' => 'automacao',               'icon' => '⚡', 'description' => 'Automação de marketing, vendas e operação'],
            ['name' => 'SEO',                     'slug' => 'seo',                     'icon' => '🔍', 'description' => 'Otimização para mecanismos de busca'],
            ['name' => 'Crescimento',             'slug' => 'crescimento',             'icon' => '📈', 'description' => 'Estratégias e sistemas de crescimento empresarial'],
            ['name' => 'Desenvolvimento',         'slug' => 'desenvolvimento',         'icon' => '💻', 'description' => 'Tecnologia, sistemas e desenvolvimento digital'],
        ];

        foreach ($categories as $cat) {
            BlogCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
