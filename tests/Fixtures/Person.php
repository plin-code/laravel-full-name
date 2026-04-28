<?php

declare(strict_types=1);

namespace PlinCode\LaravelFullName\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Person extends Model
{
    use SoftDeletes;

    protected $table = 'test_persons';

    protected $fillable = [
        'first_name',
        'last_name',
        'given_name',
        'family_name',
        'account_id',
    ];

    public $timestamps = true;

    /** @return BelongsTo<Account, $this> */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
