<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $unitId = $this->route('unit')->id ?? $this->route('unit');

        return [
            'kode_unit' => 'required|string|max:20|unique:units,kode_unit,' . $unitId,
            'nama' => 'required|string|max:100',
            'alamat' => 'nullable|string',
            'nama_ketua' => 'nullable|string|max:100',
            'no_hp' => 'nullable|string|max:20',
            'status' => 'required|in:aktif,nonaktif',
        ];
    }

    public function messages(): array
    {
        return [
            'kode_unit.required' => 'Kode unit wajib diisi.',
            'kode_unit.unique' => 'Kode unit ini sudah terdaftar.',
            'nama.required' => 'Nama unit bank sampah wajib diisi.',
            'status.required' => 'Status unit wajib dipilih.',
        ];
    }
}
