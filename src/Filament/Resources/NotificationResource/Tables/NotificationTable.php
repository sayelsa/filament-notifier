<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationResource\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotificationTable
{

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('template.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('channel')
                    ->colors([
                        'primary' => 'email',
                        'success' => 'sms',
                        'warning' => 'slack',
                        'danger' => 'push',
                    ]),
                TextColumn::make('subject')
                    ->limit(50)
                    ->searchable(),
                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ]),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'slack' => 'Slack',
                        'push' => 'Push Notification',
                    ]),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ]),
                Filter::make('scheduled_at')
                    ->form([
                        DatePicker::make('scheduled_from'),
                        DatePicker::make('scheduled_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['scheduled_from'],
                                fn($query) => $query->whereDate('scheduled_at', '>=', $data['scheduled_from'])
                            )
                            ->when(
                                $data['scheduled_until'],
                                fn($query) => $query->whereDate('scheduled_at', '<=', $data['scheduled_until'])
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('resend')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'failed')
                    ->action(function ($record) {
                        // Resend the notification
                        $record->update(['status' => 'pending']);
                        \Usamamuneerchaudhary\Notifier\Jobs\SendNotificationJob::dispatch($record->id);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

}
