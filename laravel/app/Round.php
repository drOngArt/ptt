<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property bool $isFinal
 * @property mixed $dances
 * @property int $noPrg
 */
class Round extends Model
{
    protected $table = 'block';

    public $timestamps = false;
}
