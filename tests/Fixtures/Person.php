<?php

declare(strict_types=1);

namespace PlinCode\LaravelFullName\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
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
    ];

    public $timestamps = true;
}
