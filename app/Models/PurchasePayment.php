<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    use HasFactory;

  protected $fillable = [
    'invoice_purchase_id',
    'date_payment',
    'payment',
  ];



  public function getCreatedAtAttribute($value){
    $date = Carbon::parse($value)->timezone('Asia/Jakarta');
    return $date->format('Y-m-d H:i:s');
  }

  public function invoicepurchase(){
    return $this->belongsTo(InvoicePurchase::class);
  }


}
