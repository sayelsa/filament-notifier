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
                    ->label(__('notifier::notifier.resources.notification.fields.template'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('notifier::notifier.resources.notification.fields.user'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('channel')
                    ->label(__('notifier::notifier.resources.notification.fields.channel'))
                    ->colors([
                        'primary' => 'email',
                        'success' => 'sms',
                        'warning' => 'slack',
                        'danger' => 'push',
                    ]),
                TextColumn::make('subject')
                    ->label(__('notifier::notifier.resources.notification.fields.subject'))
                    ->limit(50)
                    ->searchable(),
                BadgeColumn::make('status')
                    ->label(__('notifier::notifier.resources.notification.fields.status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => __('notifier::notifier.resources.notification.status.pending'),
                        'sent' => __('notifier::notifier.resources.notification.status.sent'),
                        'failed' => __('notifier::notifier.resources.notification.status.failed'),
                        default => $state,
                    }),
                TextColumn::make('scheduled_at')
                    ->label(__('notifier::notifier.resources.notification.fields.scheduled_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('sent_at')
                    ->label(__('notifier::notifier.resources.notification.fields.sent_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('channel')
                    ->label(__('notifier::notifier.resources.notification.fields.channel'))
                    ->options([
                        'email' => 'Email',
                        'sms' => 'SMS',
                        'slack' => 'Slack',
                        'push' => 'Push Notification',
                    ]),
                SelectFilter::make('status')
                    ->label(__('notifier::notifier.resources.notification.fields.status'))
                    ->options([
                        'pending' => __('notifier::notifier.resources.notification.status.pending'),
                        'sent' => __('notifier::notifier.resources.notification.status.sent'),
                        'failed' => __('notifier::notifier.resources.notification.status.failed'),
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
