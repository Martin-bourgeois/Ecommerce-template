<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domains\Promotion\Models\Promotion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\PromotionResource\Pages;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-percent-badge';
    protected static ?string $navigationLabel = 'Promotions';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->live()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('slug', str()->slug($state))),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(3),

                        Forms\Components\TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly identifier'),
                    ]),

                Forms\Components\Section::make('Conditions')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'percentage' => 'Pourcentage (%)',
                                'fixed_amount' => 'Montant fixe (€)',
                            ])
                            ->required(),

                        Forms\Components\Select::make('target')
                            ->label('Cible')
                            ->options([
                                'order' => 'Commande',
                                'product' => 'Produit',
                                'category' => 'Catégorie',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('value')
                            ->label('Valeur')
                            ->numeric()
                            ->required()
                            ->step(0.01),

                        Forms\Components\TextInput::make('min_amount')
                            ->label('Montant minimum (€)')
                            ->numeric()
                            ->step(0.01),

                        Forms\Components\TextInput::make('max_uses')
                            ->label('Nombre d\'utilisations max')
                            ->numeric(),
                    ])->columns(2),

                Forms\Components\Section::make('Dates')
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Début'),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Fin'),
                    ])->columns(2),

                Forms\Components\Toggle::make('is_active')
                    ->label('Actif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => $state === 'percentage' ? 'Pourcentage' : 'Montant fixe'),

                Tables\Columns\TextColumn::make('target')
                    ->label('Cible')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'order' => 'Commande',
                        'product' => 'Produit',
                        'category' => 'Catégorie',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('value')
                    ->label('Valeur')
                    ->formatStateUsing(fn ($state, $record) => $record->type === 'percentage' ? $state . '%' : $state . '€'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Début')
                    ->dateTime('d/m/Y'),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Fin')
                    ->dateTime('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut'),
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
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
