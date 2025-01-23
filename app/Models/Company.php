<?php

namespace App\Models;

use App\UrlGeneration;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use HasFactory;
    use UrlGeneration;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'tax_id',
        'address',
        'city',
        'zip',
    ];

    /**
     * Generate the slug field value when the name is set
     *
     * @param string $value
     * @return void
     */
    protected function setNameAttribute(string $value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = $this->generateSlug($value, $this->attributes['id'] ?? null);
    }

    /**
     * Check the slug field for duplication
     *
     * @return Attribute
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            set: fn (string $value, array $attributes) => $this->generateSlug($value, $attributes['id'] ?? null)
        );
    }

    /**
     * Get related employees
     *
     * @return BelongsToMany
     */
    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'company_employees', 'company_id', 'employee_id');
    }
}
