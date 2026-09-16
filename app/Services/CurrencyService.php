<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookCountryPrice;
use App\Models\Country;
use App\Models\Currency;

class CurrencyService
{
    public function getSelectedCountry(): Country
    {
        $code = (auth()->check() && auth()->user()->country_code)
            ? auth()->user()->country_code
            : session()->get('customer_country', 'IN');

        $country = Country::where('code', $code)->where('is_active', true)->first();

        if (! $country) {
            $country = Country::where('code', 'IN')->first();
            if (! $country) {
                $country = Country::first();
            }
        }

        return $country;
    }

    public function getSelectedCurrency(): Currency
    {
        if (auth()->check() && auth()->user()->preferred_currency) {
            $currency = Currency::where('code', auth()->user()->preferred_currency)->where('is_active', true)->first();
            if ($currency) {
                return $currency;
            }
        }

        $country = $this->getSelectedCountry();
        $currency = Currency::where('code', $country->currency_code)->first();

        if (! $currency) {
            $currency = Currency::where('code', 'INR')->first();
            if (! $currency) {
                $currency = Currency::first();
            }
        }

        return $currency;
    }

    public function resolveBookPrice(Book $book, ?Country $country = null): array
    {
        $country = $country ?? $this->getSelectedCountry();
        $currency = Currency::where('code', $country->currency_code)->first();
        $rate = $currency?->exchange_rate ?? 1.0;

        // 1. Check for BookMarket explicit configuration
        $market = \App\Models\BookMarket::where('book_id', $book->id)
            ->where('country_code', $country->code)
            ->first();

        if ($market) {
            if (! $market->is_available) {
                return [
                    'is_available' => false,
                    'price' => (float) $book->price,
                    'mrp' => $book->mrp ? (float) $book->mrp : null,
                    'currency' => $country->currency_code,
                    'symbol' => $country->symbol,
                    'base_price' => (float) $book->price,
                    'exchange_rate' => $rate,
                ];
            }

            if ($market->price > 0) {
                return [
                    'is_available' => true,
                    'price' => (float) $market->price,
                    'mrp' => $market->mrp ? (float) $market->mrp : null,
                    'currency' => $country->currency_code,
                    'symbol' => $country->symbol,
                    'base_price' => (float) $book->price,
                    'exchange_rate' => $rate,
                    'max_order_qty' => $market->max_order_qty,
                    'dispatch_days' => $market->dispatch_days,
                ];
            }
        }

        // 2. Check for explicit legacy country price override
        $override = BookCountryPrice::where('book_id', $book->id)
            ->where('country_code', $country->code)
            ->first();

        if ($override && $override->price > 0) {
            return [
                'is_available' => true,
                'price' => (float) $override->price,
                'mrp' => $override->mrp ? (float) $override->mrp : null,
                'currency' => $country->currency_code,
                'symbol' => $country->symbol,
                'base_price' => (float) $book->price,
                'exchange_rate' => $rate,
            ];
        }

        // 3. Base INR price converted using markup & exchange rate
        $basePrice = (float) $book->price;
        $baseMrp = $book->mrp ? (float) $book->mrp : null;

        $markupMultiplier = 1 + ($country->markup_percentage / 100);
        $convertedPrice = round($basePrice * $markupMultiplier * $rate, 2);
        $convertedMrp = $baseMrp ? round($baseMrp * $markupMultiplier * $rate, 2) : null;

        return [
            'is_available' => true,
            'price' => $convertedPrice,
            'mrp' => $convertedMrp,
            'currency' => $country->currency_code,
            'symbol' => $country->symbol,
            'base_price' => $basePrice,
            'exchange_rate' => $rate,
        ];
    }

    public function calculateShippingFee(int $totalItems, float $subtotal, ?Country $country = null): float
    {
        $country = $country ?? $this->getSelectedCountry();

        if ($country->free_shipping_threshold && $subtotal >= $country->free_shipping_threshold) {
            return 0.00;
        }

        $base = (float) $country->base_shipping_fee;
        $perItem = (float) $country->per_item_shipping_fee;

        return round($base + ($totalItems > 0 ? ($totalItems - 1) * $perItem : 0), 2);
    }
}

