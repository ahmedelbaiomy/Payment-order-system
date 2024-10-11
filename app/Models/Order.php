<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class Order extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'product_id', 'quantity', 'total_price', 'status'];

    public function product(){
        return $this->belongsTo(Product::class);
    }
    public function scopeStatus(Builder $query, $status)
    {
        return $query->when($status, function ($q) use ($status) {
            return $q->where('status', $status);
        });
    }
    public function scopeDateRange(Builder $query, $startDate, $endDate)
    {
        return $query->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
            return $q->whereBetween('created_at', [$startDate, $endDate]);
        });
    }
}
