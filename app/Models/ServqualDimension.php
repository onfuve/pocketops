<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServqualDimension extends Model
{
    protected $table = 'servqual_dimensions';

    protected $fillable = [
        'code',
        'name',
        'name_fa',
        'description',
        'sort',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    public const CODE_TANGIBLES = 'tangibles';
    public const CODE_RELIABILITY = 'reliability';
    public const CODE_RESPONSIVENESS = 'responsiveness';
    public const CODE_ASSURANCE = 'assurance';
    public const CODE_EMPATHY = 'empathy';

    public function questions(): HasMany
    {
        return $this->hasMany(ServqualQuestionBank::class, 'dimension_id')->orderBy('sort');
    }

    public function microResponses(): HasMany
    {
        return $this->hasMany(ServqualMicroResponse::class, 'dimension_id');
    }
}
