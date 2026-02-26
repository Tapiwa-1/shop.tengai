<?php

namespace App\Filament\Resources\ProductResource\Schemas;

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
                        TextInput::make('asin')
                            ->maxLength(20)
                            ->minLength(10)
                            ->unique(ignoreRecord: true),
                        TextInput::make('sku')
                            ->maxLength(255),
                        TextInput::make('category')
                            ->maxLength(255),
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
                            ->required()
                            ->maxLength(3),
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('$'),
                        TextInput::make('compare_at_price')
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
