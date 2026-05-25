<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domains\Returns\Models\Rma;
use App\Enums\RmaStatus;
use App\Enums\ReturnReason;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\RmaResource\Pages;

class RmaResource extends Resource
{
    protected static ?string $model = Rma::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationLabel = 'Retours (RMA)';
    protected static ?int $navigationSort = 7;

    protected static function getStatusOptions(): array
    {
        $options = [];
        foreach (RmaStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }
        return $options;
    }

    protected static function getReasonOptions(): array
    {
        $options = [];
        foreach (ReturnReason::cases() as $reason) {
            $options[$reason->value] = $reason->label();
        }
        return $options;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('RMA')
                    ->schema([
                        Forms\Components\TextInput::make('rma_number')
                            ->label('N° RMA')
                            ->disabled(),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Client')
                            ->disabled(),

                        Forms\Components\TextInput::make('order.order_number')
                            ->label('N° Commande')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(self::getStatusOptions())
                            ->enum(RmaStatus::class),

                        Forms\Components\Select::make('reason')
                            ->label('Raison')
                            ->options(self::getReasonOptions())
                            ->enum(ReturnReason::class)
                            ->disabled(),

                        Forms\Components\TextInput::make('refund_amount')
                            ->label('Montant remboursé')
                            ->money('EUR')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Articles')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->label('Articles retournés')
                            ->relationship()
                            ->disabled()
                            ->schema([
                                Forms\Components\TextInput::make('orderItem.product.name')
                                    ->label('Produit')
                                    ->disabled(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Quantité')
                                    ->disabled(),

                                Forms\Components\TextInput::make('refund_amount')
                                    ->label('Montant')
                                    ->disabled(),
                            ])->columns(3),
                    ]),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('customer_notes')
                            ->label('Notes client')
                            ->disabled()
                            ->rows(2),

                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Notes administrateur')
                            ->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('rma_number')
                    ->label('N° RMA')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client')
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Commande')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'requested' => 'warning',
                        'approved' => 'info',
                        'received' => 'primary',
                        'inspected' => 'primary',
                        'refunded' => 'success',
                        'rejected' => 'danger',
                    ]),

                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('Remboursement')
                    ->money('EUR'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(self::getStatusOptions())
                    ->label('Statut'),

                SelectFilter::make('reason')
                    ->options(self::getReasonOptions())
                    ->label('Raison'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListRmas::route('/'),
            'edit' => Pages\EditRma::route('/{record}/edit'),
        ];
    }
}
