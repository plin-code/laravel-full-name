<?php

declare(strict_types=1);

namespace PlinCode\LaravelFullName\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Account extends Model
{
    protected $table = 'test_accounts';

    protected $fillable = [
        'label',
    ];

    /** @return HasOne<Person, $this> */
    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'account_id');
    }

    /** @return HasMany<Person, $this> */
    public function people(): HasMany
    {
        return $this->hasMany(Person::class, 'account_id');
    }
}
