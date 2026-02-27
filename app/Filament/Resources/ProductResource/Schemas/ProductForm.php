<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('brand')
                            ->maxLength(255),
                        Select::make('parent_category_id')
                            ->label('Parent Category')
                            ->options(fn (): array => self::categoryOptionsByParentId(null))
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Set $set, Get $get): void {
                                if (filled($get('parent_category_id'))) {
                                    return;
                                }

                                $selected = self::resolveCategoryChain($get('category_id'));

                                $set('parent_category_id', $selected['parent']);
                                $set('child_category_id', $selected['child']);
                            })
                            ->afterStateUpdated(function (Set $set): void {
                                $set('child_category_id', null);
                                $set('category_id', null);
                            }),
                        Select::make('child_category_id')
                            ->label('Child Category')
                            ->options(fn (Get $get): array => self::categoryOptionsByParentId($get('parent_category_id')))
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->visible(fn (Get $get): bool => self::hasNestedChildren($get('parent_category_id')))
                            ->afterStateUpdated(fn (Set $set) => $set('category_id', null)),
                        Select::make('category_id')
                            ->label('Final Category')
                            ->options(fn (Get $get): array => self::finalCategoryOptions($get('parent_category_id'), $get('child_category_id')))
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('source_url')
                            ->url()
                            ->maxLength(65535),
                        Select::make('availability')
                            ->options([
                                'in_stock' => 'In stock',
                                'out_of_stock' => 'Out of stock',
                                'preorder' => 'Preorder',
                            ])
                            ->native(false),
                    ])
                    ->columns(2),
                Section::make('Pricing & Reviews')
                    ->schema([
                        TextInput::make('currency')
                            ->default('USD')
                            ->disabled()
                            ->dehydrated()
                            ->required(),
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('rating')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(5),
                        TextInput::make('review_count')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                    ])
                    ->columns(3),
                Section::make('Details')
                    ->schema([
                        KeyValue::make('attributes')
                            ->keyLabel('Attribute')
                            ->valueLabel('Value')
                            ->reorderable(),
                        TagsInput::make('bullet_points'),
                        FileUpload::make('images')
                            ->multiple()
                            ->disk(config('filesystems.default', 'public'))
                            ->directory('products')
                            ->image()
                            ->imagePreviewHeight('120')
                            ->panelLayout('grid'),
                    ]),
            ]);
    }

    /**
     * @return array<string, int|null>
     */
    private static function resolveCategoryChain(mixed $categoryId): array
    {
        if (! $categoryId) {
            return ['parent' => null, 'child' => null];
        }

        $leaf = Category::query()->with('parent.parent')->find($categoryId);

        if (! $leaf) {
            return ['parent' => null, 'child' => null];
        }

        $child = $leaf->parent;

        if (! $child) {
            return ['parent' => null, 'child' => null];
        }

        if (! $child->parent) {
            return ['parent' => $child->id, 'child' => null];
        }

        return [
            'parent' => $child->parent->id,
            'child' => $child->id,
        ];
    }


    /**
     * @return array<int, string>
     */
    private static function finalCategoryOptions(?int $parentCategoryId, ?int $childCategoryId): array
    {
        if (! $parentCategoryId) {
            return [];
        }

        if (self::hasNestedChildren($parentCategoryId) && ! $childCategoryId) {
            return [];
        }

        $sourceId = $childCategoryId ?: $parentCategoryId;

        return self::categoryOptionsByParentId($sourceId);
    }

    private static function hasNestedChildren(?int $parentCategoryId): bool
    {
        if (! $parentCategoryId) {
            return false;
        }

        return Category::query()
            ->where('parent_id', $parentCategoryId)
            ->whereHas('children')
            ->exists();
    }

    /**
     * @return array<int, string>
     */
    private static function categoryOptionsByParentId(?int $parentId): array
    {
        return Category::query()
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
