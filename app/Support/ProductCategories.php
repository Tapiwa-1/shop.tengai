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
    public static function optionsForTopLevel(?string $topLevel): array
    {
        if (blank($topLevel)) {
            return [];
        }

        foreach (self::tree() as $node) {
            if ($node['name'] !== $topLevel) {
                continue;
            }

            return self::flattenChildren($topLevel, $node['children']);
        }

        return [];
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

    /**
     * @param  array<int, mixed>  $children
     * @return array<string, string>
     */
    private static function flattenChildren(string $prefix, array $children): array
    {
        $options = [];

        foreach ($children as $child) {
            if (is_string($child)) {
                $value = $prefix.' > '.$child;
                $options[$value] = $value;

                continue;
            }

            if (! is_array($child) || ! isset($child['name'])) {
                continue;
            }

            $nextPrefix = $prefix.' > '.$child['name'];

            if (! isset($child['children']) || ! is_array($child['children'])) {
                $options[$nextPrefix] = $nextPrefix;

                continue;
            }

            $options += self::flattenChildren($nextPrefix, $child['children']);
        }

        return $options;
    }
}
