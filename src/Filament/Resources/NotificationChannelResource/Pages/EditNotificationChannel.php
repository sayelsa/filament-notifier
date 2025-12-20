<?php
namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationChannelResource\Pages;

use Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationChannel extends EditRecord
{
    protected static string $resource = NotificationChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
