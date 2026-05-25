<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domains\Promotion\Models\Coupon;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use App\Filament\Resources\CouponResource\Pages;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $label = 'Coupon';
    protected static ?string $pluralLabel = 'Coupons';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informations du coupon')
                ->schema([
                    TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),
                    Select::make('promotion_id')
                        ->label('Promotion')
                        ->relationship('promotion', 'name')
                        ->required()
                        ->searchable(),
                    TextInput::make('usage_limit')
                        ->label('Limite d\'utilisation')
                        ->numeric()
                        ->hint('Nombre total d\'utilisations autorisées (optionnel)'),
                    TextInput::make('per_customer_limit')
                        ->label('Limite par client')
                        ->numeric()
                        ->default(1)
                        ->hint('Nombre de fois qu\'un client peut utiliser ce coupon'),
                ]),

            Section::make('Validité')
                ->schema([
                    DateTimePicker::make('valid_from')
                        ->label('Valide à partir du')
                        ->nullable(),
                    DateTimePicker::make('valid_until')
                        ->label('Valide jusqu\'au')
                        ->nullable(),
                ]),

            Section::make('Statistiques')
                ->schema([
                    TextInput::make('usage_count')
                        ->label('Nombre d\'utilisations')
                        ->disabled()
                        ->dehydrated(false),
                ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                TextColumn::make('promotion.name')
                    ->label('Promotion')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usage_count')
                    ->label('Utilisé')
                    ->alignment('center'),
                TextColumn::make('usage_limit')
                    ->label('Limite')
                    ->alignment('center')
                    ->default('-'),
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->getStateUsing(function (Coupon $record) {
                        if ($record->valid_until && $record->valid_until->isPast()) {
                            return 'Expiré';
                        }
                        if ($record->usage_limit && $record->usage_count >= $record->usage_limit) {
                            return 'Épuisé';
                        }
                        if ($record->valid_from && $record->valid_from->isFuture()) {
                            return 'Futur';
                        }
                        return 'Actif';
                    })
                    ->colors([
                        'danger' => 'Expiré',
                        'warning' => 'Épuisé',
                        'secondary' => 'Futur',
                        'success' => 'Actif',
                    ]),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // SelectFilter::make('status')
                //     ->label('Statut')
                //     ->options([
                //         'active' => 'Actif',
                //         'expired' => 'Expiré',
                //         'exhausted' => 'Épuisé',
                //     ])
                //     ->query(function ($query, $value) {
                //         return match ($value) {
                //             'active' => $query->where(function ($q) {
                //                 $q->whereNull('valid_until')->orWhere('valid_until', '>', now());
                //             }),
                //             'expired' => $query->where('valid_until', '<=', now()),
                //             'exhausted' => $query->whereColumn('usage_count', '>=', 'usage_limit'),
                //             default => $query,
                //         };
                //     }),
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
            'index' => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit' => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
