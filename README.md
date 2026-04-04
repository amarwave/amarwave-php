# amarwave-php

Official php integration for [AmarWave](https://github.com/amarwave/amarwave) real-time messaging.

Provides a **service provider**, **facade**, and **first-class broadcasting driver** so you can push events through php's standard `broadcast()` / `event()` helpers with zero boilerplate.

> This package wraps [`amarwave/amarwave-php`](../php) — the pure PHP server client.

---

## Requirements

- PHP 8.1+
- php 10, 11, or 12

---

## Installation

```bash
composer require amarwave/amarwave-php
```

php auto-discovers the service provider and facade via `composer.json`.

---

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=amarwave-config
```

Add to your `.env`:

```dotenv
AMARWAVE_APP_KEY=your-app-key
AMARWAVE_APP_SECRET=your-app-secret
AMARWAVE_HOST=localhost
AMARWAVE_PORT=8000
AMARWAVE_SSL=false
AMARWAVE_TIMEOUT=10
```

---

## Triggering Events (Facade)

```php
use AmarWave\php\AmarWaveFacade as AmarWave;

// Single event
AmarWave::trigger('orders', 'placed', ['order_id' => 42]);

// Batch
AmarWave::triggerBatch([
    ['channel' => 'chat-1', 'event' => 'message', 'data' => ['text' => 'Hello']],
    ['channel' => 'chat-2', 'event' => 'message', 'data' => ['text' => 'World']],
]);
```

Or with the global alias (auto-registered):

```php
\AmarWave::trigger('orders', 'placed', ['order_id' => 42]);
```

---

## Dependency Injection

```php
use AmarWave\AmarWave;

class OrderController extends Controller
{
    public function __construct(private readonly AmarWave $aw) {}

    public function store(Request $request): JsonResponse
    {
        $order = Order::create($request->validated());
        $this->aw->trigger('orders', 'placed', $order->toArray());
        return response()->json($order, 201);
    }
}
```

---

## Broadcasting Driver

Use AmarWave as a php broadcasting driver so `broadcast(new YourEvent)` works out of the box.

### 1 — Add the connection to `config/broadcasting.php`

```php
'connections' => [

    'amarwave' => [
        'driver' => 'amarwave',
    ],

    // ...existing drivers
],
```

### 2 — Set the driver in `.env`

```dotenv
BROADCAST_DRIVER=amarwave
```

### 3 — Create a broadcastable event

```php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrderPlaced implements ShouldBroadcast
{
    public function __construct(public readonly array $order) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('orders'),
            new PrivateChannel("user.{$this->order['user_id']}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order['id'],
            'total'    => $this->order['total'],
        ];
    }
}
```

### 4 — Dispatch

```php
broadcast(new OrderPlaced($order));
// or via the event system (queued by default with ShouldBroadcast)
event(new OrderPlaced($order));
```

---

## Channel Authorization (private / presence)

### Register the auth route

In `routes/api.php` (or `routes/web.php`):

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);
```

### Define channel callbacks in `routes/channels.php`

```php
use Illuminate\Support\Facades\Broadcast;

// Private channel — return true/false
Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Presence channel — return member data array (or false to deny)
Broadcast::channel('presence-room.{roomId}', function ($user, $roomId) {
    $room = \App\Models\Room::find($roomId);
    if ($room?->members()->where('user_id', $user->id)->exists()) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});
```

### Manual auth endpoint (alternative)

If you prefer a manual route instead of `Broadcast::routes()`:

```php
Route::post('/broadcasting/auth', function (Request $request) {
    $socketId = $request->input('socket_id');
    $channel  = $request->input('channel_name');

    if (str_starts_with($channel, 'presence-')) {
        $auth = app(\AmarWave\AmarWave::class)->authenticatePresence(
            $socketId,
            $channel,
            ['user_id' => (string) $request->user()->id,
             'user_info' => ['name' => $request->user()->name]]
        );
    } else {
        $auth = ['auth' => app(\AmarWave\AmarWave::class)->authenticate($socketId, $channel)];
    }

    return response()->json($auth);
})->middleware('auth:sanctum');
```

---

## AmarWave Client-Side Config

Point your JS/Flutter/Swift client at the same server:

```js
const aw = new AmarWave({
    appKey: 'your-app-key',
    appSecret: 'your-app-secret',   // OR use authEndpoint
    wsHost: 'localhost',
    wsPort: 3001,
    apiHost: 'localhost',
    apiPort: 8000,
    authEndpoint: '/broadcasting/auth',
    auth: { headers: { Authorization: `Bearer ${token}` } },
});
```

---

## Error Handling

```php
use AmarWave\AmarWaveException;

try {
    AmarWave::trigger('channel', 'event', $data);
} catch (AmarWaveException $e) {
    $status = $e->getStatusCode();
    $body   = $e->getResponseBody();
    logger()->error("AmarWave {$status}: {$e->getMessage()}");
}
```

---

## License

MIT
