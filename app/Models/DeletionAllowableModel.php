<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class DeletionAllowableModel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $appends = array(
        // признак - удаление разрешено
        // передается клиенту для индикации возможности удаления
        'deletion_allowed'
    );

    public function getDeletionAllowedAttribute() {
        return $this->deletionAllowed();
    }

    public abstract function deletionAllowed() : bool;

}