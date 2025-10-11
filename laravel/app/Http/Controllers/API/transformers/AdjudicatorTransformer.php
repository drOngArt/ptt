<?php

namespace app\Http\Controllers\API\transformers;

use App\User;
use League\Fractal\TransformerAbstract;

class AdjudicatorTransformer extends TransformerAbstract {

    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'firstName' => $user->firstName,
            'lastName' => $user->lastName,
            'plId' => $user->judgeId,
            'username' => $user->username
        ];
    }
}