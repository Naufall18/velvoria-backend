<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method'       => 'required|string|in:cod,midtrans',
            'shipping_name'        => 'required|string|max:255',
            'shipping_phone'       => 'required|string|max:30',
            'shipping_address'     => 'required|string|max:500',
            'shipping_city'        => 'required|string|max:100',
            'shipping_province'    => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:20',
            'notes'                => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required'       => 'Metode pembayaran wajib dipilih',
            'payment_method.in'             => 'Metode pembayaran tidak valid (cod atau midtrans)',
            'shipping_name.required'        => 'Nama penerima wajib diisi',
            'shipping_phone.required'       => 'Nomor telepon wajib diisi',
            'shipping_address.required'     => 'Alamat pengiriman wajib diisi',
            'shipping_city.required'        => 'Kota wajib diisi',
            'shipping_province.required'    => 'Provinsi wajib diisi',
            'shipping_postal_code.required' => 'Kode pos wajib diisi',
        ];
    }
}
