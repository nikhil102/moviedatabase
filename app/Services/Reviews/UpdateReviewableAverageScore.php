<?php

namespace App\Services\Reviews;

use DB;
use App\Review;

class UpdateReviewableAverageScore
{
    /**
     * @param int $id
     * @param string $type
     */
    public function execute($id, $type)
    {
        $localVoteAverage = app(Review::class)
            ->where('type', Review::USER_REVIEW_TYPE)
            ->where('reviewable_type', $type)
            ->where('reviewable_id', $id)
            ->avg('score');

        $average = number_format((float) $localVoteAverage, 1);

        // title or episode
        $model = app($type)->find($id);
        $model->local_vote_average = $average;
        $model->save();
    }
}