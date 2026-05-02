<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Str;

class CustomerAccountService
{
    public function __construct(private readonly LoyaltyPointService $loyaltyPointService)
    {
    }

    public function customerForUser(?User $user): ?Customer
    {
        if (! $user) {
            return null;
        }

        return Customer::query()
            ->with('loyaltyAccount')
            ->where('user_id', $user->id)
            ->first();
    }

    public function syncCustomerForUser(User $user, array $attributes = []): Customer
    {
        $email = $attributes['email'] ?? $user->email;
        $phone = $attributes['phone'] ?? null;
        $name = $attributes['full_name'] ?? $attributes['name'] ?? $user->name;

        $customer = Customer::query()
            ->when($user->relationLoaded('customer') && $user->customer, fn ($query) => $query->whereKey($user->customer->getKey()))
            ->when(! isset($user->customer) && $email, fn ($query) => $query->orWhere('email', $email))
            ->when(! isset($user->customer) && $phone, fn ($query) => $query->orWhere('phone', $phone))
            ->orderByDesc('id')
            ->first();

        if (! $customer) {
            $customer = Customer::create([
                'public_id' => (string) Str::ulid(),
                'user_id' => $user->id,
                'full_name' => $name,
                'phone' => $phone,
                'email' => $email,
                'account_status' => 'ACTIVE',
            ]);
        } else {
            $customer->update([
                'user_id' => $user->id,
                'full_name' => $name ?: $customer->full_name,
                'phone' => $phone ?: $customer->phone,
                'email' => $email ?: $customer->email,
                'account_status' => $customer->account_status ?: 'ACTIVE',
            ]);
        }

        $this->loyaltyPointService->ensureAccount($customer->fresh());

        return $customer->fresh(['loyaltyAccount']);
    }
}
