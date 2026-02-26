<?php

namespace App\Support;

class ProductCategories
{
    /**
     * @return array<int, array{name: string, children: array<int, mixed>}>
     */
    public static function tree(): array
    {
        return [
            [
                'name' => 'Electronics',
                'children' => [
                    ['name' => 'Mobiles & Accessories', 'children' => ['Mobile Phones', 'Smartphones', 'Feature Phones', 'Phone Cases', 'Screen Protectors', 'Chargers & Cables', 'Power Banks', 'Headsets']],
                    ['name' => 'Computers & Accessories', 'children' => ['Laptops', 'Gaming Laptops', 'Desktops', 'Monitors', 'Keyboards', 'Mice', 'Storage Devices', 'Networking Equipment']],
                    ['name' => 'TV & Home Theater', 'children' => ['Televisions', 'Projectors', 'Streaming Devices', 'Soundbars', 'Speakers']],
                    ['name' => 'Cameras & Photography', 'children' => ['Digital Cameras', 'Mirrorless Cameras', 'DSLR Cameras', 'Camera Lenses', 'Tripods', 'Memory Cards']],
                ],
            ],
            [
                'name' => 'Fashion',
                'children' => [
                    ['name' => 'Women', 'children' => ['Dresses', 'Tops', 'Jeans', 'Shoes', 'Handbags', 'Jewelry']],
                    ['name' => 'Men', 'children' => ['Shirts', 'T-Shirts', 'Jeans', 'Shoes', 'Watches']],
                    ['name' => 'Kids', 'children' => ['Boys Clothing', 'Girls Clothing', 'School Shoes']],
                ],
            ],
            [
                'name' => 'Home & Kitchen',
                'children' => [
                    ['name' => 'Furniture', 'children' => ['Sofas', 'Beds', 'Dining Tables', 'Office Chairs']],
                    ['name' => 'Kitchen & Dining', 'children' => ['Cookware', 'Bakeware', 'Kitchen Appliances', 'Dinnerware']],
                    ['name' => 'Home Decor', 'children' => ['Wall Art', 'Lighting', 'Rugs', 'Curtains']],
                ],
            ],
            ['name' => 'Beauty & Personal Care', 'children' => ['Makeup', 'Skincare', 'Hair Care', 'Fragrances', "Men's Grooming"]],
            ['name' => 'Health & Household', 'children' => ['Vitamins & Supplements', 'Medical Supplies', 'Cleaning Supplies', 'Personal Hygiene']],
            ['name' => 'Sports & Outdoors', 'children' => ['Fitness Equipment', 'Outdoor Gear', 'Camping Equipment', 'Cycling', 'Team Sports']],
            ['name' => 'Automotive', 'children' => ['Car Electronics', 'Car Care', 'Interior Accessories', 'Motorcycle Accessories']],
            ['name' => 'Baby Products', 'children' => ['Diapers', 'Baby Clothing', 'Strollers', 'Toys', 'Feeding Supplies']],
            ['name' => 'Toys & Games', 'children' => ['Action Figures', 'Board Games', 'Educational Toys', 'Outdoor Play Equipment']],
            ['name' => 'Grocery & Gourmet Food', 'children' => ['Snacks', 'Beverages', 'Breakfast Foods', 'International Foods']],
            ['name' => 'Office Products', 'children' => ['Office Furniture', 'Printers', 'Stationery', 'Office Supplies']],
            ['name' => 'Industrial & Scientific', 'children' => ['Lab Equipment', 'Safety Equipment', 'Electrical Supplies', 'Tools']],
            ['name' => 'Pet Supplies', 'children' => ['Dog Supplies', 'Cat Supplies', 'Fish & Aquatic Pets', 'Pet Food']],
            ['name' => 'Books', 'children' => ['Fiction', 'Non-Fiction', 'Educational', "Children's Books"]],
            ['name' => 'Video Games', 'children' => ['Consoles', 'Video Games', 'Gaming Accessories', 'VR Equipment']],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function topLevelOptions(): array
    {
        $options = [];

        foreach (self::tree() as $node) {
            $options[$node['name']] = $node['name'];
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function nestedPairOptions(?string $topLevel): array
    {
        if (blank($topLevel)) {
            return [];
        }

        $node = self::findTopLevelNode($topLevel);

        if (! $node) {
            return [];
        }

        $options = [];

        foreach ($node['children'] as $child) {
            if (! is_array($child) || ! isset($child['name'])) {
                continue;
            }

            $path = $topLevel.' > '.$child['name'];
            $options[$path] = $child['name'];
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function optionsForPair(?string $topLevel, ?string $pairPath): array
    {
        if (blank($topLevel)) {
            return [];
        }

        $node = self::findTopLevelNode($topLevel);

        if (! $node) {
            return [];
        }

        if (blank($pairPath)) {
            return self::leafOptionsFromChildren($topLevel, $node['children']);
        }

        $target = self::findNodeByPath($node, $pairPath);

        if (! is_array($target) || ! isset($target['children']) || ! is_array($target['children'])) {
            return [];
        }

        return self::leafOptionsFromChildren($pairPath, $target['children']);
    }

    public static function hasNestedPair(?string $topLevel): bool
    {
        return self::nestedPairOptions($topLevel) !== [];
    }

    public static function guessTopLevel(?string $category): ?string
    {
        if (blank($category)) {
            return null;
        }

        foreach (array_keys(self::topLevelOptions()) as $topLevel) {
            if ($category === $topLevel || str_starts_with($category, $topLevel.' > ')) {
                return $topLevel;
            }
        }

        return null;
    }

    public static function guessNestedPair(?string $category): ?string
    {
        if (blank($category)) {
            return null;
        }

        $parts = explode(' > ', $category);

        if (count($parts) < 3) {
            return null;
        }

        return $parts[0].' > '.$parts[1];
    }

    /**
     * @return array{name: string, children: array<int, mixed>}|null
     */
    private static function findTopLevelNode(string $topLevel): ?array
    {
        foreach (self::tree() as $node) {
            if ($node['name'] === $topLevel) {
                return $node;
            }
        }

        return null;
    }

    /**
     * @param  array{name: string, children?: array<int, mixed>}  $node
     * @return array{name: string, children?: array<int, mixed>}|null
     */
    private static function findNodeByPath(array $node, string $path): ?array
    {
        $parts = explode(' > ', $path);

        if (($parts[0] ?? null) !== ($node['name'] ?? null)) {
            return null;
        }

        $current = $node;

        foreach (array_slice($parts, 1) as $part) {
            $next = null;

            foreach (($current['children'] ?? []) as $child) {
                if (is_array($child) && ($child['name'] ?? null) === $part) {
                    $next = $child;
                    break;
                }
            }

            if (! $next) {
                return null;
            }

            $current = $next;
        }

        return $current;
    }

    /**
     * @param  array<int, mixed>  $children
     * @return array<string, string>
     */
    private static function leafOptionsFromChildren(string $prefix, array $children): array
    {
        $options = [];

        foreach ($children as $child) {
            if (is_string($child)) {
                $value = $prefix.' > '.$child;
                $options[$value] = $child;
            }
        }

        return $options;
    }
}
