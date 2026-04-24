# Testing
The tests should follow the pattern from the following examples. 
The files should follow the path convention: "App/Http/Controllers/OrderController.php" will have a test located at "tests/Feature/Http/Controllers/OrderController/StoreTest.php", or, "App/Models/Order.php" will have a test located at "tests/Unit/Models/OrderTest.php".
The tests doesn't need to use "RefreshDatabase" because it is already configured on Pest configuration file (tests/Pest.php).
All of the models created from a Factory should use the "fresh()" method, because it guarantees that the record is the same that would be in the database. 
When testing Actions or Controllers that interact with external APIs (e.g., Stripe, Mailgun), always use Http::fake() or Mockery. Do not allow the agent to make real outbound requests.

### Model test example:
```php
<?php
declare(strict_types=1);
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
// this is the test for a Order model class
// testing properties
test('to array', function (): void {
    $order = Order::factory()->create()->fresh();
    expect(array_keys($order->toArray()))->toEqual([
        'id',
        'user_id',
        'status',
        'total_amount',
        'notes',
        'created_at',
        'updated_at',
    ]);
});
// testing casts
it('casts status to Enum', function (): void {
    $order = Order::factory()->create(['status' => OrderStatus::Pending->value])->fresh();
    expect($order->status)->toBeInstanceOf(OrderStatus::class);
});
// testing relationships
it('belongs to user', function (): void {
    $user = User::factory()->create()->fresh();
    $order = Order::factory()->for($user)->create()->fresh();
    expect($order->user)->toBeInstanceOf(User::class)
        ->and($order->user->is($user))->toBeTrue();
});
```

### Action test example
```php
<?php
declare(strict_types=1);
use App\Actions\CreateOrder;
use App\Models\Order;
use App\Enums\OrderStatus;
use App\Models\User;
it('creates an order', function (): void {
    // Arrange
    $user = User::factory()->create()->fresh();
    $data = [
        'user_id'      => $user->id,
        'status'       => OrderStatus::Pending->value,
        'total_amount' => '299.99',
        'notes'        => 'Priority shipping requested.',
    ];
    // Act
    $createOrder = $this->app->make(CreateOrder::class);
    $order = $createOrder($data);
    // Assert
    expect(Order::count())->toBe(1)
        ->and($order)->toBeInstanceOf(Order::class)
        ->and($order->user_id)->toBe($user->id)
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->total_amount)->toBe('299.99')
        ->and($order->notes)->toBe('Priority shipping requested.');
});
```

### API Resource test example
```php
<?php
use App\Enums\OrderStatus;
use App\Http\Resources\ShowResource;
use App\Models\User;
use Illuminate\Http\Request;
it('transforms order resource into array with all fields', function (): void {
    $user = User::factory()->create()->fresh();
    $order = Order::factory()->create([
      'user_id' => $user->id,
      'status' => OrderStatus::Pending->value,
      'total_amount' => '1000',
      'notes' => 'test'
    ])->fresh();

    expect((new ShowResource($order))->toArray(new Request()))
    ->toBe([
      'user_id' => $user->id,
      'status' => OrderStatus::Pending->value,
      'total_amount' => '1000',
      'notes' => 'test'
    ]);
});
```

### Controller feature test example
```php
<?php
declare(strict_types=1);
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
it('stores a new order in the database and returns the resource', function () {
    // Arrange
    $user = User::factory()->create()->fresh();
    $payload = [
        'total_amount' => '299.99',
        'status' => OrderStatus::Pending->value,
        'notes' => 'Priority shipping requested.',
    ];
    // Act
    $response = $this->actingAs($user)
        ->postJson(route('orders.store'), $payload);
    // Assert
    $response->assertStatus(Response::HTTP_CREATED)
        ->assertJsonFragment([
            'total_amount' => '299.99',
            'status' => OrderStatus::Pending->value,
            'notes' => 'Priority shipping requested.',
        ]);
    // Verify Database Persistence
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total_amount' => 299.99,
        'status' => OrderStatus::Pending->value,
    ]);
});
it('requires valid data to create an order', function (string $field, mixed $value) {
    // Arrange
    $user = User::factory()->create()->fresh();
    $payload = [
        'total_amount' => '100.00',
        'status' => OrderStatus::Pending->value,
    ];
    // Override with invalid data
    $payload[$field] = $value;
    // Act & Assert
    $this->actingAs($user)
        ->postJson(route('orders.store'), $payload)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonValidationErrors($field);
})->with([
    'missing total' => ['total_amount', ''],
    'non-numeric total' => ['total_amount', 'not-a-number'],
    'invalid status' => ['status', 'invalid-status-string'],
]);
it('enforces authentication for ordering', function () {
    $this->postJson(route('orders.store'), [])
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});
```