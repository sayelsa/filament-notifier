<?php

namespace Usamamuneerchaudhary\Notifier\Services;

class EventService
{
    /**
     * Get all configured events.
     *
     * @return array
     */
    public function all(): array
    {
        return config('notifier.events', []);
    }

    /**
     * Get all events grouped by their group name.
     *
     * @return array
     */
    public function grouped(): array
    {
        $events = $this->all();
        $grouped = [];

        foreach ($events as $key => $event) {
            $group = $event['group'] ?? 'General';
            $grouped[$group][$key] = $event;
        }

        return $grouped;
    }

    /**
     * Get a single event by key.
     *
     * @param string $key
     * @return array|null
     */
    public function get(string $key): ?array
    {
        $events = $this->all();

        return $events[$key] ?? null;
    }

    /**
     * Check if an event exists.
     *
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get all event keys.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->all());
    }

    /**
     * Get events as options for select fields.
     *
     * @return array [key => name]
     */
    public function options(): array
    {
        $events = $this->all();
        $options = [];

        foreach ($events as $key => $event) {
            $options[$key] = $event['name'] ?? $key;
        }

        return $options;
    }

    /**
     * Get events as grouped options for select fields.
     *
     * @return array [group => [key => name]]
     */
    public function groupedOptions(): array
    {
        $grouped = $this->grouped();
        $options = [];

        foreach ($grouped as $group => $events) {
            foreach ($events as $key => $event) {
                $options[$group][$key] = $event['name'] ?? $key;
            }
        }

        return $options;
    }
}
