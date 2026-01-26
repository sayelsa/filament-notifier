<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationChannelResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class NotificationChannelTable
{

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('notifier::notifier.resources.channel.fields.title.label'))
                    ->searchable(),
                TextColumn::make('type')
                    ->label(__('notifier::notifier.resources.channel.fields.type.label'))
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label(__('notifier::notifier.resources.channel.fields.is_active.label'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('notifier::notifier.resources.channel.fields.is_active.label')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

}
