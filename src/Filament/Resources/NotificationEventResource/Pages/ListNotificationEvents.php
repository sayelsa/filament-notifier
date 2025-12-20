<?php
namespace Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationEventResource\Pages;

use Usamamuneerchaudhary\Notifier\Filament\Resources\NotificationEventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotificationEvents extends ListRecords
{
    protected static string $resource = NotificationEventResource::class;


    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
