<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Support\ProductCategories;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (ProductCategories::tree() as $categoryNode) {
            $this->seedNode($categoryNode, null);
        }
    }

    /**
     * @param  array{name: string, children?: array<int, mixed>}  $node
     */
    private function seedNode(array $node, ?int $parentId): void
    {
        $category = Category::query()->updateOrCreate(
            ['name' => $node['name'], 'parent_id' => $parentId],
            [],
        );

        foreach ($node['children'] ?? [] as $child) {
            if (is_string($child)) {
                Category::query()->updateOrCreate(
                    ['name' => $child, 'parent_id' => $category->id],
                    [],
                );

                continue;
            }

            if (is_array($child) && isset($child['name'])) {
                $this->seedNode($child, $category->id);
            }
        }
    }
}
