<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Support\Facades\DB;

class ProductSkuGenerator
{
    public static function initial(string $name): string
    {
        preg_match('/[\p{L}\p{N}]/u', $name, $matches);

        return mb_strtoupper($matches[0] ?? 'P');
    }

    public function nextNumber(): int
    {
        return $this->nextAvailableNumber((int) DB::table('product_sku_sequences')->where('id', 2)->value('last_number'));
    }

    public function generate(Category $category, ?Subcategory $subcategory): string
    {
        $prefix = self::initial($category->name).($subcategory ? self::initial($subcategory->name) : '');

        return DB::transaction(function () use ($prefix): string {
            $sequence = DB::table('product_sku_sequences')->where('id', 2)->lockForUpdate()->firstOrFail();
            $number = $this->nextAvailableNumber((int) $sequence->last_number);
            while (Product::where('sku', $prefix.'-'.$number)->exists()) {
                $number = $this->nextAvailableNumber($number);
            }
            DB::table('product_sku_sequences')->where('id', 2)->update(['last_number' => $number]);

            return $prefix.'-'.$number;
        }, 5);
    }

    private function nextAvailableNumber(int $lastNumber): int
    {
        $number = $lastNumber + 1;
        $legacyLastNumber = (int) DB::table('product_sku_sequences')->where('id', 1)->value('last_number');

        if ($number >= 1001 && $number <= $legacyLastNumber) {
            return $legacyLastNumber + 1;
        }

        return $number;
    }
}
