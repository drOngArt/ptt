<?php

namespace app\Http\Controllers\API\transformers;

use App\Round;
use League\Fractal\TransformerAbstract;

class RoundTransformer extends TransformerAbstract {

    public function transform(Round $round)
    {
        return [
            'danceId' => (int) $round->id,
            'danceSignature' => $round->dance,
            'roundName' => $round->description,
            'isDance' => (bool) $round->isDance,
            'isClosed' => (bool) $round->closed
        ];
    }
}