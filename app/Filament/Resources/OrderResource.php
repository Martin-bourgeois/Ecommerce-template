<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domains\Order\Models\Order;
use App\Enums\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\OrderResource\Pages;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Commandes';
    protected static ?int $navigationSort = 2;

    protected static function getStatusOptions(): array
    {
        $options = [];
        foreach (OrderStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }
        return $options;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations commande')
                    ->schema([
                        Forms\Components\TextInput::make('order_number')
                            ->label('N° Commande')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(self::getStatusOptions())
                            ->enum(OrderStatus::class),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Client')
                            ->disabled(),

                        Forms\Components\TextInput::make('total_cents')
                            ->label('Total')
                            ->disabled()
                    ])->columns(2),

                Forms\Components\Section::make('Articles')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Articles')
                            ->relationship()
                            ->disabled()
                            ->schema([
                                Forms\Components\TextInput::make('product.name')
                                    ->label('Produit')
                                    ->disabled(),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Quantité')
                                    ->disabled(),

                                Forms\Components\TextInput::make('price_cents')
                                    ->label('Prix unitaire')
                                    ->disabled(),
                            ])->columns(3),
                    ]),

                Forms\Components\Section::make('Livraison')
                    ->schema([
                        Forms\Components\TextInput::make('shipping_address')
                            ->label('Adresse')
                            ->disabled(),

                        Forms\Components\TextInput::make('tracking_number')
                            ->label('Numéro de suivi')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('N° Commande')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_cents')
                    ->label('Total')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'pending' => 'warning',
                        'processing' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(self::getStatusOptions())
                    ->label('Statut'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('ship')
                    ->label('Expédier')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->visible(fn (Order $record) => $record->status === OrderStatus::PROCESSING)
                    ->action(fn (Order $record) => $record->update(['status' => OrderStatus::SHIPPED]))
                    ->requiresConfirmation(),
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
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
