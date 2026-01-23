<?php

namespace Usamamuneerchaudhary\Notifier\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Usamamuneerchaudhary\Notifier\Models\EventChannelSetting;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Services\EventService;

class EventChannelConfiguration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Event Channels';
    protected static ?string $title = 'Event Channel Configuration';
    protected static string|null|\UnitEnum $navigationGroup = 'Notifier';
    protected static ?int $navigationSort = 3;
    protected string $view = 'notifier::filament.pages.event-channel-configuration';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->getFormData());
    }

    protected function getFormData(): array
    {
        $eventService = app(EventService::class);
        $events = $eventService->grouped();

        $activeChannels = NotificationChannel::where('is_active', true)
            ->orderBy('title')
            ->get();

        // Get current settings from database
        $currentSettings = EventChannelSetting::getAllSettings();

        $formData = [];

        foreach ($events as $group => $groupEvents) {
            foreach ($groupEvents as $eventKey => $event) {
                $eventChannels = $currentSettings[$eventKey] ?? [];

                $channelData = [];
                foreach ($activeChannels as $channel) {
                    $channelData[$channel->type] = in_array($channel->type, $eventChannels);
                }

                // Sanitize event key for form field name
                $fieldKey = $this->sanitizeEventKey($eventKey);
                $formData["event_{$fieldKey}"] = $channelData;
            }
        }

        return $formData;
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        $eventService = app(EventService::class);
        $events = $eventService->grouped();

        $activeChannels = NotificationChannel::where('is_active', true)
            ->orderBy('title')
            ->get();

        $sections = [];

        foreach ($events as $group => $groupEvents) {
            $fields = [];

            foreach ($groupEvents as $eventKey => $event) {
                $channelCheckboxes = [];
                $fieldKey = $this->sanitizeEventKey($eventKey);

                foreach ($activeChannels as $channel) {
                    $channelCheckboxes[] = Checkbox::make("event_{$fieldKey}.{$channel->type}")
                        ->label($channel->title)
                        ->inline(false);
                }

                $fields[] = Grid::make([
                    'default' => 1,
                    'md' => $activeChannels->count() + 1,
                ])
                    ->schema([
                        Forms\Components\Placeholder::make("event_label_{$fieldKey}")
                            ->label($event['name'] ?? $eventKey)
                            ->content($event['description'] ?? '')
                            ->extraAttributes([
                                'class' => 'font-medium text-gray-900'
                            ]),
                        ...$channelCheckboxes,
                    ])
                    ->extraAttributes([
                        'class' => 'border-b border-gray-200 py-4 first:pt-0 last:border-b-0 last:pb-0'
                    ]);
            }

            $sections[] = Section::make($group ?: 'General')
                ->description('Configure which channels should be used for each event.')
                ->schema([
                    ...$fields,
                ])
                ->collapsible()
                ->collapsed(false);
        }

        return $sections;
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $activeChannels = NotificationChannel::where('is_active', true)
            ->pluck('type')
            ->toArray();

        $eventService = app(EventService::class);
        $allEvents = $eventService->all();
        
        $updated = 0;

        foreach ($data as $key => $channels) {
            if (!str_starts_with($key, 'event_')) {
                continue;
            }

            // Restore event key from sanitized form field name
            $fieldKey = str_replace('event_', '', $key);
            $eventKey = $this->restoreEventKey($fieldKey, array_keys($allEvents));

            if (!$eventKey || !isset($allEvents[$eventKey])) {
                continue;
            }

            $enabledChannels = [];
            foreach ($channels as $channelType => $enabled) {
                if ($enabled && in_array($channelType, $activeChannels)) {
                    $enabledChannels[] = $channelType;
                }
            }

            EventChannelSetting::setChannelsForEvent($eventKey, $enabledChannels);
            $updated++;
        }

        Notification::make()
            ->title('Configuration Saved')
            ->body("Successfully updated channel configuration for {$updated} event(s).")
            ->success()
            ->send();
    }

    /**
     * Sanitize event key for use as form field name.
     * Replaces dots with double underscores.
     */
    protected function sanitizeEventKey(string $eventKey): string
    {
        return str_replace('.', '__', $eventKey);
    }

    /**
     * Restore event key from sanitized form field name.
     */
    protected function restoreEventKey(string $fieldKey, array $validKeys): ?string
    {
        $eventKey = str_replace('__', '.', $fieldKey);
        
        return in_array($eventKey, $validKeys) ? $eventKey : null;
    }
}
