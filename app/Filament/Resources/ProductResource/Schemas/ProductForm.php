<?php

namespace App\Filament\Resources\ProductResource\Schemas;

use App\Support\ProductCategories;
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
                        Select::make('category_key')
                            ->label('Category Key')
                            ->options(ProductCategories::topLevelOptions())
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateHydrated(function (Set $set, Get $get): void {
                                if (filled($get('category_key'))) {
                                    return;
                                }

                                $set('category_key', ProductCategories::guessTopLevel($get('category')));
                                $set('category_pair', ProductCategories::guessNestedPair($get('category')));
                            })
                            ->afterStateUpdated(function (Set $set): void {
                                $set('category_pair', null);
                                $set('category', null);
                            }),
                        Select::make('category_pair')
                            ->label('Category Pair')
                            ->options(fn (Get $get): array => ProductCategories::nestedPairOptions($get('category_key')))
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->visible(fn (Get $get): bool => ProductCategories::hasNestedPair($get('category_key')))
                            ->afterStateUpdated(fn (Set $set) => $set('category', null))
                            ->placeholder('Select category pair'),
                        Select::make('category')
                            ->label('Category Value')
                            ->options(fn (Get $get): array => ProductCategories::optionsForPair($get('category_key'), $get('category_pair')))
                            ->searchable()
                            ->placeholder('Select category value'),
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
