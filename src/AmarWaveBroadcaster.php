<?php

declare(strict_types=1);

namespace AmarWave\php;

use AmarWave\AmarWave;
use AmarWave\AmarWaveException;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * php Broadcasting driver for AmarWave.
 *
 * Enables `broadcast(new YourEvent)` to push events to AmarWave channels.
 *
 * Setup — add to config/broadcasting.php:
 *   'connections' => [
 *       'amarwave' => ['driver' => 'amarwave'],
 *   ],
 *
 * Then set BROADCAST_DRIVER=amarwave in .env.
 *
 * Channel authorization is handled via routes/channels.php (same as Pusher).
 */
class AmarWaveBroadcaster extends Broadcaster
{
    public function __construct(private readonly AmarWave $amarwave) {}

    // -------------------------------------------------------------------------
    // Broadcaster contract
    // -------------------------------------------------------------------------

    /**
     * Authenticate an incoming channel subscription request.
     *
     * Called automatically when the client SDK hits /broadcasting/auth.
     * Validates the user against the channel callbacks in routes/channels.php,
     * then returns a signed auth token.
     *
     * @throws AccessDeniedHttpException
     */
    public function auth($request): mixed
    {
        $channelName = (string) $request->input('channel_name', '');
        $socketId    = (string) $request->input('socket_id', '');

        // Validate against routes/channels.php callbacks.
        $normalised  = $this->normaliseChannelName($channelName);
        $channelAuth = $this->verifyUserCanAccessChannel($request, $normalised);

        if (str_starts_with($channelName, 'presence-')) {
            $user = $request->user();

            $channelData = [
                'user_id'   => (string) ($user?->getAuthIdentifier() ?? ''),
                'user_info' => is_array($channelAuth) ? $channelAuth : [],
            ];

            return $this->amarwave->authenticatePresence($socketId, $channelName, $channelData);
        }

        return ['auth' => $this->amarwave->authenticate($socketId, $channelName)];
    }

    /**
     * Return the valid authentication response to the client.
     */
    public function validAuthenticationResponse($request, $result): mixed
    {
        return response()->json($result);
    }

    /**
     * Broadcast the given event on all specified channels.
     *
     * Called by php when you dispatch a `ShouldBroadcast` event.
     *
     * @param  string[] $channels  Channel names from `broadcastOn()`.
     * @param  string   $event     Event name from `broadcastAs()`.
     * @param  array    $payload   Data payload from `broadcastWith()`.
     *
     * @throws BroadcastException
     */
    public function broadcast(array $channels, $event, array $payload = []): void
    {
        unset($payload['socket']); // strip php-internal socket_id key

        foreach ($channels as $channel) {
            $name = $this->formatChannelName((string) $channel);

            try {
                $this->amarwave->trigger($name, $event, $payload);
            } catch (AmarWaveException $e) {
                throw new BroadcastException(
                    "AmarWave broadcast failed on channel '{$name}': {$e->getMessage()}"
                );
            }
        }
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Strip private- / presence- prefix to match channel route definitions.
     */
    private function normaliseChannelName(string $channel): string
    {
        foreach (['private-encrypted-', 'private-', 'presence-'] as $prefix) {
            if (str_starts_with($channel, $prefix)) {
                return substr($channel, strlen($prefix));
            }
        }
        return $channel;
    }

    /**
     * Ensure we always work with plain channel name strings.
     */
    private function formatChannelName(string $channel): string
    {
        return $channel;
    }
}
