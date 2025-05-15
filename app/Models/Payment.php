<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'vendor_id',
        'project_id',
        'client_id',
        'status',
        'external_reference',
        'internal_reference',
        'preference_id',
        'payment_id',
        'title',
        'quantity',
        'price', 
        'fee',
        'price_fee', 
        'success_url',
        'failure_url',
        'pending_url',
        'email',
        'name',
        'phone',
        'cpf',
            
    ];

  
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function projectToken()
    {
        return $this->project()->first()->token; 
    }
    public function webhookUrl()
    {
        switch ($this->status) {
            case 'pending':
                return $this->pending_url;
            case 'approved':
                return $this->success_url;
            case 'failure':
                return $this->failure_url;
            default:
                return $this->pending_url;
        }
        return $this->pending_url;
    }
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'pending':
                return 'Aguardando pagamento';
            case 'approved':
                return 'Pagamento aprovado';
            case 'in_process':
                return 'Pagamento em processo';
            case 'rejected':
                return 'Pagamento rejeitado';
            case 'refunded':
                return 'Pagamento estornado';
            default:
                return 'Status desconhecido';
        }
    }
 
}
