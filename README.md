# amarwave-php

Official PHP client for [AmarWave](https://github.com/amarwave/amarwave) real-time messaging.

Works in **any PHP environment** — raw PHP, Symfony, or any other framework.

For Laravel projects, install the dedicated [`amarwave/amarwave-laravel`](../amarwave-laravel) package which adds a service provider, facade, and broadcasting driver.

---

## Requirements

- PHP 8.1+
- `ext-curl`
- `ext-json`

---

## Installation

```bash
composer require amarwave/amarwave-php
```

---

## Usage

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
try {
    $aw->trigger('channel', 'event', $data);
} catch (AmarWaveException $e) {
    $status = $e->getStatusCode();
    $body   = $e->getResponseBody();
    echo "AmarWave {$status}: {$e->getMessage()}";
}
```

### Channel Authentication

```php
// Private channel
$auth = $aw->authenticate($socketId, 'private-orders');

// Presence channel
$auth = $aw->authenticatePresence($socketId, 'presence-room.42', [
    'user_id'   => '99',
    'user_info' => ['name' => 'Alice'],
]);
```

### Constructor Options

| Parameter   | Type   | Default     | Description                                     |
|-------------|--------|-------------|-------------------------------------------------|
| `appKey`    | string | —           | Your AmarWave app key                           |
| `appSecret` | string | —           | Your AmarWave app secret (keep server-side)     |
| `cluster`   | string | `'default'` | `default`, `local`, `eu`, `us`, `ap1`, `ap2`   |
| `timeout`   | int    | `10`        | HTTP request timeout in seconds                 |

---

## Laravel

Use the dedicated Laravel package for full framework integration:

```bash
composer require amarwave/amarwave-laravel
```

See [amarwave-laravel](../amarwave-laravel/README.md) for setup.

---

## License

MIT
