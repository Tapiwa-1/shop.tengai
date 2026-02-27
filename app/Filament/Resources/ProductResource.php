<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\ViewProduct;
use App\Filament\Resources\ProductResource\Schemas\ProductForm;
use App\Models\Category;
use App\Models\Product;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('category.full_name')
                    ->label('Category')
                    ->toggleable(),
                TextColumn::make('price')
                    ->money('USD', divideBy: 1)
                    ->sortable(),
                TextColumn::make('availability')
                    ->badge()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('availability')
                    ->options([
                        'in_stock' => 'In stock',
                        'out_of_stock' => 'Out of stock',
                        'preorder' => 'Preorder',
                    ]),
                SelectFilter::make('brand')
                    ->options(fn () => Product::query()
                        ->whereNotNull('brand')
                        ->orderBy('brand')
                        ->distinct()
                        ->pluck('brand', 'brand')
                        ->all())
                    ->searchable(),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->options(fn (): array => Category::query()
                        ->with(['parent.parent'])
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Category $category): array => [$category->id => $category->full_name])
                        ->all())
                    ->searchable(),
            ])
            ->searchable(['title', 'brand'])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'view' => ViewProduct::route('/{record}'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
