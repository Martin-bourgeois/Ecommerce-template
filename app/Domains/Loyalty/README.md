# Loyalty Module Documentation

## Overview
The Loyalty module provides a complete point-based loyalty system for e-commerce customers. Features include:

- **Points System**: Customers earn 1 point per €1 spent
- **Tier System**: Bronze (0pts) → Silver (500pts) → Gold (1500pts) → Platinum (5000pts)
- **Discounts**: Automatic discounts based on tier (0% → 3% → 5% → 10%)
- **Points Redemption**: Spend points for discounts (100 points = 5€)
- **Transaction History**: Complete audit trail of all points activity

## Architecture

### Directory Structure
```
app/Domains/Loyalty/
├── Controllers/
│   └── LoyaltyController.php
├── DTOs/
│   ├── LoyaltyAccountDTO.php
│   └── LoyaltyTransactionDTO.php
├── Enums/
│   ├── LoyaltyTier.php
│   └── TransactionType.php
├── Models/
│   ├── LoyaltyAccount.php
│   └── LoyaltyTransaction.php
├── Repositories/
│   ├── LoyaltyAccountRepository.php
│   └── LoyaltyTransactionRepository.php
├── Responses/
│   ├── LoyaltyAccountResponse.php
│   └── LoyaltyTransactionResponse.php
├── Services/
│   └── LoyaltyService.php
├── Providers/
│   └── LoyaltyServiceProvider.php
└── routes/
    └── loyalty.php
```

## Usage

### Award Points for Purchase
```php
$loyaltyService = app(\App\Domains\Loyalty\Services\LoyaltyService::class);
$transaction = $loyaltyService->awardPointsForPurchase($user, 100.50, $orderId);
```

### Spend Points
```php
$transaction = $loyaltyService->spendPoints($user, 50, 'Discount redemption');
```

### Add Bonus Points
```php
$transaction = $loyaltyService->addBonusPoints($user, 100, 'Referral bonus');
```

### Refund Points
```php
$transaction = $loyaltyService->refundPoints($user, 50, 'Order cancellation');
```

### Get Loyalty Summary
```php
$summary = $loyaltyService->getSummary($user);
// Returns: [
//     'balance' => 150,
//     'total_earned' => 500,
//     'current_tier' => LoyaltyTier::SILVER,
//     'tier_name' => 'Argent',
//     'discount_percent' => 3,
//     'points_to_next_tier' => 1000,
//     'points_in_euros' => 7.5
// ]
```

## API Endpoints

### Get User's Loyalty Account
```
GET /loyalty/account
Authorization: Bearer {token}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "userId": 5,
    "pointsBalance": 150,
    "totalEarned": 500,
    "currentTier": {
      "name": "Argent",
      "value": "silver",
      "discountPercent": 3
    },
    "balance": {
      "points": 150,
      "euros": 7.5
    },
    "progression": {
      "pointsToNextTier": 1000,
      "nextTierName": "Gold"
    }
  }
}
```

### Get Transactions
```
GET /loyalty/transactions?limit=25
Authorization: Bearer {token}
```

### Spend Points
```
POST /loyalty/spend
Authorization: Bearer {token}
Content-Type: application/json

{
  "points": 50,
  "reason": "Discount redemption"
}
```

### Get Summary
```
GET /loyalty/summary
Authorization: Bearer {token}
```

### Get Top Earners (Admin)
```
GET /loyalty/top-earners?limit=10
Authorization: Bearer {admin_token}
```

## Enums

### LoyaltyTier
- `BRONZE`: 0 points, 0% discount
- `SILVER`: 500 points, 3% discount
- `GOLD`: 1500 points, 5% discount
- `PLATINUM`: 5000 points, 10% discount

### TransactionType
- `EARNED`: Points from purchases
- `SPENT`: Points redeemed for discount
- `REFUNDED`: Points refunded (e.g., order cancellation)
- `EXPIRED`: Points that expired
- `BONUS`: Promotional bonus points

## Database Schema

### loyalty_accounts Table
```sql
CREATE TABLE loyalty_accounts (
    id BIGINT PRIMARY KEY,
    user_id BIGINT UNIQUE NOT NULL,
    points_balance INT DEFAULT 0,
    total_earned INT DEFAULT 0,
    current_tier VARCHAR(255) DEFAULT 'bronze',
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### loyalty_transactions Table
```sql
CREATE TABLE loyalty_transactions (
    id BIGINT PRIMARY KEY,
    account_id BIGINT NOT NULL,
    type VARCHAR(255),
    points INT,
    description TEXT,
    order_id BIGINT,
    created_at TIMESTAMP
);
```

## Integration with Orders

### Automatic Points on Order Completion
Add this to your order completion service:

```php
$loyaltyService = app(\App\Domains\Loyalty\Services\LoyaltyService::class);
$loyaltyService->awardPointsForPurchase($order->user, $order->total, $order->id);
```

### Points Refund on Order Cancellation
```php
$transaction = $order->loyaltyTransaction(); // Get the original transaction
$loyaltyService->refundPoints($order->user, abs($transaction->points), 'Order cancellation');
```

## Testing

Run the loyalty tests:
```bash
php artisan test tests/Feature/Loyalty/
php artisan test tests/Unit/Loyalty/
```

## Configuration

The loyalty tier thresholds and discount percentages can be modified in the `LoyaltyTier` enum.

### Example: Modify Thresholds
```php
// In LoyaltyTier.php
public function getThreshold(): int
{
    return match ($this) {
        self::BRONZE => 0,
        self::SILVER => 1000,    // Changed from 500
        self::GOLD => 3000,      // Changed from 1500
        self::PLATINUM => 10000, // Changed from 5000
    };
}
```

## Points-to-Euros Conversion
By default: 20 points = 1€ (1 point = 0.05€)

Customize in `LoyaltyAccount::getPointsInEuros()`:
```php
public function getPointsInEuros(int $points): float
{
    return $points / 25; // New rate: 25 points = 1€
}
```

## Events

The system can emit events on tier promotion (ready for implementation):
```php
event(new UserPromotedToTier($user, $newTier));
```

## Best Practices

1. **Always use repositories** instead of querying models directly
2. **Use the service layer** for business logic, never in controllers
3. **Validate tier changes** before updating
4. **Log transactions** for audit trails
5. **Cache tier information** for frequently accessed data
6. **Handle concurrent requests** with database transactions

## Troubleshooting

### User has low balance but can't spend points
Check `LoyaltyAccount.points_balance` is updated correctly after each transaction.

### Tier not updating automatically
Call `$loyaltyService->updateTier($user)` after points are awarded.

### Points calculation seems off
Verify the conversion rate in `getPointsInEuros()` method.
