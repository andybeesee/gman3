<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['grantable_type', 'grantable_id', 'grantee_type', 'grantee_id'])]
class VisibilityGrant extends Model
{
    /**
     * @return MorphTo<Model, $this>
     */
    public function grantable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function grantee(): MorphTo
    {
        return $this->morphTo();
    }
}
