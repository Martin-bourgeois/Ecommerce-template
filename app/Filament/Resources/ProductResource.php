<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\ProductResource\Pages;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Produits';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->description('Détails basiques du produit')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(4),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->unique(ignoreRecord: true)
                            ->required(),

                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Tarification')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('Prix (€)')
                            ->numeric()
                            ->required()
                            ->step(0.01),

                        Forms\Components\TextInput::make('cost')
                            ->label('Coût (€)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('stock')
                            ->label('Stock')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ])->columns(3),

                Forms\Components\Section::make('Média')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->label('Image principale')
                            ->image()
                            ->disk('public')
                            ->directory('products'),

                        Forms\Components\FileUpload::make('images')
                            ->label('Galerie')
                            ->multiple()
                            ->image()
                            ->disk('public')
                            ->directory('products/gallery'),
                    ])->columns(2),

                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('URL')
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta titre'),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta description')
                            ->rows(2),
                    ]),

                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Produit')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Prix')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->color(fn ($state) => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Catégorie'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->nullable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
