<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
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
                        Select::make('category_id')
                            ->label('Category')
                            ->options(fn (): array => Category::query()
                                ->with(['parent.parent'])
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Category $category): array => [$category->id => $category->full_name])
                                ->all())
                            ->searchable()
                            ->preload(),
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
}
