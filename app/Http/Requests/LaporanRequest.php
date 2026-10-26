<?php

namespace App\Http\Requests;

use App\Enums\StatusAktif;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi parameter laporan (dipakai untuk pratinjau, PDF, dan Excel).
 */
class LaporanRequest extends FormRequest
{
    /**
     * Jenis laporan yang tersedia: [kunci => label].
     *
     * @var array<string, string>
     */
    public const JENIS = [
        'opd' => 'Per OPD',
        'status' => 'Per status',
        'expired' => 'Domain expired',
    ];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis' => [$this->routeIs('laporan.index') ? 'sometimes' : 'required', Rule::in(array_keys(self::JENIS))],
            'opd_id' => ['nullable', 'required_if:jenis,opd', 'integer', Rule::exists('opd', 'id')],
            'status' => ['nullable', 'required_if:jenis,status', Rule::enum(StatusAktif::class)],
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => array_filter([
                'nullable',
                'date_format:Y-m-d',
                $this->filled('dari') ? 'after_or_equal:dari' : null,
            ]),
            'sertakan_akan_expired' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'opd_id.required_if' => 'Pilih OPD untuk laporan per OPD.',
            'status.required_if' => 'Pilih status untuk laporan per status.',
            'sampai.after_or_equal' => 'Tanggal akhir periode tidak boleh lebih awal dari tanggal awal.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'jenis' => 'jenis laporan',
            'dari' => 'tanggal awal periode',
            'sampai' => 'tanggal akhir periode',
        ];
    }
}
