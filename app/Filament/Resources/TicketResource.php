<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domains\Support\Models\Ticket;
use App\Enums\TicketStatus;
use App\Enums\TicketPriority;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\TicketResource\Pages;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Tickets';
    protected static ?int $navigationSort = 6;

    protected static function getStatusOptions(): array
    {
        $options = [];
        foreach (TicketStatus::cases() as $status) {
            $options[$status->value] = $status->label();
        }
        return $options;
    }

    protected static function getPriorityOptions(): array
    {
        $options = [];
        foreach (TicketPriority::cases() as $priority) {
            $options[$priority->value] = $priority->label();
        }
        return $options;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ticket')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Sujet')
                            ->disabled(),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->disabled()
                            ->rows(3),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Client')
                            ->disabled(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(self::getStatusOptions())
                            ->enum(TicketStatus::class),

                        Forms\Components\Select::make('priority')
                            ->label('Priorité')
                            ->options(self::getPriorityOptions())
                            ->enum(TicketPriority::class),

                        Forms\Components\Select::make('assigned_to')
                            ->label('Assigné à')
                            ->relationship('assignedTo', 'name')
                            ->searchable(),
                    ])->columns(2),

                Forms\Components\Section::make('Messages')
                    ->schema([
                        Forms\Components\Repeater::make('messages')
                            ->label('Messages')
                            ->relationship()
                            ->disabled()
                            ->schema([
                                Forms\Components\TextInput::make('user.name')
                                    ->label('Auteur')
                                    ->disabled(),

                                Forms\Components\Textarea::make('message')
                                    ->label('Message')
                                    ->disabled()
                                    ->rows(2),

                                Forms\Components\TextInput::make('created_at')
                                    ->label('Date')
                                    ->disabled(),
                            ])->columns(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('subject')
                    ->label('Sujet')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Client'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'open' => 'warning',
                        'in_progress' => 'info',
                        'resolved' => 'success',
                        'closed' => 'gray',
                    ]),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priorité')
                    ->colors([
                        'low' => 'info',
                        'medium' => 'warning',
                        'high' => 'danger',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(self::getStatusOptions())
                    ->label('Statut'),

                SelectFilter::make('priority')
                    ->options(self::getPriorityOptions())
                    ->label('Priorité'),
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
            'index' => Pages\ListTickets::route('/'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
        ];
    }
}
