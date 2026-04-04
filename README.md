# amarwave-php

Official PHP client for [AmarWave](https://github.com/amarwave/amarwave) real-time messaging.

Works in **any PHP environment** — raw PHP, Laravel, Symfony, or any other framework.

For Laravel projects, a **service provider**, **facade**, and **broadcasting driver** are included automatically.

---

## Requirements

- PHP 8.1+
- `ext-curl`
- `ext-json`
- Laravel 10, 11, or 12 *(optional — only needed for the Laravel integration)*

---

## Installation

```bash
composer require amarwave/amarwave-php
```

Laravel auto-discovers the service provider and facade via `composer.json`.

---

## Usage — Raw PHP

```php
use AmarWave\AmarWave;
use AmarWave\AmarWaveException;

$aw = new AmarWave(
    appKey:    'your-app-key',
    appSecret: 'your-app-secret',
    cluster:   'default',
);

// Trigger a single event
$aw->trigger('orders', 'placed', ['order_id' => 42]);

// Trigger multiple events in one request
$aw->triggerBatch([
    ['channel' => 'chat-1', 'event' => 'message', 'data' => ['text' => 'Hello']],
    ['channel' => 'chat-2', 'event' => 'message', 'data' => ['text' => 'World']],
]);
```

### Error handling

```php
use AmarWave\AmarWaveException;

try {
    $aw->trigger('channel', 'event', $data);
} catch (AmarWaveException $e) {
    $status = $e->getStatusCode();
    $body   = $e->getResponseBody();
    echo "AmarWave {$status}: {$e->getMessage()}";
}
```

---

## Usage — Laravel

### Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=amarwave-config
```

Add to your `.env`:

```dotenv
AMARWAVE_APP_KEY=your-app-key
AMARWAVE_APP_SECRET=your-app-secret
AMARWAVE_CLUSTER=default
AMARWAVE_TIMEOUT=10
```

### Facade

```php
use AmarWave\Laravel\AmarWaveFacade as AmarWave;

AmarWave::trigger('orders', 'placed', ['order_id' => 42]);

AmarWave::triggerBatch([
    ['channel' => 'chat-1', 'event' => 'message', 'data' => ['text' => 'Hello']],
    ['channel' => 'chat-2', 'event' => 'message', 'data' => ['text' => 'World']],
]);
```

Or with the global alias:

```php
\AmarWave::trigger('orders', 'placed', ['order_id' => 42]);
```

### Dependency Injection

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

### Broadcasting Driver

Use AmarWave as a Laravel broadcasting driver so `broadcast(new YourEvent)` works out of the box.

**1 — Add the connection to `config/broadcasting.php`**

```php
'connections' => [
    'amarwave' => [
        'driver' => 'amarwave',
    ],
    // ...existing drivers
],
```

**2 — Set the driver in `.env`**

```dotenv
# Laravel 10
BROADCAST_DRIVER=amarwave
# Laravel 11+
BROADCAST_CONNECTION=amarwave
```

**3 — Create a broadcastable event**

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

    public function broadcastAs(): string { return 'order.placed'; }

    public function broadcastWith(): array
    {
        return ['order_id' => $this->order['id'], 'total' => $this->order['total']];
    }
}
```

**4 — Dispatch**

```php
broadcast(new OrderPlaced($order));
```

### Channel Authorization

In `routes/channels.php`:

```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('presence-room.{roomId}', function ($user, $roomId) {
    $room = \App\Models\Room::find($roomId);
    if ($room?->members()->where('user_id', $user->id)->exists()) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});
```

---

## License

MIT
