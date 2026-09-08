<?php

namespace JeffersonGoncalves\Paddle\Resources;

class Notifications extends Resource
{
    protected string $path = '/notifications';

    /** Re-send a notification to its destination. */
    public function replay(string $id): array
    {
        return $this->client->post($this->path.'/'.$id.'/replay');
    }

    /** @param array<string, mixed> $params */
    public function logs(string $id, array $params = []): array
    {
        return $this->client->get($this->path.'/'.$id.'/logs', $params);
    }
}
