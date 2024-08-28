<?php

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseAuth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class Login extends BaseAuth
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();

        if (!Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();

        if ($user->is_active == 0) {
            Filament::auth()->logout();
            Notification::make()->title('Akun Anda Dibatasi')->body('Silahkan Hubungi Administrator.')->color('danger')->danger()->send();
        } elseif (is_null($user->email_verified_at)) {
            Notification::make()->title('Verifikasi Email Anda')->body('Silahkan Verifikasi Email Anda Terlebih Dahulu.')->color('warning')->warning()->send();
        } else {
            Notification::make()->title('Selamat Datang Kembali, ' . $user->name)->success()->color('success')->send();
        }

        if (
            ($user instanceof FilamentUser) &&
            (!$user->canAccessPanel(Filament::getCurrentPanel()))
        ) {
            Filament::auth()->logout();
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/login.form.email.label'))
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->prefixIcon('ri-mail-send-line')
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()" tabindex="3"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->prefixIcon('ri-lock-line')
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getEmailFormComponent()->label('Alamat Email')->placeholder('Masukkan Alamat Email'),
                        $this->getPasswordFormComponent()->label('Kata Sandi'),
                        $this->getRememberFormComponent()->label('Ingat Saya'),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    public function registerAction(): Action
    {
        return Action::make('register')
            ->link()
            ->label('Daftar Sekarang')
            ->url(filament()->getRegistrationUrl());
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label('Masuk Sekarang')
            ->submit('authenticate');
    }
}
