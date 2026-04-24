# Coding Style
All comments, variable names, method names, and documentation must be in English.
Use strict typing in every PHP file. No exceptions.

## Controller Pattern
Controllers should be thin. They validate, delegate to an action class, and return a response.
Single-action controllers are preferred for non-CRUD endpoints like a "SearchController".

### Good controller example:
```php
declare(strict_types=1);
namespace App\Http\Controllers;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Actions\CreateOrder;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function __invoke(
        StoreOrderRequest $request,
        CreateOrder $createOrder,
    ): JsonResponse {
        // Controller does not contain business logic.
        // It validates (via FormRequest), delegates, and returns.
        $createdOrder = $createOrder($request->validated());
        return OrderResource::make($createdOrder)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
```

### Bad controller example:
```php
// WRONG: fat controller with business logic, no type hints, no form request
class OrderController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all(); // Never use all(), never skip validation
        $order = new Order();
        $order->user_id = auth()->id();
        $order->total = 0;

        foreach ($data['items'] as $item) { // Business logic in controller
            $order->total += $item['price'] * $item['qty'];
        }
        $order->save();
        return response()->json($order); // Raw model dump, no API Resource
    }
}
```

## Action Pattern
Actions contain business logic. The name should follow the CRUD methods. Example: CreateOrder, DeleteUserAvatar, etc. They are injected via parameter (method injection). Actions should not depend on Request or other HTTP-layer objects. Accept only validated data (arrays or DTOs), return models or results. 

### Action example:
```php
declare(strict_types=1);
namespace App\Actions;
use App\Models\Order;

class CreateOrder
{
  public function __invoke(array $data): Order
  {
      $createdOrder = Order::create($data);
      return $createdOrder;
  }
}
```

## Model Conventions
- Never use $fillable or $guarded
- Add property annotations like: "@property-read int $id" to all columns in the table related to the model
- Use Enums for status fields, not magic strings
- Relationships must have return types
- Scopes should be typed and named descriptively

### Model example:
```php 
declare(strict_types=1);
namespace App\Models;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property-read string $user_id
 * @property-read OrderStatus $status
 * @property-read string $total_amount
 * @property-read string $notes
*/
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,  // Enum cast, not string
            'total_amount' => 'decimal:2',
        ];
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    // Scope: descriptive name, typed builder return
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }
}
```

## Migration Conventions
- Use Restrict Deletes, not Cascade deletes
- Do not use default values
- Use the string() method on columns that will be casted to an Enum instead of using the enum() method.