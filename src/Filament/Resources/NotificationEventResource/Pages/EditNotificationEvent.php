<?php
namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationEventResource\Pages;

use Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationEvent extends EditRecord
{
    protected static string $resource = NotificationEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
