<?php

namespace App\Http\Requests;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Maksimal percobaan login gagal sebelum dikunci sementara.
     */
    private const MAKS_PERCOBAAN = 5;

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
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Coba autentikasi. Gagal → pesan umum "Email/Password salah" (tidak membocorkan mana yang salah).
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->pastikanTidakDikunci();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->kunciThrottle());

            throw ValidationException::withMessages([
                'email' => 'Email/Password salah.',
            ]);
        }

        RateLimiter::clear($this->kunciThrottle());
    }

    /**
     * @throws ValidationException
     */
    private function pastikanTidakDikunci(): void
    {
        if (! RateLimiter::tooManyAttempts($this->kunciThrottle(), self::MAKS_PERCOBAAN)) {
            return;
        }

        event(new Lockout($this));

        $detik = RateLimiter::availableIn($this->kunciThrottle());

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$detik} detik.",
        ]);
    }

    private function kunciThrottle(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
