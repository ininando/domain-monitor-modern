<?php

namespace App\Http\Requests;

use App\Enums\StatusAktif;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DomainRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Rapikan input sebelum divalidasi: nama domain huruf kecil tanpa spasi/protokol.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('nama_domain')) {
            $this->merge([
                'nama_domain' => str($this->input('nama_domain'))
                    ->trim()
                    ->lower()
                    ->replaceMatches('#^https?://#', '')
                    ->rtrim('/')
                    ->value(),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tanggalExpired = ['required', 'date_format:Y-m-d'];

        if ($this->filled('tanggal_registrasi')) {
            $tanggalExpired[] = 'after_or_equal:tanggal_registrasi';
        }

        return [
            'opd_id' => ['required', 'integer', Rule::exists('opd', 'id')],
            'nama_domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^(?=.{1,255}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/',
                Rule::unique('domains', 'nama_domain')->ignore($this->route('domain')),
            ],
            'tanggal_registrasi' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_expired' => $tanggalExpired,
            'status' => ['required', Rule::enum(StatusAktif::class)],
            'registrar' => ['nullable', 'string', 'max:100'],
            'nameserver' => ['nullable', 'string', 'max:1000'],
            'ip_address' => ['nullable', 'ip'],
            'hosting_provider' => ['nullable', 'string', 'max:100'],
            'ssl_status' => ['nullable', Rule::enum(StatusAktif::class)],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Pesan khusus.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama_domain.regex' => 'Format nama domain tidak valid. Contoh: dinkes.tomohonkota.go.id',
            'nama_domain.unique' => 'Nama domain ini sudah terdaftar.',
            'tanggal_expired.after_or_equal' => 'Tanggal expired tidak boleh lebih awal dari tanggal registrasi.',
            'status.enum' => 'Status yang dipilih tidak valid.',
            'ssl_status.enum' => 'Status SSL yang dipilih tidak valid.',
        ];
    }
}
