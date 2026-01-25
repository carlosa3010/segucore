<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'subtotal', // Agregamos subtotal e iva si deseas, por ahora total directo
        'total',
        'status', // unpaid, paid, cancelled
        'details' // JSON para guardar el desglose (ej: 5 GPS x $10)
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'details' => 'array'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}