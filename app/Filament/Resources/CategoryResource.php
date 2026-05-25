<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domains\Catalog\Models\Category;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\CategoryResource\Pages;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Catégorie';
    protected static ?string $pluralLabel = 'Catégories';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informations générales')
                ->schema([
                    TextInput::make('name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(255),
                    Select::make('parent_id')
                        ->label('Catégorie parente')
                        ->relationship('parent', 'name')
                        ->searchable()
                        ->nullable(),
                    TextInput::make('slug')
                        ->label('Slug')
                        ->disabled()
                        ->dehydrated(false),
                    Textarea::make('description')
                        ->label('Description')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),

            Section::make('Images')
                ->schema([
                    FileUpload::make('category_image')
                        ->label('Image de catégorie')
                        ->image()
                        ->directory('categories')
                        ->columnSpanFull(),
                ]),

            Section::make('SEO')
                ->schema([
                    TextInput::make('seo_title')
                        ->label('Titre SEO')
                        ->maxLength(255),
                    TextInput::make('seo_keywords')
                        ->label('Mots-clés SEO')
                        ->maxLength(255),
                    Textarea::make('seo_description')
                        ->label('Description SEO')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Section::make('Paramètres')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Actif')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('category_image')
                    ->label('Image')
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),
                TextColumn::make('parent.name')
                    ->label('Catégorie parente')
                    ->default('-'),
                IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Statut')
                    ->options([
                        true => 'Actif',
                        false => 'Inactif',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
