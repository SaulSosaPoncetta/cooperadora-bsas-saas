<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use App\Support\Tenant;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function establecimiento(): BelongsTo
    {
        return $this->belongsTo(Establecimiento::class);
    }

    /**
     * Usuarios de la cooperadora activa. User no lleva el filtro global
     * automático (se consulta durante el login, antes de saber a qué
     * cooperadora pertenece), así que todo listado o búsqueda de usuarios
     * debe pasar por este scope.
     */
    public function scopeDelEstablecimiento(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('establecimiento_id'), Tenant::id() ?? 0);
    }

    /**
     * El/la Presidente/a es el/la administrador/a de su cooperadora.
     */
    public function esPresidente(): bool
    {
        return $this->hasRole('Presidente');
    }
}
