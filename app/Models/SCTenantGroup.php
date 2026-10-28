<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A manually maintained set of tenants.
 *
 * Kept generic on purpose: rules are the first user, other features can scope to a
 * group the same way through tenants() or hasTenant().
 */
class SCTenantGroup extends Model
{
    use HasFactory;

    protected $table = 'sc_tenant_groups';

    protected $fillable = [
        'name',
        'description',
    ];

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(SCTenant::class, 'sc_tenant_group_members', 'sc_tenant_group_id', 'tenantId');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ISCPPEventRule::class, 'sc_tenant_group_id');
    }

    /**
     * Uses the loaded tenants relation, so callers matching many events eager load it once.
     */
    public function hasTenant(?string $tenantId): bool
    {
        return $tenantId !== null && $this->tenants->contains('id', $tenantId);
    }
}
