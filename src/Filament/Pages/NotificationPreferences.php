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
use Illuminate\Support\Facades\Auth;
use Usamamuneerchaudhary\Notifier\Models\EventChannelSetting;
use Usamamuneerchaudhary\Notifier\Models\NotificationChannel;
use Usamamuneerchaudhary\Notifier\Models\NotificationPreference;
use Usamamuneerchaudhary\Notifier\Models\NotificationSetting;
use Usamamuneerchaudhary\Notifier\Services\ChannelService;
use Usamamuneerchaudhary\Notifier\Services\EventService;

class NotificationPreferences extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-bell';
    protected static ?int $navigationSort = 6;
    protected string $view = 'notifier::filament.pages.notification-preferences';

    public static function getNavigationGroup(): ?string
    {
        return __(config('notifier.defaults.navigation_group', 'Notifier'));
    }

    public static function getNavigationLabel(): string
    {
        return __('notifier::notifier.pages.preferences.navigation_label');
    }

    public function getTitle(): string
    {
        return __('notifier::notifier.pages.preferences.title');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        $eventService = app(EventService::class);
        $events = $eventService->grouped();

        $channelService = app(ChannelService::class);
        $activeChannels = $channelService->getActiveChannels();

        $formData = [];

        foreach ($events as $group => $groupEvents) {
            foreach ($groupEvents as $eventKey => $event) {
                $preference = NotificationPreference::where('user_id', $user->id)
                    ->where('event_key', $eventKey)
                    ->first();

                $defaultChannels = $this->getDefaultChannelsForEventKey($eventKey);

                $channelData = [];
                foreach ($activeChannels as $channel) {
                    if ($preference && isset($preference->channels[$channel->type])) {
                        $channelData[$channel->type] = (bool) $preference->channels[$channel->type];
                    } else {
                        $channelData[$channel->type] = in_array($channel->type, $defaultChannels);
                    }
                }

                // Use sanitized key for form field name (replace dots)
                $sanitizedKey = $this->sanitizeEventKey($eventKey);
                $formData["event_{$sanitizedKey}"] = $channelData;
            }
        }

        $this->form->fill($formData);
    }

    protected function getFormSchema(): array
    {
        $eventService = app(EventService::class);
        $events = $eventService->grouped();

        $channelService = app(ChannelService::class);
        $activeChannels = $channelService->getActiveChannels();

        $sections = [];

        foreach ($events as $group => $groupEvents) {
            $fields = [];

            foreach ($groupEvents as $eventKey => $event) {
                $channelCheckboxes = [];
                $sanitizedKey = $this->sanitizeEventKey($eventKey);

                foreach ($activeChannels as $channel) {
                    $channelCheckboxes[] = Checkbox::make("event_{$sanitizedKey}.{$channel->type}")
                        ->label('')
                        ->inline(false);
                }

                $fields[] = Grid::make([
                    'default' => 1,
                    'md' => $activeChannels->count() + 1,
                ])
                    ->schema([
                        Forms\Components\Placeholder::make("event_label_{$sanitizedKey}")
                            ->label($event['name'])
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

            $sections[] = Section::make($group ?: __('notifier::notifier.pages.preferences.sections.general'))
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => $activeChannels->count() + 1,
                    ])
                        ->schema([
                            Forms\Components\Placeholder::make('header_label')
                                ->label('')
                                ->content(''),
                            ...array_map(function ($channel) {
                                return Forms\Components\Placeholder::make("header_{$channel->type}")
                                    ->label($channel->title)
                                    ->content('')
                                    ->extraAttributes([
                                        'class' => 'font-semibold text-gray-700 text-sm'
                                    ]);
                            }, $activeChannels->all()),
                        ])
                        ->extraAttributes([
                            'class' => 'border-b-2 border-gray-300 pb-3 mb-2'
                        ]),
                    ...$fields,
                ])
                ->collapsible()
                ->collapsed(false);
        }

        return $sections;
    }

    public function save(): void
    {
        $preferences = NotificationSetting::getPreferences();
        if (!($preferences['allow_override'] ?? config('notifier.settings.preferences.allow_override', true))) {
            Notification::make()
                ->title(__('notifier::notifier.pages.preferences.notifications.disabled'))
                ->body(__('notifier::notifier.pages.preferences.notifications.disabled_body'))
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $user = Auth::user();
        $channelService = app(ChannelService::class);
        $activeChannels = $channelService->getActiveChannelTypes();

        $eventService = app(EventService::class);
        $updated = 0;

        foreach ($data as $key => $channels) {
            if (!str_starts_with($key, 'event_')) {
                continue;
            }

            $sanitizedEventKey = str_replace('event_', '', $key);
            $eventKey = $this->restoreEventKey($sanitizedEventKey);
            
            if (!$eventService->exists($eventKey)) {
                continue;
            }

            $validatedChannels = [];
            foreach ($channels as $channelType => $enabled) {
                if (in_array($channelType, $activeChannels)) {
                    $validatedChannels[$channelType] = (bool) $enabled;
                }
            }

            NotificationPreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'event_key' => $eventKey,
                ],
                [
                    'channels' => $validatedChannels,
                ]
            );

            $updated++;
        }

        Notification::make()
            ->title(__('notifier::notifier.pages.preferences.notifications.saved'))
            ->body(__('notifier::notifier.pages.preferences.notifications.saved_body', ['count' => $updated]))
            ->success()
            ->send();
    }

    protected function getDefaultChannelsForEventKey(string $eventKey): array
    {
        // Check EventChannelSetting for admin-configured channels
        $channelSetting = EventChannelSetting::where('event_key', $eventKey)->first();
        if ($channelSetting && is_array($channelSetting->channels)) {
            return $channelSetting->channels;
        }

        $defaultChannels = NotificationSetting::get('preferences.default_channels', config('notifier.settings.preferences.default_channels', ['email']));
        return is_array($defaultChannels) ? $defaultChannels : ['email'];
    }

    /**
     * Sanitize event key for use in form field names (replace dots)
     */
    protected function sanitizeEventKey(string $key): string
    {
        return str_replace('.', '__DOT__', $key);
    }

    /**
     * Restore event key from sanitized form field name
     */
    protected function restoreEventKey(string $sanitized): string
    {
        return str_replace('__DOT__', '.', $sanitized);
    }
}
