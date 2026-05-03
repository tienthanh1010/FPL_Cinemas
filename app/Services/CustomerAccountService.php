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
            ->with('loyaltyAccount.tier')
            ->where('user_id', $user->id)
            ->first();
    }

    public function syncCustomerForUser(User $user, array $attributes = []): Customer
    {
        $email = $attributes['email'] ?? $user->email;
        $phone = $attributes['phone'] ?? null;
        $name = $attributes['full_name'] ?? $attributes['name'] ?? $user->name;

        $existingCustomer = $this->customerForUser($user);

        if ($existingCustomer) {
            $customer = $existingCustomer;
        } else {
            $customerQuery = Customer::query();

            $customerQuery->where(function ($query) use ($user, $email, $phone) {
                $query->where('user_id', $user->id);

                if ($email) {
                    $query->orWhere('email', $email);
                }

                if ($phone) {
                    $query->orWhere('phone', $phone);
                }
            });

            $customer = $customerQuery
                ->orderByDesc('id')
                ->first();
        }

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

        return $customer->fresh(['loyaltyAccount.tier']);
    }
}
