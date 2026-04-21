<?php

declare(strict_types=1);

namespace PlinCode\LaravelFullName\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    protected $table = 'test_booking_items';

    protected $fillable = [
        'booking_id',
        'label',
    ];
}
